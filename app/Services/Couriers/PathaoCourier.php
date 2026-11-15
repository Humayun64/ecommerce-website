<?php

namespace App\Services\Couriers;

use App\Models\Order;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Pathao Courier. The client ID and secret come from the Pathao merchant
 * panel (Developer API); the email and password are the merchant login.
 *
 * Pathao works out the city and zone from the written address, so nothing
 * has to be picked from a list when a parcel is booked.
 */
class PathaoCourier implements Courier
{
    public function label(): string
    {
        return 'Pathao';
    }

    public function fields(): array
    {
        return [
            'key'      => 'Client ID',
            'secret'   => 'Client secret',
            'username' => 'Merchant email',
            'password' => 'Merchant password',
        ];
    }

    public function needsArea(): bool
    {
        return false;
    }

    public function hasSandbox(): bool
    {
        return true;
    }

    private function base(array $creds): string
    {
        return $creds['sandbox']
            ? 'https://courier-api-sandbox.pathao.com'
            : 'https://api-hermes.pathao.com';
    }

    /**
     * Pathao hands out a token that lasts for days. It is kept (encrypted)
     * until shortly before it runs out, so most bookings are one request.
     */
    private function token(array $creds, bool $fresh = false): string
    {
        $name = 'courier.pathao.token.' . sha1($this->base($creds) . '|' . $creds['key'] . '|' . $creds['username']);

        if (! $fresh && ($saved = Cache::get($name))) {
            try {
                return Crypt::decryptString($saved);
            } catch (\Throwable $e) {
                // Fall through and ask for a new one.
            }
        }

        $response = Http::acceptJson()->asJson()->timeout(20)->post($this->base($creds) . '/aladdin/api/v1/issue-token', [
            'client_id'     => $creds['key'],
            'client_secret' => $creds['secret'],
            'grant_type'    => 'password',
            'username'      => $creds['username'],
            'password'      => $creds['password'],
        ]);

        $body  = $response->json();
        $token = is_array($body) ? (string) ($body['access_token'] ?? '') : '';

        if (! $response->successful() || $token === '') {
            throw new RuntimeException($this->why($body, 'Pathao did not accept this login. Check the client ID, client secret, email and password.'));
        }

        $seconds = max(300, min((int) ($body['expires_in'] ?? 3600), 86400 * 3) - 300);
        Cache::put($name, Crypt::encryptString($token), $seconds);

        return $token;
    }

    /** One request with the token; a rejected token is replaced once and the request repeated. */
    private function call(array $creds, string $method, string $path, array $data = [])
    {
        $send = fn (string $token) => Http::withToken($token)->acceptJson()->asJson()->timeout(25)
            ->{$method}($this->base($creds) . $path, $data);

        $response = $send($this->token($creds));

        if ($response->status() === 401) {
            $response = $send($this->token($creds, fresh: true));
        }

        return $response;
    }

    /** @return array<int, array{id:int, name:string}> */
    private function stores(array $creds): array
    {
        $response = $this->call($creds, 'get', '/aladdin/api/v1/stores');
        $body     = $response->json();

        if (! $response->successful() || ! is_array($body)) {
            throw new RuntimeException($this->why($body, 'Pathao did not return the store list.'));
        }

        $rows = $body['data']['data'] ?? [];

        return collect(is_array($rows) ? $rows : [])
            ->filter(fn ($row) => is_array($row) && ! empty($row['store_id']))
            ->map(fn ($row) => ['id' => (int) $row['store_id'], 'name' => (string) ($row['store_name'] ?? 'Store')])
            ->values()->all();
    }

    private function storeId(array $creds): int
    {
        $chosen = (int) ($creds['store'] ?? 0);

        if ($chosen > 0) {
            return $chosen;
        }

        $stores = $this->stores($creds);

        if (! $stores) {
            throw new RuntimeException('This Pathao account has no store (pickup point) yet. Create one in the Pathao merchant panel first.');
        }

        return $stores[0]['id'];
    }

    public function test(array $creds): string
    {
        $this->token($creds, fresh: true);
        $stores = $this->stores($creds);

        if (! $stores) {
            throw new RuntimeException('The login works, but this Pathao account has no store (pickup point) yet. Create one in the Pathao merchant panel.');
        }

        $list = collect($stores)->take(5)->map(fn ($s) => $s['name'] . ' (ID ' . $s['id'] . ')')->implode(', ');

        return 'Connected' . ($creds['sandbox'] ? ' (test mode)' : '') . '. Pathao stores: ' . $list . '.'
            . ((int) ($creds['store'] ?? 0) > 0 ? '' : ' Parcels will be picked up from the first one unless you enter a Store ID.');
    }

