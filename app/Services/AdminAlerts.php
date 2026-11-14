<?php

namespace App\Services;

use App\Models\ContactMessage;
use App\Models\Order;
use App\Models\Payment;
use App\Models\ReturnRequest;
use App\Models\Review;

/**
 * What is waiting for the shop owner, for the badges in the admin menu,
 * the bell in the top bar and the "new order" sound.
 *
 * Every figure is "things that still need a person", not "things that
 * exist": pending orders, payments to check, returns to decide, unread
 * messages, reviews to approve.
 */
class AdminAlerts
{
    public const KINDS = ['orders', 'payments', 'returns', 'messages', 'reviews'];

    /** @return array<string,int> */
    public function counts(): array
    {
        return [
            'orders'   => $this->safe(fn () => Order::where('status', 'pending')->count()),
            'payments' => $this->safe(fn () => Payment::where('status', 'pending')->count()),
            'returns'  => $this->safe(fn () => ReturnRequest::where('status', 'pending')->count()),
            'messages' => $this->safe(fn () => ContactMessage::where('status', 'new')->count()),
            'reviews'  => $this->safe(fn () => Review::where('status', 'pending')->count()),
        ];
    }

    /**
     * The newest row of each kind. The browser remembers the last one it
     * saw; a higher number means something arrived since.
     *
     * @return array<string,int>
     */
    public function latest(): array
    {
        return [
            'orders'   => $this->safe(fn () => (int) Order::max('id')),
            'payments' => $this->safe(fn () => (int) Payment::where('status', 'pending')->max('id')),
            'returns'  => $this->safe(fn () => (int) ReturnRequest::max('id')),
            'messages' => $this->safe(fn () => (int) ContactMessage::max('id')),
            'reviews'  => $this->safe(fn () => (int) Review::max('id')),
        ];
    }

    /** The ten most recent things waiting, newest first, for the bell's list. */
    public function items(): array
    {
        $items = [];

        try {
            foreach (Order::where('status', 'pending')->latest('id')->take(6)->get() as $o) {
                $items[] = $this->item('orders', $o->id, 'New order ' . $o->order_number,
                    $o->customer_name . ' · ৳' . number_format((float) $o->total), route('admin.orders.show', $o), $o->created_at);
            }

            foreach (Payment::with('order')->where('status', 'pending')->latest('id')->take(5)->get() as $p) {
                $items[] = $this->item('payments', $p->id, 'Payment to check · ৳' . number_format((float) $p->amount),
                    $p->method_name . ($p->order ? ' · ' . $p->order->order_number : ''), route('admin.payments.index'), $p->created_at);
            }

            foreach (ReturnRequest::where('status', 'pending')->latest('id')->take(5)->get() as $r) {
                $items[] = $this->item('returns', $r->id, 'Return request',
                    $r->product_name . ' · ' . $r->customer_name, route('admin.returns.show', $r), $r->created_at);
            }

            foreach (ContactMessage::where('status', 'new')->latest('id')->take(5)->get() as $m) {
                $items[] = $this->item('messages', $m->id, 'Message from ' . $m->name,
                    (string) ($m->topic ?: mb_strimwidth((string) $m->message, 0, 60, '…')), route('admin.messages.show', $m), $m->created_at);
            }

            foreach (Review::where('status', 'pending')->latest('id')->take(5)->get() as $v) {
                $items[] = $this->item('reviews', $v->id, 'Review waiting · ' . $v->rating . ' star' . ($v->rating == 1 ? '' : 's'),
                    (string) $v->reviewer_name, route('admin.reviews.index'), $v->created_at);
            }
        } catch (\Throwable $e) {
            // A missing table on a half-migrated site should not break the panel.
        }

        usort($items, fn ($a, $b) => $b['at'] <=> $a['at']);

        return array_slice($items, 0, 10);
    }

    private function item(string $kind, int $id, string $title, string $sub, string $url, $when): array
    {
        return [
            'kind'  => $kind,
            'id'    => $id,
            'title' => $title,
            'sub'   => $sub,
            'url'   => $url,
            'at'    => $when ? $when->getTimestamp() : 0,
            'ago'   => $when ? $when->diffForHumans() : '',
        ];
    }

    private function safe(callable $count): int
    {
        try {
            return (int) $count();
        } catch (\Throwable $e) {
            return 0;
        }
    }
}
