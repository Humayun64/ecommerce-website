<?php

namespace App\Services\Gateways;

use App\Models\Order;
use App\Models\Payment;

/**
 * One online payment provider.
 *
 * Every provider does the same two jobs: open a payment page for an order,
 * and — when the customer comes back — say whether the money really arrived.
 * The second job never trusts what the browser brought back; it always asks
 * the provider's own server.
 */
interface Gateway
{
    /** The name shown in the admin panel. */
    public function label(): string;

    /** What the provider calls its secret, e.g. "Store password". */
    public function secretLabel(): string;

    /**
     * Open a payment session and return the address to send the customer to.
     *
     * @param  array{store_id:string, secret:string, sandbox:bool}  $creds
     * @param  array{success:string, fail:string, cancel:string, ipn:?string}  $urls
     *
     * @throws \RuntimeException when the provider refuses or cannot be reached
     */
    public function start(array $creds, Order $order, Payment $payment, array $urls): string;

    /**
     * Ask the provider whether this payment was really made.
     *
     * @param  array{store_id:string, secret:string, sandbox:bool}  $creds
     * @param  array  $input  whatever the provider posted back
     * @return array{paid:bool, reference:?string, note:string, raw:array}
     *
     * @throws \RuntimeException when the provider cannot be reached
     */
    public function confirm(array $creds, Payment $payment, array $input): array;
}
