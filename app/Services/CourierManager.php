<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Setting;
use App\Services\Couriers\Courier;
use App\Services\Couriers\RedxCourier;
use App\Services\Couriers\SteadfastCourier;
use Illuminate\Support\Facades\Crypt;
use RuntimeException;

/**
 * The couriers that have code behind them, and their saved logins.
 *
 * Logins live in the settings table; the secret half is encrypted and is
 * only decrypted here, at the moment a request is made.
 */
class CourierManager
{
    private const DRIVERS = [
        'steadfast' => SteadfastCourier::class,
        'redx'      => RedxCourier::class,
    ];

    public function __construct(private OrderStatus $statuses)
    {
    }

    public function has(?string $code): bool
    {
        return $code !== null && isset(self::DRIVERS[$code]);
    }

    public function driver(string $code): Courier
    {
        if (! $this->has($code)) {
            throw new RuntimeException('Unknown courier.');
        }

        return app(self::DRIVERS[$code]);
    }

    /** @return array<string, Courier> */
    public function all(): array
    {
        return array_map(fn ($class) => app($class), self::DRIVERS);
    }

    /** @return array{key:string, secret:string, sandbox:bool, on:bool} */
    public function creds(string $code): array
    {
        $secret = (string) Setting::get("courier_{$code}_secret", '');

        if ($secret !== '') {
            try {
                $secret = Crypt::decryptString($secret);
            } catch (\Throwable $e) {
                $secret = '';
            }
        }

        return [
            'key'     => trim((string) Setting::get("courier_{$code}_key", '')),
            'secret'  => $secret,
            'sandbox' => Setting::get("courier_{$code}_sandbox", '0') === '1',
            'on'      => Setting::get("courier_{$code}_on", '0') === '1',
        ];
    }

    /** Every login this courier asks for has been filled in. */
    public function connected(string $code): bool
    {
        $creds = $this->creds($code);

        foreach (array_keys($this->driver($code)->fields()) as $field) {
            if ($creds[$field] === '') {
                return false;
            }
        }

        return true;
    }

    /** Couriers an order can be sent with right now: [code => label]. */
    public function available(): array
    {
        $out = [];

        foreach ($this->all() as $code => $driver) {
            if ($this->creds($code)['on'] && $this->connected($code)) {
                $out[$code] = $driver->label();
            }
        }

        return $out;
    }

    public function save(string $code, array $input): void
    {
        $driver = $this->driver($code);
        $pairs  = [
            "courier_{$code}_on"      => ! empty($input['on']) ? '1' : '0',
            "courier_{$code}_sandbox" => $driver->hasSandbox() && ! empty($input['sandbox']) ? '1' : '0',
        ];

        if (isset($driver->fields()['key'])) {
            $pairs["courier_{$code}_key"] = trim((string) ($input['key'] ?? ''));
        }

        // An empty box means "keep the secret already saved".
        if (trim((string) ($input['secret'] ?? '')) !== '') {
            $pairs["courier_{$code}_secret"] = Crypt::encryptString(trim($input['secret']));
        }

        Setting::put($pairs);
    }

    /** Book the parcel and write what came back onto the order. */
    public function book(Order $order, string $code, array $input): void
    {
        if ($order->courier_code) {
            throw new RuntimeException('This order has already been sent to a courier.');
        }

        if (in_array($order->status, ['cancelled', 'returned'], true)) {
            throw new RuntimeException('A cancelled or returned order cannot be sent to a courier.');
        }

        if (! isset($this->available()[$code])) {
            throw new RuntimeException('That courier is not switched on.');
        }

        $driver = $this->driver($code);
        $result = $driver->book($this->creds($code), $order, $input);

        $order->update([
            'courier_code'      => $code,
            'courier_ref'       => $result['ref'],
            'courier_status'    => $result['status'],
            'courier_synced_at' => now(),
            'courier'           => $driver->label(),
            'tracking_number'   => $result['tracking'],
        ]);
    }

    /**
     * Ask the courier where the parcel is. A parcel the courier reports as
     * delivered marks the order delivered; anything that looks like a
     * problem is only recorded, for a person to decide.
     *
     * @return array{status:string, state:string, changed:bool}
     */
    public function sync(Order $order): array
    {
        if (! $order->courier_code || ! $order->courier_ref || ! $this->has($order->courier_code)) {
            throw new RuntimeException('This order was not sent through a connected courier.');
        }

        $answer = $this->driver($order->courier_code)->status($this->creds($order->courier_code), $order);

        $order->update([
            'courier_status'    => $answer['status'],
            'courier_synced_at' => now(),
        ]);

        $changed = false;

        if ($answer['state'] === 'delivered'
            && in_array($order->status, ['pending', 'confirmed', 'processing', 'shipped'], true)) {
            $this->statuses->change($order, 'delivered');
            $changed = true;
        }

        return $answer + ['changed' => $changed];
    }

    public function trackUrl(Order $order): ?string
    {
        return $order->courier_code && $order->tracking_number && $this->has($order->courier_code)
            ? $this->driver($order->courier_code)->trackUrl($order->tracking_number)
            : null;
    }
}
