<?php

namespace App\Services;

use App\Models\Order;

/**
 * Moving an order from one status to another, with everything that goes
 * with it: the timestamp, the stock, the payment and the customer's notice.
 *
 * It lives here so that a person clicking in the panel and a courier
 * reporting a delivery both do exactly the same thing.
 */
class OrderStatus
{
    public function __construct(
        private OrderService $orders,
        private OrderMailer $mailer,
    ) {
    }

    /** Returns the sentence to show the admin, or null if nothing changed. */
    public function change(Order $order, string $to): ?string
    {
        $from = $order->status;

        if ($from === $to) {
            return null;
        }

        $wasLive  = ! in_array($from, ['cancelled', 'returned']);
        $nowDead  = in_array($to, ['cancelled', 'returned']);
        $wasDead  = ! $wasLive;
        $nowLive  = ! $nowDead;

        $stamps = [
            'confirmed' => 'confirmed_at',
            'shipped'   => 'shipped_at',
            'delivered' => 'delivered_at',
            'cancelled' => 'cancelled_at',
        ];

        $payload = ['status' => $to];

        if (isset($stamps[$to]) && ! $order->{$stamps[$to]}) {
            $payload[$stamps[$to]] = now();
        }

        // Delivered means the courier collected the cash.
        if ($to === 'delivered' && $order->payment_method === 'cod') {
            $payload['payment_status'] = 'paid';
        }

        $order->update($payload);

        // Stock goes back when an order dies, and comes off again if it is revived.
        if ($wasLive && $nowDead) {
            $this->orders->restock($order->load('items'));
            $message = __('Order cancelled and stock returned.');
        } elseif ($wasDead && $nowLive) {
            $message = __('Order reopened. Check stock — it was returned when the order was cancelled.');
        } else {
            $message = __('Status updated to :status.', ['status' => __(Order::STATUSES[$to])]);
        }

        $this->mailer->statusChanged($order->fresh(), $to);

        return $message;
    }
}
