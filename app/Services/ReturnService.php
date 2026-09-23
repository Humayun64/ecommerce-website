<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ReturnRequest;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReturnService
{
    /**
     * How many days after delivery a return can still be opened.
     *
     * This is the same `return_days` the returns policy page shows the
     * customer, on purpose. Two numbers for one promise is how they end up
     * disagreeing, and the customer always believes the one on the page.
     */
    public function windowDays(): int
    {
        return max(0, (int) Setting::get('return_days', 7));
    }

    public function isEnabled(): bool
    {
        return (string) Setting::get('returns_enabled', '1') === '1';
    }

    /**
     * Why this order cannot be returned, or null when it can.
     *
     * Returned as a sentence rather than a boolean so the storefront can
     * tell the customer what is actually wrong instead of hiding the button
     * and leaving them to guess.
     */
    public function blockedReason(Order $order): ?string
    {
        if (! $this->isEnabled()) {
            return __('Returns are not being accepted at the moment. Please call us.');
        }

        if ($order->status === 'returned') {
            return __('This order has already been returned in full.');
        }

        if ($order->status !== 'delivered' || ! $order->delivered_at) {
            return __('A return can be opened once the order has been delivered.');
        }

        $days   = $this->windowDays();
        $closes = $order->delivered_at->copy()->addDays($days);

        if (now()->greaterThan($closes)) {
            return __('The :days-day return window for this order closed on :date.', [
                'days' => $days,
                'date' => $closes->format('j M Y'),
            ]);
        }

        if (! $this->returnableItems($order)->count()) {
            return __('Every item on this order already has a return request open.');
        }

        return null;
    }

    /** The day the window shuts, for showing on the order page. */
    public function closesAt(Order $order)
    {
        return $order->delivered_at?->copy()->addDays($this->windowDays());
    }

    /**
     * Lines that can still be sent back: quantity ordered, less whatever is
     * already claimed on a request that has not been rejected.
     */
    public function returnableItems(Order $order)
    {
        $claimed = ReturnRequest::where('order_id', $order->id)
            ->where('status', '!=', 'rejected')
            ->get()
            ->groupBy('order_item_id')
            ->map(fn ($rows) => (int) $rows->sum('quantity'));

        return $order->items->filter(
            fn ($item) => $item->quantity - (int) $claimed->get($item->id, 0) > 0
        )->values();
    }

    public function remainingFor(OrderItem $item): int
    {
        $claimed = (int) ReturnRequest::where('order_item_id', $item->id)
            ->where('status', '!=', 'rejected')
            ->sum('quantity');

        return max(0, $item->quantity - $claimed);
    }

    /**
     * Open a request. Everything is re-checked here under a lock, because
     * the eligibility the customer saw was rendered seconds ago and the
     * window may have closed, or another tab may have claimed the item.
     */
    public function open(Order $order, array $input): ReturnRequest
    {
        return DB::transaction(function () use ($order, $input) {
            $order = Order::whereKey($order->id)->lockForUpdate()->first();
            $order->load('items');

            if ($reason = $this->blockedReason($order)) {
                throw ValidationException::withMessages(['order' => $reason]);
            }

            $item = $order->items->firstWhere('id', (int) ($input['order_item_id'] ?? 0));

            if (! $item) {
                throw ValidationException::withMessages([
                    'order_item_id' => __('Pick which item you are sending back.'),
                ]);
            }

            $remaining = $this->remainingFor($item);
            $quantity  = max(1, (int) ($input['quantity'] ?? 1));

            if ($quantity > $remaining) {
                throw ValidationException::withMessages([
                    'quantity' => trans_choice(
                        '{1}Only :count of that item can still be returned.|[2,*]Only :count of those can still be returned.',
                        $remaining,
                        ['count' => $remaining]
                    ),
                ]);
            }

            if (! isset(ReturnRequest::REASONS[$input['reason'] ?? ''])) {
                throw ValidationException::withMessages([
                    'reason' => __('Choose a reason for the return.'),
                ]);
            }

            return ReturnRequest::create([
                'order_id'       => $order->id,
                'order_item_id'  => $item->id,
                'user_id'        => $order->user_id,
                'product_name'   => $item->name,
                'variant_label'  => $item->variant_label,
                'customer_name'  => $order->customer_name,
                'customer_phone' => $order->customer_phone,
                'customer_email' => $order->customer_email,
                'quantity'       => $quantity,
                'reason'         => $input['reason'],
                'note'           => $input['note'] ?? null,
                'photo'          => $input['photo'] ?? null,
                // What they actually paid for these units. The delivery charge
                // is not refunded by default -- the courier was still paid.
                'refund_amount'  => round((float) $item->unit_price * $quantity, 2),
                'status'         => 'pending',
            ]);
        });
    }

    public function approve(ReturnRequest $request, ?string $note = null): void
    {
        $this->guardSettled($request);

        $request->update([
            'status'      => 'approved',
            'admin_note'  => $note ?: $request->admin_note,
            'approved_at' => now(),
            'rejected_at' => null,
        ]);
    }

    public function reject(ReturnRequest $request, ?string $note = null): void
    {
        $this->guardSettled($request);

        $request->update([
            'status'      => 'rejected',
            'admin_note'  => $note ?: $request->admin_note,
            'rejected_at' => now(),
            'approved_at' => null,
        ]);
    }

    /**
     * Money back to the customer.
     *
     * Restocking is a choice, not a rule: a leaking bottle or a torn box is
     * not going back on the shelf, so the admin decides. The stock move is
     * recorded on the row so a second click cannot double it.
     */
    public function refund(ReturnRequest $request, float $amount, bool $restock, ?string $note = null): void
    {
        if ($request->status === 'refunded') {
            throw ValidationException::withMessages([
                'status' => __('This return has already been refunded.'),
            ]);
        }

        if ($amount < 0) {
            throw ValidationException::withMessages([
                'refund_amount' => __('A refund cannot be negative.'),
            ]);
        }

        DB::transaction(function () use ($request, $amount, $restock, $note) {
            $fresh = ReturnRequest::whereKey($request->id)->lockForUpdate()->first();

            if ($fresh->status === 'refunded') {
                return;
            }

            if ($restock && ! $fresh->restocked && $fresh->order_item_id) {
                $this->putBack($fresh);
            }

            $fresh->update([
                'status'        => 'refunded',
                'refund_amount' => round($amount, 2),
                'admin_note'    => $note ?: $fresh->admin_note,
                'restocked'     => $restock ? true : $fresh->restocked,
                'approved_at'   => $fresh->approved_at ?: now(),
                'refunded_at'   => now(),
            ]);

            $this->settleOrder($fresh->order_id);
        });
    }

    private function putBack(ReturnRequest $request): void
    {
        $item = OrderItem::whereKey($request->order_item_id)->first();

        if (! $item) {
            return;
        }

        if ($item->product_variant_id) {
            ProductVariant::whereKey($item->product_variant_id)
                ->lockForUpdate()->first()?->increment('stock', $request->quantity);
        } elseif ($item->product_id) {
            Product::whereKey($item->product_id)
                ->lockForUpdate()->first()?->increment('stock', $request->quantity);
        }
    }

    /**
     * Once every unit on the order has been refunded the order itself is a
     * return, and the dashboard should stop counting it as a sale.
     */
    private function settleOrder(int $orderId): void
    {
        $order = Order::with('items')->find($orderId);

        if (! $order || $order->status === 'returned') {
            return;
        }

        $refunded = ReturnRequest::where('order_id', $orderId)
            ->where('status', 'refunded')
            ->get()
            ->groupBy('order_item_id')
            ->map(fn ($rows) => (int) $rows->sum('quantity'));

        $whole = $order->items->every(
            fn ($item) => (int) $refunded->get($item->id, 0) >= $item->quantity
        );

        if ($whole && $order->items->isNotEmpty()) {
            $order->update(['status' => 'returned']);
        }
    }

    private function guardSettled(ReturnRequest $request): void
    {
        if ($request->status === 'refunded') {
            throw ValidationException::withMessages([
                'status' => __('This return has already been refunded, so it cannot be changed.'),
            ]);
        }
    }

    /** Totals for the four cards at the top of the admin screen. */
    public function counts(): array
    {
        $rows = ReturnRequest::selectRaw('status, count(*) as n')
            ->groupBy('status')
            ->pluck('n', 'status');

        return [
            'pending'  => (int) ($rows['pending'] ?? 0),
            'approved' => (int) ($rows['approved'] ?? 0),
            'rejected' => (int) ($rows['rejected'] ?? 0),
            'refunded' => (int) ($rows['refunded'] ?? 0),
        ];
    }

    public function refundedTotal(): float
    {
        return (float) ReturnRequest::where('status', 'refunded')->sum('refund_amount');
    }
}
