<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use App\Services\CouponService;
use App\Services\DeliveryCalculator;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(
        private CartService $cart,
        private CouponService $coupons,
        private DeliveryCalculator $delivery,
    ) {
    }

    public function index()
    {
        $cart = $this->cart->current();

        // Re-checked on every visit: a coupon that stopped qualifying
        // (cart changed, limit reached, date passed) drops itself here
        // rather than surviving all the way to checkout.
        $result = $this->coupons->evaluateCart(
            $this->coupons->fromSession(),
            $cart,
            $this->phone(),
            auth()->id(),
        );

        $dropped = null;

        if (session(CouponService::SESSION_KEY) && ! $result['ok']) {
            $dropped = $result['message'];
            $this->coupons->forget();
        }

        // The cart can only estimate — the real charge needs an area,
        // so show the cheapest this basket could ship for.
        $estimate = $this->delivery->cheapest(
            $this->delivery->linesFromCart($cart),
            (float) $cart->subtotal
        );

        return view('site.cart.index', [
            'cart'     => $cart,
            'estimate' => $estimate,
            'coupon'   => $result['ok'] ? $result['coupon'] : null,
            'discount' => $result['ok'] ? $result['discount'] : 0.0,
            'freeShip' => $result['ok'] && $result['free_shipping'],
            'dropped'  => $dropped,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'variant_id' => ['nullable', 'exists:product_variants,id'],
            'quantity'   => ['nullable', 'integer', 'min:1', 'max:99'],
            'buy_now'    => ['nullable', 'boolean'],
        ]);

        $product = Product::active()->findOrFail($data['product_id']);
        $variant = ! empty($data['variant_id'])
            ? ProductVariant::find($data['variant_id'])
            : null;

        $result = $this->cart->add($product, $variant, (int) ($data['quantity'] ?? 1));

        // Buy now: same add, then straight to checkout instead of back.
        if ($result['ok'] && $request->boolean('buy_now')) {
            return redirect()->route('checkout.show');
        }

        return $this->respond($request, $result);
    }

    public function update(Request $request, CartItem $item)
    {
        $this->authorizeItem($item);

        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:99'],
        ]);

        $result = $this->cart->update($item, (int) $data['quantity']);

        return $this->respond($request, $result);
    }

    public function destroy(Request $request, CartItem $item)
    {
        $this->authorizeItem($item);
        $this->cart->remove($item);

        return $this->respond($request, [
            'ok' => true, 'capped' => false, 'message' => __('Removed from your cart.'),
        ]);
    }

    /* ---------- helpers ---------- */

    /** Nobody edits a line that is not in their own cart. */
    private function authorizeItem(CartItem $item): void
    {
        $cart = $this->cart->current(false);

        abort_unless($cart && $item->cart_id === $cart->id, 403);
    }

    private function phone(): ?string
    {
        $phone = preg_replace('/\D/', '', (string) auth()->user()?->phone);

        return $phone ?: null;
    }

    private function respond(Request $request, array $result)
    {
        if ($request->wantsJson()) {
            $cart = $this->cart->current();

            return response()->json([
                'ok'       => $result['ok'],
                'message'  => $result['message'],
                'count'    => $cart->count,
                'subtotal' => number_format($cart->subtotal),
            ], $result['ok'] ? 200 : 422);
        }

        return $result['ok']
            ? back()->with('status', $result['message'])
            : back()->with('error', $result['message']);
    }
}
