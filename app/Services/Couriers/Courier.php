<?php

namespace App\Services\Couriers;

use App\Models\Order;

/**
 * One courier company with an API.
 *
 * $creds is always ['key' => string, 'secret' => string, 'sandbox' => bool];
 * a courier uses whichever of those it needs.
 */
interface Courier
{
    public function label(): string;

    /** Which logins the admin form asks for: ['key' => 'API key', 'secret' => 'Secret key']. */
    public function fields(): array;

    /** Does booking need a delivery area picked from the courier's own list? */
    public function needsArea(): bool;

    /** Does this courier have a separate test server? */
    public function hasSandbox(): bool;

    /**
     * Prove the login works. Returns a short sentence for the admin.
     *
     * @throws \RuntimeException
     */
    public function test(array $creds): string;

    /**
     * Book a parcel for this order.
     *
     * @param  array{cod:float, note:?string, weight:int, area_id:?int, area_name:?string}  $input
     * @return array{ref:string, tracking:string, status:string}
     *
     * @throws \RuntimeException
     */
    public function book(array $creds, Order $order, array $input): array;

    /**
     * Where the parcel is now.
     *
     * state is one of: moving, delivered, problem.
     *
     * @return array{status:string, state:string}
     *
     * @throws \RuntimeException
     */
    public function status(array $creds, Order $order): array;

    /** The courier's public tracking page for a parcel. */
    public function trackUrl(string $tracking): string;

    /** Delivery areas matching a search, as [['id' => 1, 'name' => 'Uttara']]. Empty if not used. */
    public function areas(array $creds, string $search): array;
}
