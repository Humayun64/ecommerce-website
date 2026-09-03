<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private CartService $cart)
    {
    }

    public function index()
    {
        return view('site.cart.index', [
            'cart' => $this->cart->current(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'variant_id' => ['nullable', 'exists:product_variants,id'],
            'quantity'   => ['nullable', 'integer', 'min:1', 'max:99'],
        ]);

        $product = Product::active()->findOrFail($data['product_id']);
        $variant = ! empty($data['variant_id'])
            ? ProductVariant::find($data['variant_id'])
            : null;

        $result = $this->cart->add($product, $variant, (int) ($data['quantity'] ?? 1));

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