    public function book(array $creds, Order $order, array $input): array
    {
        $order->loadMissing('items');

        $address = trim($order->shipping_address . ($order->shipping_area ? ', ' . $order->shipping_area : '')
            . ($order->shipping_zone_name ? ', ' . $order->shipping_zone_name : ''));

        if (mb_strlen($address) < 10) {
            throw new RuntimeException('Pathao needs a fuller address (at least 10 characters) to find the delivery area.');
        }

        // Pathao takes the phone as 11 digits: 01XXXXXXXXX.
        $phone = preg_replace('/\D+/', '', (string) $order->customer_phone);
        $phone = preg_replace('/^(?:88)?(01\d{9})$/', '$1', $phone);

        if (! preg_match('/^01\d{9}$/', $phone)) {
            throw new RuntimeException('Pathao needs an 11-digit Bangladeshi mobile number. Fix the customer\'s phone number on the order first.');
        }

        $items = $order->items;
        $kg    = min(10, max(0.5, round(((int) ($input['weight'] ?? 500)) / 1000, 1)));

        $response = $this->call($creds, 'post', '/aladdin/api/v1/orders', [
            'store_id'            => $this->storeId($creds),
            'merchant_order_id'   => $order->order_number,
            'recipient_name'      => Str::limit((string) $order->customer_name, 100, ''),
            'recipient_phone'     => $phone,
            'recipient_address'   => Str::limit($address, 220, ''),
            'delivery_type'       => 48,   // normal delivery
            'item_type'           => 2,    // parcel
            'special_instruction' => Str::limit((string) ($input['note'] ?? ''), 200, ''),
            'item_quantity'       => max(1, (int) $items->sum('quantity')),
            'item_weight'         => $kg,
            'amount_to_collect'   => (int) round((float) $input['cod']),
            'item_description'    => Str::limit($items->pluck('name')->implode(', '), 200, ''),
        ]);

        $body = $response->json();
        $data = is_array($body) ? ($body['data'] ?? null) : null;

        if (! $response->successful() || ! is_array($data) || empty($data['consignment_id'])) {
            throw new RuntimeException($this->why($body, 'Pathao did not create the parcel.'));
        }

        return [
            'ref'      => (string) $data['consignment_id'],
            'tracking' => (string) $data['consignment_id'],
            'status'   => (string) ($data['order_status'] ?? 'Pending'),
        ];
    }

    public function status(array $creds, Order $order): array
    {
        $response = $this->call($creds, 'get', '/aladdin/api/v1/orders/' . rawurlencode((string) $order->courier_ref) . '/info');
        $body     = $response->json();
        $status   = is_array($body) ? (string) ($body['data']['order_status'] ?? '') : '';

        if (! $response->successful() || $status === '') {
            throw new RuntimeException($this->why($body, 'Pathao did not return a status.'));
        }

        $plain = strtolower(str_replace(['_', '-'], ' ', $status));

        // Only a clean "delivered" counts. Anything partial, returned,
        // cancelled or on hold needs a person to look.
        $state = match (true) {
            (bool) preg_match('/partial|return|cancel|hold|fail|lost|damage/', $plain) => 'problem',
            str_contains($plain, 'delivered')                                           => 'delivered',
            default                                                                     => 'moving',
        };

        return ['status' => $status, 'state' => $state];
    }

    public function trackUrl(string $tracking): string
    {
        return 'https://merchant.pathao.com/tracking?consignment_id=' . rawurlencode($tracking);
    }

    public function areas(array $creds, string $search): array
    {
        return [];
    }

    private function why($body, string $fallback): string
    {
        if (is_array($body)) {
            if (! empty($body['errors']) && is_array($body['errors'])) {
                return 'Pathao: ' . Str::limit(implode(' ', array_map(fn ($e) => is_array($e) ? implode(' ', $e) : (string) $e, $body['errors'])), 300);
            }
            if (! empty($body['message']) && is_string($body['message'])) {
                return 'Pathao: ' . Str::limit($body['message'], 300);
            }
        }

        return $fallback;
    }
}
