<?php

namespace App\Services\Gateways;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * aamarPay, JSON API.
 *
 * The store ID and signature key come from the aamarPay merchant panel.
 */
class AamarPayGateway implements Gateway
{
    public function label(): string
    {
        return 'aamarPay';
    }

    public function secretLabel(): string
    {
        return 'Signature key';
    }

    private function host(array $creds): string
    {
        return $creds['sandbox']
            ? 'https://sandbox.aamarpay.com'
            : 'https://secure.aamarpay.com';
    }

    public function start(array $creds, Order $order, Payment $payment, array $urls): string
    {
        $response = Http::asJson()->acceptJson()->timeout(20)->post($this->host($creds) . '/jsonpost.php', [
            'store_id'      => $creds['store_id'],
            'signature_key' => $creds['secret'],
            'tran_id'       => $payment->transaction_id,
            'amount'        => number_format((float) $payment->amount, 2, '.', ''),
            'currency'      => 'BDT',
            'desc'          => 'Order ' . $order->order_number,
            'cus_name'      => $order->customer_name,
            'cus_email'     => $order->customer_email ?: (config('mail.from.address') ?: 'customer@example.com'),
            'cus_phone'     => $order->customer_phone,
            'cus_add1'      => Str::limit((string) $order->shipping_address, 100, ''),
            'cus_city'      => $order->shipping_zone_name ?: 'Dhaka',
            'cus_country'   => 'Bangladesh',
            'success_url'   => $urls['success'],
            'fail_url'      => $urls['fail'],
            'cancel_url'    => $urls['cancel'],
            'opt_a'         => (string) $payment->id,
            'type'          => 'json',
        ]);

        $body = $response->json();

        if (! $response->successful() || ! is_array($body) || empty($body['payment_url'])) {
            $why = is_array($body) ? json_encode($body) : Str::limit((string) $response->body(), 200);

            throw new RuntimeException('aamarPay: ' . ($why ?: 'no payment page returned'));
        }

        return $body['payment_url'];
    }

    public function confirm(array $creds, Payment $payment, array $input): array
    {
        // What the browser brought back is ignored. The transaction is looked
        // up on aamarPay's server by our own ID.
        $response = Http::acceptJson()->timeout(20)->get($this->host($creds) . '/api/v1/trxcheck/request.php', [
            'request_id'    => $payment->transaction_id,
            'store_id'      => $creds['store_id'],
            'signature_key' => $creds['secret'],
            'type'          => 'json',
        ]);

        $body = $response->json();

        if (! $response->successful() || ! is_array($body)) {
            throw new RuntimeException('aamarPay transaction check did not answer.');
        }

        $status   = (string) ($body['pay_status'] ?? '');
        $amount   = (float) ($body['amount'] ?? 0);
        $currency = (string) ($body['currency'] ?? 'BDT');

        $problems = [];

        if ($status !== 'Successful') {
            $problems[] = 'status ' . ($status ?: 'missing');
        }
        if (($body['mer_txnid'] ?? null) !== $payment->transaction_id) {
            $problems[] = 'transaction ID does not match';
        }
        if ($currency !== 'BDT') {
            $problems[] = 'currency ' . $currency;
        }
        if (abs($amount - (float) $payment->amount) > 0.01) {
            $problems[] = 'amount ' . $amount . ' instead of ' . $payment->amount;
        }

        return [
            'paid'      => $problems === [],
            'reference' => $body['pg_txnid'] ?? null,
            'note'      => $problems === []
                ? 'Confirmed by aamarPay (' . ($body['payment_type'] ?? 'online') . ').'
                : 'aamarPay: ' . implode('; ', $problems) . '.',
            'raw'       => array_intersect_key($body, array_flip([
                'pay_status', 'status_code', 'status_title', 'mer_txnid', 'pg_txnid', 'amount',
                'amount_bdt', 'currency', 'payment_type', 'bank_trxid', 'date_processed',
                'error_code', 'error_title',
            ])),
        ];
    }
}
