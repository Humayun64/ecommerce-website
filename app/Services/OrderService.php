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

    /** Storefront checkout: turn the customer's cart into an order. */
    public function place(Cart $cart, array $input): Order
    {
        if ($cart->items->isEmpty()) {
            throw ValidationException::withMessages(['cart' => __('Your cart is empty.')]);
        }

        $lines = $cart->items->map(fn ($item) => [
            'product_id' => $item->product_id,
            'variant_id' => $item->product_variant_id,
            'quantity'   => $item->quantity,
        ])->all();

        $order = $this->commit($lines, $input);

        $cart->items()->delete();

        return $order;
    }

    /**
     * Admin-entered order, for the ones that arrive by Facebook message
     * or phone call. Same locking and snapshotting as the storefront.
     */
    public function placeManual(array $lines, array $input): Order
    {
        $lines = array_values(array_filter(
            $lines,
            fn ($line) => ! empty($line['product_id']) && (int) ($line['quantity'] ?? 0) > 0
        ));

        if (! $lines) {
            throw ValidationException::withMessages(['lines' => __('Add at least one product.')]);
        }

        return $this->commit($lines, $input);
    }

    /**
     * The only place stock is ever taken.
     *
     * One transaction, rows locked with SELECT … FOR UPDATE, real stock
     * re-read after the lock. Two people buying the last bottle at the same
     * instant cannot both succeed: the second waits, sees zero, is refused.
     */
    private function commit(array $lines, array $input): Order
    {
        return DB::transaction(function () use ($lines, $input) {
            $rows      = [];
            $subtotal  = 0.0;
            $costs     = 0.0;
            $costKnown = true;

            foreach ($lines as $line) {
                $quantity = max(1, (int) $line['quantity']);
                $variantId = $line['variant_id'] ?? null;

                $product = Product::with('brand')->find($line['product_id']);

                if (! $product) {
                    throw ValidationException::withMessages([
                        'cart' => __('An item in this order no longer exists.'),
                    ]);
                }

                $variant = null;

                if ($variantId) {
                    $variant = ProductVariant::whereKey($variantId)->lockForUpdate()->first();
                    $stockRow = $variant;
                } else {
                    $stockRow = Product::whereKey($product->id)->lockForUpdate()->first();
                }

                if (! $stockRow) {
                    throw ValidationException::withMessages([
                        'cart' => __(':name is no longer available.', ['name' => $product->name]),
                    ]);
                }

                if ($stockRow->stock < $quantity) {
                    throw ValidationException::withMessages([
                        'cart' => __('Only :n of :name left.', [
                            'n' => $stockRow->stock, 'name' => $product->name,
                        ]),
                    ]);
                }

                $unitPrice = (float) ($line['unit_price'] ?? $variant?->price ?? $product->price);
                $unitCost  = $variant?->cost_price ?? $product->cost_price;

                if ($unitCost === null) {
                    $costKnown = false;
                } else {
                    $costs += (float) $unitCost * $quantity;
                }

                $lineTotal = $unitPrice * $quantity;
                $subtotal += $lineTotal;

                $rows[] = [
                    'product_id'         => $product->id,
                    'product_variant_id' => $variant?->id,
                    'name'               => $product->name,
                    'variant_label'      => $variant?->name,
                    'sku'                => $variant?->sku ?? $product->sku,
                    'brand_name'         => $product->brand?->name,
                    'unit_price'         => $unitPrice,
                    'unit_cost'          => $unitCost,
                    'quantity'           => $quantity,
                    'line_total'         => $lineTotal,
                ];

                $stockRow->decrement('stock', $quantity);
            }

            $zone     = ShippingZone::find($input['shipping_zone_id'] ?? null);
            $freeOver = (float) Setting::get('free_delivery_over', 2000);

            $delivery = array_key_exists('delivery_charge', $input) && $input['delivery_charge'] !== null
                ? (float) $input['delivery_charge']
                : (($freeOver > 0 && $subtotal >= $freeOver) ? 0.0 : (float) ($zone->rate ?? 0));

            $discount = (float) ($input['discount'] ?? 0);

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
                'discount'           => $discount,
                'total'              => max(0, $subtotal + $delivery - $discount),
                'cost_total'         => $costKnown ? $costs : null,

                'payment_method'     => $input['payment_method'] ?? 'cod',
                'payment_status'     => 'pending',
                'status'             => $input['status'] ?? 'pending',
                'customer_note'      => $input['customer_note'] ?? null,
                'admin_note'         => $input['admin_note'] ?? null,
            ]);

            $order->items()->createMany($rows);

            return $order;
        });
    }

    private function generateNumber(): string
    {
        do {
            $number = 'AMJR-' . now()->format('ymd') . '-' . random_int(1000, 9999);
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }

    /** Cancelling or returning an order puts the stock back. */
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
