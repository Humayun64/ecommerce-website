<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Setting;
use App\Models\SmsLog;

/**
 * Decides which SMS goes out when, and what it says.
 *
 * Every message is a template you edit in admin, so the wording, the
 * language and whether an event sends at all are yours to change without
 * touching code.
 */
class SmsNotifier
{
    public function __construct(private SmsGateway $gateway)
    {
    }

    /** The events that can send, in the order a customer meets them. */
    public const EVENTS = [
        'placed'    => 'Order placed',
        'confirmed' => 'Order confirmed',
        'shipped'   => 'Out for delivery',
        'delivered' => 'Delivered',
        'cancelled' => 'Order cancelled',
        'paid'      => 'Payment verified',
    ];

    public const DEFAULTS = [
        'placed'    => 'Thank you {name}! Order {order} for Tk {total} is received. We will call to confirm. - {store}',
        'confirmed' => 'Order {order} is confirmed and being packed. Tk {total} payable on delivery. - {store}',
        'shipped'   => 'Order {order} is out for delivery today. Please keep Tk {total} ready. {tracking} - {store}',
        'delivered' => 'Order {order} delivered. Thank you for shopping with us! - {store}',
        'cancelled' => 'Order {order} has been cancelled. Call {phone} if this is a mistake. - {store}',
        'paid'      => 'Payment of Tk {total} for order {order} is confirmed. Thank you! - {store}',
    ];

    public function template(string $event): string
    {
        $saved = trim((string) Setting::get("sms_tpl_{$event}", ''));

        return $saved !== '' ? $saved : (self::DEFAULTS[$event] ?? '');
    }

    public function isOn(string $event): bool
    {
        // Order placed is on by default; the rest are opt-in, because every
        // message costs money and not every shop wants all six.
        $fallback = $event === 'placed' ? '1' : '0';

        return (string) Setting::get("sms_on_{$event}", $fallback) === '1';
    }

    /** Fill a template from an order. Unknown placeholders are left alone. */
    public function render(string $template, Order $order): string
    {
        $values = [
            '{name}'     => $this->firstName($order->customer_name),
            '{order}'    => $order->order_number,
            '{total}'    => number_format((float) $order->total),
            '{store}'    => Setting::get('store_name', 'AMJR Global'),
            '{phone}'    => Setting::get('store_phone', ''),
            '{status}'   => $order->status_label ?? $order->status,
            '{tracking}' => $order->tracking_number
                ? 'Tracking: ' . $order->tracking_number
                : '',
            '{courier}'  => (string) $order->courier,
            '{items}'    => (string) $order->items()->count(),
        ];

        $text = strtr($template, $values);

        // A blank {tracking} leaves a double space behind it.
        return trim(preg_replace('/ {2,}/', ' ', $text));
    }

    /**
     * Send the message for this event, once.
     *
     * The unique index on (order_id, event) is what makes "once" true even
     * if two requests race; this check just avoids the wasted attempt.
     */
    public function notify(Order $order, string $event): ?SmsLog
    {
        if (! isset(self::EVENTS[$event]) || ! $this->isOn($event)) {
            return null;
        }

        if (! $this->gateway->enabled()) {
            return null;
        }

        $already = SmsLog::where('order_id', $order->id)
            ->where('event', $event)
            ->exists();

        if ($already) {
            return null;
        }

        $template = $this->template($event);

        if (trim($template) === '') {
            return null;
        }

        return $this->gateway->send(
            $order->customer_phone,
            $this->render($template, $order),
            $event,
            $order->id,
        );
    }

    /** Which event, if any, a new order status should announce. */
    public function eventForStatus(string $status): ?string
    {
        return match ($status) {
            'confirmed', 'processing' => 'confirmed',
            'shipped'                 => 'shipped',
            'delivered'               => 'delivered',
            'cancelled'               => 'cancelled',
            default                   => null,
        };
    }

    private function firstName(?string $name): string
    {
        $name = trim((string) $name);

        if ($name === '') {
            return 'there';
        }

        return explode(' ', $name)[0];
    }

    /** A rough monthly bill, so the cost is visible before switching things on. */
    public function estimateParts(): array
    {
        $out = [];

        foreach (self::EVENTS as $event => $label) {
            $text = $this->template($event);

            $out[$event] = [
                'label'   => $label,
                'on'      => $this->isOn($event),
                'chars'   => mb_strlen($text),
                'parts'   => $text === '' ? 0 : SmsLog::partsFor($text),
                'unicode' => SmsLog::isUnicode($text),
            ];
        }

        return $out;
    }
}
