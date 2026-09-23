<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use App\Services\CouponService;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function __construct(
        private CartService $cart,
        private CouponService $coupons,
    ) {
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'max:40'],
        ]);

        $cart = $this->cart->current();

        if ($cart->items->isEmpty()) {
            return back()->with('error', __('Add something to your cart first.'));
        }

        $coupon = $this->coupons->findByCode($request->code);

        $result = $this->coupons->evaluateCart(
            $coupon,
            $cart,
            $this->phone(),
            auth()->id(),
        );

        if (! $result['ok']) {
            $this->coupons->forget();

            return back()->with('error', $result['message']);
        }

        $this->coupons->remember($result['coupon']);

        return back()->with('status', __('Coupon applied. You saved ৳:n.', [
            'n' => number_format($result['discount']),
        ]));
    }

    public function destroy()
    {
        $this->coupons->forget();

        return back()->with('status', __('Coupon removed.'));
    }

    /** The phone we count per-customer limits against. */
    private function phone(): ?string
    {
        $phone = preg_replace('/\D/', '', (string) auth()->user()?->phone);

        return $phone ?: null;
    }
}
