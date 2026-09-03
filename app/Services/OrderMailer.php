<?php

namespace App\Services;

use App\Mail\OrderPlaced;
use App\Mail\OrderStatusChanged;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Every send is wrapped. A mail server that is down, misconfigured or slow
 * must never take an order down with it — the order is already committed by
 * the time we get here, and a failed email is a footnote, not a failure.
 */
class OrderMailer
{
    public function placed(Order $order): void
    {
        if (! $order->customer_email) {
            return;
        }

        $this->send($order->customer_email, new OrderPlaced($order), $order);
    }

    public function statusChanged(Order $order, string $status): void
    {
        if (! $order->customer_email) {
            return;
        }

        $lines = [
            'confirmed' => [
                __('Your order is confirmed'),
                __('We have your order and it is being packed. We will let you know when it leaves us.'),
            ],
            'shipped' => [
                __('Your order is on the way'),
                __('Your parcel has left us and is with the courier. Please keep your phone reachable — they will call before delivery.'),
            ],
            'delivered' => [
                __('Your order was delivered'),
                __('Thank you for shopping with us. If anything is not right, reply to this email within the return window.'),
            ],
            'cancelled' => [
                __('Your order was cancelled'),
                __('This order has been cancelled and nothing will be delivered. If this is a mistake, please call us.'),
            ],
        ];

        if (! isset($lines[$status])) {
            return;
        }

        [$headline, $body] = $lines[$status];

        $this->send($order->customer_email, new OrderStatusChanged($order, $headline, $body), $order);
    }

    private function send(string $to, $mailable, Order $order): void
    {
        try {
            Mail::to($to)->send($mailable);
        } catch (\Throwable $e) {
            Log::warning('Order email failed', [
                'order'   => $order->order_number,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
