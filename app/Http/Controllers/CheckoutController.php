<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\Address;
use App\Models\Order;
use App\Models\ShippingZone;
use App\Services\CartService;
use App\Services\CouponService;
use App\Services\DeliveryCalculator;
use App\Services\OrderMailer;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function __construct(
        private CartService $cart,
        private OrderService $orders,
        private OrderMailer $mailer,
        private CouponService $coupons,
        private DeliveryCalculator $delivery,
        private PaymentService $payments,
    ) {
    }

    public function show()
    {
        $cart = $this->cart->current();

        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', __('Your cart is empty.'));
        }

        $default = auth()->check()
            ? Address::where('user_id', auth()->id())->orderByDesc('is_default')->first()
            : null;

        // Re-checked on every page load, so a coupon that stopped qualifying
        // quietly drops rather than surviving to the order.
        $coupon = $this->coupons->evaluateCart(
            $this->coupons->fromSession(),
            $cart,
            $default?->phone ?: auth()->user()?->phone,
            auth()->id(),
        );

        if (! $coupon['ok']) {
            $this->coupons->forget();
        }

        // Each area is priced for this exact basket, so the radio buttons
        // show the real figure rather than a headline rate.
        $lines  = $this->delivery->linesFromCart($cart);
        $quotes = $this->delivery->quoteZones($lines, (float) $cart->subtotal);

        return view('site.checkout.show', [
            'cart'     => $cart,
            'zones'    => ShippingZone::active()->orderBy('sort_order')->get(),
            'quotes'   => $quotes,
            'address'  => $default,
            'discount' => $coupon['ok'] ? $coupon['discount'] : 0.0,
            'freeShip' => $coupon['ok'] && $coupon['free_shipping'],
            'coupon'   => $coupon['ok'] ? $coupon['coupon'] : null,
            'methods'  => $this->payments->choices(),
        ]);
    }

    public function store(CheckoutRequest $request)
    {
        $cart = $this->cart->current();

        /**
         * The payment fields are validated here rather than in CheckoutRequest
         * because what is required depends on which method was picked, and
         * that is only known once the form arrives.
         */
        try {
            $method = $this->payments->resolve(
                $request->input('payment_method_code'),
                (float) $cart->subtotal,
            );

            $paymentInput = $request->validate($this->payments->rulesFor($method));

            // Before any stock is taken.
            $this->payments->precheck($method, $paymentInput);
        } catch (ValidationException $e) {
            return back()->withInput()->withErrors($e->errors());
        }

        $data = $request->validated();
        $data['payment_method'] = $method->code;

        try {
            $order = $this->orders->place($cart, $data + [
                'user_id'     => auth()->id(),
                'coupon_code' => session(CouponService::SESSION_KEY),
            ]);

            if (! $method->is_gateway) {
                $this->payments->record($order, $method, $paymentInput);
            }
        } catch (ValidationException $e) {
            // A coupon that failed at the last moment should not keep
            // blocking checkout — drop it and let them try again.
            if (array_key_exists('coupon', $e->errors())) {
                $this->coupons->forget();
            }

            return back()->withInput()->withErrors($e->errors());
        }

        $this->coupons->forget();

        if (auth()->check() && $request->boolean('save_address')) {
            Address::updateOrCreate(
                [
                    'user_id' => auth()->id(),
                    'phone'   => $request->customer_phone,
                    'address' => $request->shipping_address,
                ],
                [
                    'name'             => $request->customer_name,
                    'area'             => $request->shipping_area,
                    'shipping_zone_id' => $request->shipping_zone_id,
                    'is_default'       => true,
                ]
            );
        }

        // Sent after the transaction committed, never inside it.
        $this->mailer->placed($order);

        // And a WhatsApp message to the owner, if that is switched on.
        app(\App\Services\WhatsApp::class)->orderPlaced($order);

        // Held in the session so the confirmation page can be shown once
        // without exposing every order number to anyone who guesses one.
        session()->put('recent_order', $order->id);

        // Paying online: off to the provider's page. The order already
        // exists, so a customer who closes that page can still pay later.
        if ($method->is_gateway) {
            return app(GatewayPaymentController::class)->send($order, $method);
        }

        return redirect()->route('checkout.done', $order->order_number);
    }

    public function done(string $number)
    {
        $order = Order::with('items')->where('order_number', $number)->firstOrFail();

        abort_unless(
            session('recent_order') === $order->id
            || (auth()->check() && $order->user_id === auth()->id()),
            403
        );

        $method = \App\Models\PaymentMethod::where('code', $order->payment_method)->first();

        return view('site.checkout.done', [
            'order'  => $order,
            'method' => $method,
            // Offer "Pay now" only for an online method that is still unpaid.
            'canPay' => $method && $method->is_gateway && $method->is_usable
                && $order->payment_status !== 'paid'
                && ! in_array($order->status, ['cancelled', 'returned'], true),
        ]);
    }

    /* ---------- public tracking ---------- */

    public function trackForm()
    {
        return view('site.orders.track', [
            'order'     => null,
            'searched'  => false,
            'returns'   => collect(),
            'canReturn' => false,
        ]);
    }

    public function track(Request $request)
    {
        $data = $request->validate([
            'order_number' => ['required', 'string', 'max:32'],
            'phone'        => ['required', 'string', 'max:40'],
        ]);

        $phone = preg_replace('/\D/', '', $data['phone']);

        if (str_starts_with($phone, '880')) {
            $phone = '0' . substr($phone, 3);
        }

        // An order number has only a few thousand possibilities per day,
        // so without a limit someone who knows a customer's phone could
        // simply try them all. Wrong guesses are counted per phone and per
        // visitor; a correct lookup costs nothing.
        $limits = [
            'track-phone:' . $phone        => 8,
            'track-ip:' . $request->ip()   => 20,
        ];

        foreach ($limits as $key => $max) {
            if (RateLimiter::tooManyAttempts($key, $max)) {
                throw ValidationException::withMessages([
                    'order_number' => __('Too many attempts. Please try again in :m minutes, or call us.', [
                        'm' => (int) ceil(RateLimiter::availableIn($key) / 60),
                    ]),
                ]);
            }
        }

        // Both the number and the phone must match, so an order number
        // alone is not enough to read someone else's details.
        $order = Order::with('items')
            ->where('order_number', trim($data['order_number']))
            ->where('customer_phone', $phone)
            ->first();

        // Knowing the number AND the phone is how a guest proves the order is
        // theirs. Remember it for this session so they can open a return
        // without having to make an account first.
        if (! $order) {
            foreach ($limits as $key => $max) {
                RateLimiter::hit($key, 1800);
            }
        }

        if ($order) {
            $seen = session('tracked_orders', []);
            $seen[] = $order->id;
            session(['tracked_orders' => array_values(array_unique($seen))]);
        }

        return view('site.orders.track', [
            'order'    => $order,
            'searched' => true,
            'returns'  => $order
                ? \App\Models\ReturnRequest::where('order_id', $order->id)->latest()->get()
                : collect(),
            'canReturn' => $order ? app(\App\Services\ReturnService::class)->blockedReason($order) === null : false,
        ]);
    }
}
