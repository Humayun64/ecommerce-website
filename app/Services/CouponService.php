<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Order;
use Illuminate\Support\Str;

/**
 * One place that decides whether a coupon applies and what it is worth.
 *
 * The cart page, the checkout page and the order transaction all call the
 * same method, so a customer can never be shown one discount and charged
 * against another.
 */
class CouponService
{
    public const SESSION_KEY = 'coupon_code';

    /**
     * @return array{ok: bool, message: string, coupon: ?Coupon, discount: float, free_shipping: bool}
     */
    public function evaluate(?Coupon $coupon, array $lines, ?string $phone = null, ?int $userId = null): array
    {
        $fail = fn (string $message) => [
            'ok' => false, 'message' => $message,
            'coupon' => $coupon, 'discount' => 0.0, 'free_shipping' => false,
        ];

        if (! $coupon) {
            return $fail(__('That coupon code is not valid.'));
        }

        if (! $coupon->is_active) {
            return $fail(__('That coupon is no longer available.'));
        }

        if (! $coupon->has_started) {
            return $fail(__('That coupon starts on :date.', [
                'date' => $coupon->starts_at->format('j M Y'),
            ]));
        }

        if ($coupon->is_expired) {
            return $fail(__('That coupon expired on :date.', [
                'date' => $coupon->expires_at->format('j M Y'),
            ]));
        }

        if ($coupon->is_exhausted) {
            return $fail(__('That coupon has been fully claimed.'));
        }

        $subtotal = (float) collect($lines)->sum('line_total');

        if ($subtotal <= 0) {
            return $fail(__('Add something to your cart first.'));
        }

        if ($coupon->min_spend && $subtotal < (float) $coupon->min_spend) {
            return $fail(__('Spend ৳:n or more to use this coupon.', [
                'n' => number_format((float) $coupon->min_spend),
            ]));
        }

        if ($coupon->first_order_only && $this->hasOrderedBefore($phone, $userId)) {
            return $fail(__('That coupon is for first orders only.'));
        }

        if ($coupon->usage_limit_per_customer && $phone) {
            $used = CouponUsage::where('coupon_id', $coupon->id)
                ->where('phone', $phone)
                ->count();

            if ($used >= $coupon->usage_limit_per_customer) {
                return $fail(__('You have already used this coupon.'));
            }
        }

        $eligible = $this->eligibleTotal($coupon, $lines);

        if ($eligible <= 0) {
            $only = $coupon->category?->name ?? $coupon->brand?->name;

            return $fail($only
                ? __('This coupon only applies to :name products.', ['name' => $only])
                : __('This coupon does not apply to anything in your cart.'));
        }

        $discount = $coupon->type === 'percent'
            ? $eligible * ((float) $coupon->value / 100)
            : (float) $coupon->value;

        if ($coupon->type === 'percent' && $coupon->max_discount) {
            $discount = min($discount, (float) $coupon->max_discount);
        }

        // Never discount more than the goods are worth.
        $discount = round(min($discount, $eligible), 2);

        if ($discount <= 0 && ! $coupon->free_shipping) {
            return $fail(__('This coupon does not reduce your total.'));
        }

        return [
            'ok'            => true,
            'message'       => __('Coupon :code applied.', ['code' => $coupon->code]),
            'coupon'        => $coupon,
            'discount'      => $discount,
            'free_shipping' => (bool) $coupon->free_shipping,
        ];
    }

    /** Convenience wrapper for anywhere holding a cart. */
    public function evaluateCart(?Coupon $coupon, Cart $cart, ?string $phone = null, ?int $userId = null): array
    {
        return $this->evaluate($coupon, $this->linesFromCart($cart), $phone, $userId);
    }

    /** The shape evaluate() expects, built from cart items. */
    public function linesFromCart(Cart $cart): array
    {
        return $cart->items->map(fn ($item) => [
            'category_id' => $item->product?->category_id,
            'brand_id'    => $item->product?->brand_id,
            'line_total'  => (float) $item->line_total,
        ])->all();
    }

    public function findByCode(?string $code): ?Coupon
    {
        $code = Str::upper(trim((string) $code));

        return $code === '' ? null : Coupon::where('code', $code)->first();
    }

    /* ---------- session helpers ---------- */

    public function remember(Coupon $coupon): void
    {
        session([self::SESSION_KEY => $coupon->code]);
    }

    public function forget(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    public function fromSession(): ?Coupon
    {
        return $this->findByCode(session(self::SESSION_KEY));
    }

    /* ---------- internals ---------- */

    /**
     * How much of the cart the coupon is allowed to discount. A coupon
     * scoped to a parent category covers everything beneath it.
     */
    private function eligibleTotal(Coupon $coupon, array $lines): float
    {
        if (! $coupon->category_id && ! $coupon->brand_id) {
            return (float) collect($lines)->sum('line_total');
        }

        $categoryIds = [];

        if ($coupon->category_id) {
            $categoryIds = Category::where('id', $coupon->category_id)
                ->orWhere('parent_id', $coupon->category_id)
                ->pluck('id')->all();
        }

        return (float) collect($lines)
            ->filter(function ($line) use ($coupon, $categoryIds) {
                if ($coupon->category_id && in_array($line['category_id'], $categoryIds)) {
                    return true;
                }

                return $coupon->brand_id && $line['brand_id'] == $coupon->brand_id;
            })
            ->sum('line_total');
    }

    private function hasOrderedBefore(?string $phone, ?int $userId): bool
    {
        if (! $phone && ! $userId) {
            return false;
        }

        return Order::whereNotIn('status', ['cancelled', 'returned'])
            ->where(function ($q) use ($phone, $userId) {
                if ($phone) {
                    $q->orWhere('customer_phone', $phone);
                }

                if ($userId) {
                    $q->orWhere('user_id', $userId);
                }
            })
            ->exists();
    }
}
