<?php

namespace App\Services;

use App\Models\PaymentMethod;
use App\Services\Gateways\AamarPayGateway;
use App\Services\Gateways\Gateway;
use App\Services\Gateways\SslCommerzGateway;

/**
 * The providers that really have code behind them.
 *
 * A provider appears in the admin panel only if it is listed here, so a
 * method can never be offered at checkout without something to take the money.
 */
class GatewayManager
{
    private const DRIVERS = [
        'sslcommerz' => SslCommerzGateway::class,
        'aamarpay'   => AamarPayGateway::class,
    ];

    public function has(?string $gateway): bool
    {
        return $gateway !== null && isset(self::DRIVERS[$gateway]);
    }

    public function for(PaymentMethod $method): ?Gateway
    {
        return $this->has($method->gateway) ? app(self::DRIVERS[$method->gateway]) : null;
    }

    /** @return array<string, array{label:string, secret:string}> */
    public function all(): array
    {
        $out = [];

        foreach (self::DRIVERS as $key => $class) {
            $driver    = app($class);
            $out[$key] = ['label' => $driver->label(), 'secret' => $driver->secretLabel()];
        }

        return $out;
    }
}
