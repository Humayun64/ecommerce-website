<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\Address;
use App\Models\Order;
use App\Models\ShippingZone;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function __construct(
        private CartService $cart,
        private OrderService $orders,
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

        return view('site.checkout.show', [
            'cart'    => $cart,
            'zones'   => ShippingZone::active()->orderBy('sort_order')->get(),
            'address' => $default,
        ]);
    }

    public function store(CheckoutRequest $request)
    {
        $cart = $this->cart->current();

        try {
            $order = $this->orders->place($cart, $request->validated() + [
                'user_id' => auth()->id(),
            ]);
        } catch (ValidationException $e) {
            return back()->withInput()->withErrors($e->errors());
        }

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

        // Held in the session so the confirmation page can be shown once
        // without exposing every order number to anyone who guesses one.
        session()->put('recent_order', $order->id);

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

        return view('site.checkout.done', compact('order'));
    }

    /* ---------- public tracking ---------- */

    public function trackForm()
    {
        return view('site.orders.track', ['order' => null, 'searched' => false]);
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

        // Both the number and the phone must match, so an order number
        // alone is not enough to read someone else's details.
        $order = Order::with('items')
            ->where('order_number', trim($data['order_number']))
            ->where('customer_phone', $phone)
            ->first();

        return view('site.orders.track', [
            'order'    => $order,
            'searched' => true,
        ]);
    }
}
