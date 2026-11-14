<?php

namespace App\Services\Couriers;

use App\Models\Order;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * RedX. The access token comes from the RedX merchant panel (Developer API).
 * RedX has a separate test server, used while "Test mode" is ticked.
 */
class RedxCourier implements Courier
{
    public function label(): string
    {
        return 'RedX';
    }

    public function fields(): array
    {
        return ['secret' => 'Access token'];
    }

    public function needsArea(): bool
    {
        return true;
    }

    public function hasSandbox(): bool
    {
        return true;
    }

    private function base(array $creds): string
    {
        return $creds['sandbox']
            ? 'https://sandbox.redx.com.bd/v1.0.0-beta'
            : 'https://openapi.redx.com.bd/v1.0.0-beta';
    }

    private function client(array $creds)
    {
        $token = preg_replace('/^Bearer\s+/i', '', trim($creds['secret']));

        return Http::withHeaders(['API-ACCESS-TOKEN' => 'Bearer ' . $token])
            ->acceptJson()->asJson()->timeout(25);
    }

    public function test(array $creds): string
    {
        $count = count($this->allAreas($creds, fresh: true));

        if ($count === 0) {
            throw new RuntimeException('RedX answered but returned no delivery areas.');
        }

        return 'Connected. RedX returned ' . number_format($count) . ' delivery areas' . ($creds['sandbox'] ? ' (test mode).' : '.');
    }

    public function book(array $creds, Order $order, array $input): array
    {
        if (empty($input['area_id']) || empty($input['area_name'])) {
            throw new RuntimeException('Choose the RedX delivery area first.');
        }

        $order->loadMissing('items');

        $response = $this->client($creds)->post($this->base($creds) . '/parcel', [
            'customer_name'          => $order->customer_name,
            'customer_phone'         => $order->customer_phone,
            'delivery_area'          => $input['area_name'],
            'delivery_area_id'       => (int) $input['area_id'],
            'customer_address'       => Str::limit(trim($order->shipping_address . ($order->shipping_area ? ', ' . $order->shipping_area : '')), 250, ''),
            'merchant_invoice_id'    => $order->order_number,
            'cash_collection_amount' => (string) round((float) $input['cod']),
            'parcel_weight'          => max(1, (int) $input['weight']),
            'instruction'            => Str::limit((string) ($input['note'] ?? ''), 200, ''),
            'value'                  => (int) round((float) $order->subtotal),
            'parcel_details_json'    => $order->items->map(fn ($item) => [
                'name'     => Str::limit((string) $item->name, 100, ''),
                'category' => 'general',
                'value'    => (int) round((float) $item->unit_price),
            ])->values()->all(),
        ]);

        $body     = $response->json();
        $tracking = is_array($body) ? ($body['tracking_id'] ?? ($body['parcel']['tracking_id'] ?? null)) : null;

        if (! $response->successful() || ! $tracking) {
            throw new RuntimeException($this->why($body, 'RedX did not create the parcel.'));
        }

        return ['ref' => (string) $tracking, 'tracking' => (string) $tracking, 'status' => 'pickup-pending'];
    }

    public function status(array $creds, Order $order): array
    {
        $response = $this->client($creds)->get($this->base($creds) . '/parcel/info/' . rawurlencode((string) $order->courier_ref));
        $body     = $response->json();
        $status   = is_array($body) ? ($body['parcel']['status'] ?? ($body['status'] ?? null)) : null;

        if (! $response->successful() || ! is_string($status) || $status === '') {
            throw new RuntimeException($this->why($body, 'RedX did not return a status.'));
        }

        $low   = strtolower($status);
        $state = match (true) {
            $low === 'delivered'                                                                   => 'delivered',
            str_contains($low, 'return') || str_contains($low, 'cancel') || str_contains($low, 'reject') => 'problem',
            default                                                                                => 'moving',
        };

        return ['status' => $status, 'state' => $state];
    }

    public function trackUrl(string $tracking): string
    {
        return 'https://redx.com.bd/track-parcel/?trackingId=' . rawurlencode($tracking);
    }

    public function areas(array $creds, string $search): array
    {
        $search = mb_strtolower(trim($search));

        if (mb_strlen($search) < 2) {
            return [];
        }

        $hits = array_filter($this->allAreas($creds), fn ($a) => str_contains(mb_strtolower($a['name']), $search));

        return array_slice(array_values($hits), 0, 30);
    }

    /** The whole list changes rarely, so it is fetched once a day. */
    private function allAreas(array $creds, bool $fresh = false): array
    {
        $key = 'couriers.redx.areas.' . ($creds['sandbox'] ? 'test' : 'live');

        if ($fresh) {
            Cache::forget($key);
        }

        return Cache::remember($key, now()->addDay(), function () use ($creds) {
            $response = $this->client($creds)->get($this->base($creds) . '/areas');
            $body     = $response->json();

            if (! $response->successful() || ! is_array($body) || ! isset($body['areas']) || ! is_array($body['areas'])) {
                throw new RuntimeException($this->why($body, 'RedX did not accept this access token.'));
            }

            return array_values(array_map(fn ($a) => [
                'id'   => (int) ($a['id'] ?? 0),
                'name' => trim((string) ($a['name'] ?? '') . (! empty($a['division_name']) ? ' — ' . $a['division_name'] : '')),
                'area' => (string) ($a['name'] ?? ''),
            ], array_filter($body['areas'], fn ($a) => is_array($a) && ! empty($a['id']) && ! empty($a['name']))));
        });
    }

    private function why($body, string $fallback): string
    {
        if (is_array($body) && ! empty($body['message'])) {
            return 'RedX: ' . Str::limit(is_string($body['message']) ? $body['message'] : json_encode($body['message']), 300);
        }

        return $fallback;
    }
}
