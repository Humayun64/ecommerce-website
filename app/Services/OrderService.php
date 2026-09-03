<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\ShippingZone;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function __construct(private CartService $cart)
    {
    }

    /**
     * Turn a cart into an order.
     *
     * Everything happens inside one transaction with the stock rows locked.
     * Two people buying the last bottle at the same moment must not both
     * succeed — the second one waits for the lock, re-reads the real stock,
     * and is rejected rather than overselling.
     */
    public function place(Cart $cart, array $input): Order
    {
        if ($cart->items->isEmpty()) {
            throw ValidationException::withMessages([
                'cart' => __('Your cart is empty.'),
            ]);
        }

        return DB::transaction(function () use ($cart, $input) {
            $lines    = [];
            $subtotal = 0.0;
            $costs    = 0.0;
            $costKnown = true;

            foreach ($cart->items as $item) {
                // Lock the row that owns the stock for this line.
                if ($item->product_variant_id) {
                    $stockRow = ProductVariant::whereKey($item->product_variant_id)
                        ->lockForUpdate()->first();
                } else {
                    $stockRow = Product::whereKey($item->product_id)
                        ->lockForUpdate()->first();
                }

                $product = $item->product;

                if (! $stockRow || ! $product || ! $product->is_active) {
                    throw ValidationException::withMessages([
                        'cart' => __(':name is no longer available.', ['name' => $product->name ?? __('An item')]),
                    ]);
                }

                if ($stockRow->stock < $item->quantity) {
                    throw ValidationException::withMessages([
                        'cart' => __('Only :n of :name left. Please adjust your cart.', [
                            'n'    => $stockRow->stock,
                            'name' => $product->name,
                        ]),
                    ]);
                }

                $unitPrice = (float) ($item->variant?->price ?? $product->price);
                $unitCost  = $item->variant?->cost_price ?? $product->cost_price;

                if ($unitCost === null) {
                    $costKnown = false;
                } else {
                    $costs += (float) $unitCost * $item->quantity;
                }

                $lineTotal = $unitPrice * $item->quantity;
                $subtotal += $lineTotal;

                $lines[] = [
                    'product_id'         => $product->id,
                    'product_variant_id' => $item->product_variant_id,
                    'name'               => $product->name,
                    'variant_label'      => $item->variant?->name,
                    'sku'                => $item->variant?->sku ?? $product->sku,
                    'brand_name'         => $product->brand?->name,
                    'unit_price'         => $unitPrice,
                    'unit_cost'          => $unitCost,
                    'quantity'           => $item->quantity,
                    'line_total'         => $lineTotal,
                ];

                // Decrement inside the same transaction as the order insert.
                $stockRow->decrement('stock', $item->quantity);
            }

            $zone     = ShippingZone::find($input['shipping_zone_id'] ?? null);
            $freeOver = (float) Setting::get('free_delivery_over', 2000);
            $delivery = ($freeOver > 0 && $subtotal >= $freeOver) ? 0.0 : (float) ($zone->rate ?? 0);

            $order = Order::create([
                'order_number'       => $this->generateNumber(),
                'user_id'            => $input['user_id'] ?? null,

                'customer_name'      => $input['customer_name'],
                'customer_phone'     => $input['customer_phone'],
                'customer_email'     => $input['customer_email'] ?? null,

                'shipping_address'   => $input['shipping_address'],
                'shipping_area'      => $input['shipping_area'] ?? null,
                'shipping_zone_id'   => $zone?->id,
                'shipping_zone_name' => $zone?->name,

                'subtotal'           => $subtotal,
                'delivery_charge'    => $delivery,
                'discount'           => 0,
                'total'              => $subtotal + $delivery,
                'cost_total'         => $costKnown ? $costs : null,

                'payment_method'     => 'cod',
                'payment_status'     => 'pending',
                'status'             => 'pending',
                'customer_note'      => $input['customer_note'] ?? null,
            ]);

            $order->items()->createMany($lines);

            $cart->items()->delete();

            return $order;
        });
    }

    /** AMJR-260903-4817, retried on the very unlikely collision. */
    private function generateNumber(): string
    {
        do {
            $number = 'AMJR-' . now()->format('ymd') . '-' . random_int(1000, 9999);
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }

    /** Putting stock back when an order is cancelled. */
    public function restock(Order $order): void
    {
        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                if ($item->product_variant_id) {
                    ProductVariant::whereKey($item->product_variant_id)
                        ->lockForUpdate()->first()?->increment('stock', $item->quantity);
                } elseif ($item->product_id) {
                    Product::whereKey($item->product_id)
                        ->lockForUpdate()->first()?->increment('stock', $item->quantity);
                }
            }
        });
    }
}
