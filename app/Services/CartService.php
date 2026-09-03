<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * The cart is always a database row, for guests and logged-in customers alike.
 * Guests are identified by a token in the session; logging in moves the cart
 * onto the user. One code path, so the merge cannot drift out of sync.
 */
class CartService
{
    public const SESSION_KEY = 'cart_token';

    public function current(bool $createIfMissing = true): ?Cart
    {
        if ($user = Auth::user()) {
            $cart = Cart::firstOrCreate(['user_id' => $user->id]);

            return $cart->loadLines();
        }

        $token = session(self::SESSION_KEY);

        if (! $token) {
            if (! $createIfMissing) {
                return null;
            }

            $token = (string) Str::uuid();
            session([self::SESSION_KEY => $token]);
        }

        $cart = Cart::firstOrCreate(['token' => $token]);

        return $cart->loadLines();
    }

    public function count(): int
    {
        $cart = $this->current(false);

        return $cart ? $cart->count : 0;
    }

    /**
     * @return array{ok: bool, message: string, capped: bool}
     */
    public function add(Product $product, ?ProductVariant $variant, int $quantity): array
    {
        $quantity = max(1, $quantity);

        if ($product->has_variants && ! $variant) {
            return ['ok' => false, 'message' => __('Choose an option first.'), 'capped' => false];
        }

        if ($variant && $variant->product_id !== $product->id) {
            return ['ok' => false, 'message' => __('That option does not belong to this product.'), 'capped' => false];
        }

        $available = (int) ($variant?->stock ?? $product->stock);

        if ($available < 1) {
            return ['ok' => false, 'message' => __('That is out of stock.'), 'capped' => false];
        }

        $cart = $this->current();

        $item = $cart->items()->firstOrNew([
            'product_id'         => $product->id,
            'product_variant_id' => $variant?->id,
        ]);

        $wanted = ($item->quantity ?? 0) + $quantity;
        $capped = $wanted > $available;

        $item->quantity = min($wanted, $available);
        $item->save();

        return [
            'ok'      => true,
            'capped'  => $capped,
            'message' => $capped
                ? __('Only :n in stock, so we added what we could.', ['n' => $available])
                : __('Added to your cart.'),
        ];
    }

    public function update(CartItem $item, int $quantity): array
    {
        if ($quantity < 1) {
            $item->delete();

            return ['ok' => true, 'message' => __('Removed from your cart.'), 'capped' => false];
        }

        $available = $item->available;

        if ($available < 1) {
            $item->delete();

            return ['ok' => true, 'message' => __('That item sold out and was removed.'), 'capped' => false];
        }

        $capped = $quantity > $available;
        $item->update(['quantity' => min($quantity, $available)]);

        return [
            'ok'      => true,
            'capped'  => $capped,
            'message' => $capped
                ? __('Only :n in stock.', ['n' => $available])
                : __('Cart updated.'),
        ];
    }

    public function remove(CartItem $item): void
    {
        $item->delete();
    }

    public function clear(): void
    {
        $this->current()?->items()->delete();
    }

    /**
     * Called on login: fold the guest cart into the user's cart, then bin it.
     * Quantities add up, capped at whatever stock allows.
     */
    public function mergeGuestCart(int $userId): void
    {
        $token = session(self::SESSION_KEY);

        if (! $token) {
            return;
        }

        $guest = Cart::where('token', $token)->with('items')->first();
        session()->forget(self::SESSION_KEY);

        if (! $guest) {
            return;
        }

        if (! $guest->items->count()) {
            $guest->delete();

            return;
        }

        $mine = Cart::firstOrCreate(['user_id' => $userId]);

        foreach ($guest->items as $line) {
            $item = $mine->items()->firstOrNew([
                'product_id'         => $line->product_id,
                'product_variant_id' => $line->product_variant_id,
            ]);

            $item->quantity = ($item->quantity ?? 0) + $line->quantity;
            $item->save();

            // Re-read stock and trim if the total now exceeds it.
            $item->refresh()->load(['product', 'variant']);

            if ($item->quantity > $item->available) {
                $item->update(['quantity' => max(1, $item->available)]);
            }
        }

        $guest->items()->delete();
        $guest->delete();
    }
}
