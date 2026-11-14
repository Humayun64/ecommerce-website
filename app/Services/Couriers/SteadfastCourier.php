<?php

namespace App\Services\Couriers;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Steadfast Courier. The API key and secret key come from the Steadfast
 * merchant panel. There is no test server — every booking is real.
 */
class SteadfastCourier implements Courier
{
    private const BASE = 'https://portal.packzy.com/api/v1';

    public function label(): string
    {
        return 'Steadfast';
    }

    public function fields(): array
    {
        return ['key' => 'API key', 'secret' => 'Secret key'];
    }

    public function needsArea(): bool
    {
        return false;
    }

    public function hasSandbox(): bool
    {
        return false;
    }

    private function client(array $creds)
    {
        return Http::withHeaders([
            'Api-Key'    => $creds['key'],
            'Secret-Key' => $creds['secret'],
        ])->acceptJson()->asJson()->timeout(20);
    }

    public function test(array $creds): string
    {
        $response = $this->client($creds)->get(self::BASE . '/get_balance');
        $body     = $response->json();

        if (! $response->successful() || ! is_array($body) || (int) ($body['status'] ?? 0) !== 200) {
            throw new RuntimeException($this->why($body, 'Steadfast did not accept these keys.'));
        }

        return 'Connected. Steadfast balance: ৳' . number_format((float) ($body['current_balance'] ?? 0)) . '.';
    }

    public function book(array $creds, Order $order, array $input): array
    {
        $address = trim($order->shipping_address . ($order->shipping_area ? ', ' . $order->shipping_area : ''));

        $response = $this->client($creds)->post(self::BASE . '/create_order', [
            'invoice'           => $order->order_number,
            'recipient_name'    => $order->customer_name,
            'recipient_phone'   => $order->customer_phone,
            'recipient_address' => Str::limit($address, 250, ''),
            'cod_amount'        => round((float) $input['cod'], 2),
            'note'              => Str::limit((string) ($input['note'] ?? ''), 200, ''),
        ]);

        $body = $response->json();
        $c    = is_array($body) ? ($body['consignment'] ?? null) : null;

        if (! $response->successful() || ! is_array($c) || empty($c['consignment_id']) || empty($c['tracking_code'])) {
            throw new RuntimeException($this->why($body, 'Steadfast did not create the parcel.'));
        }

        return [
            'ref'      => (string) $c['consignment_id'],
            'tracking' => (string) $c['tracking_code'],
            'status'   => (string) ($c['status'] ?? 'in_review'),
        ];
    }

    public function status(array $creds, Order $order): array
    {
        $response = $this->client($creds)->get(self::BASE . '/status_by_cid/' . rawurlencode((string) $order->courier_ref));
        $body     = $response->json();

        if (! $response->successful() || ! is_array($body) || empty($body['delivery_status'])) {
            throw new RuntimeException($this->why($body, 'Steadfast did not return a status.'));
        }

        $status = (string) $body['delivery_status'];

        // Only a final "delivered" counts. A partial delivery or a cancel
        // needs a person to look, and "approval pending" is not final yet.
        $state = match (true) {
            $status === 'delivered'                                      => 'delivered',
            in_array($status, ['partial_delivered', 'cancelled'], true) => 'problem',
            default                                                      => 'moving',
        };

        return ['status' => $status, 'state' => $state];
    }

    public function trackUrl(string $tracking): string
    {
        return 'https://steadfast.com.bd/t/' . rawurlencode($tracking);
    }

    public function areas(array $creds, string $search): array
    {
        return [];
    }

    private function why($body, string $fallback): string
    {
        if (is_array($body)) {
            if (! empty($body['errors']) && is_array($body['errors'])) {
                return 'Steadfast: ' . Str::limit(implode(' ', array_map(fn ($e) => is_array($e) ? implode(' ', $e) : (string) $e, $body['errors'])), 300);
            }
            if (! empty($body['message']) && is_string($body['message'])) {
                return 'Steadfast: ' . Str::limit($body['message'], 300);
            }
        }

        return $fallback;
    }
}
