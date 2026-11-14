<?php

namespace App\Services\Gateways;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * SSLCommerz, API v4.
 *
 * Sandbox and live are the same API on two hosts. The store ID and store
 * password come from the SSLCommerz merchant panel.
 */
class SslCommerzGateway implements Gateway
{
    public function label(): string
    {
        return 'SSLCommerz';
    }

    public function secretLabel(): string
    {
        return 'Store password';
    }

    private function host(array $creds): string
    {
        return $creds['sandbox']
            ? 'https://sandbox.sslcommerz.com'
            : 'https://securepay.sslcommerz.com';
    }

    public function start(array $creds, Order $order, Payment $payment, array $urls): string
    {
        $order->loadMissing('items');

        $response = Http::asForm()->timeout(20)->post($this->host($creds) . '/gwprocess/v4/api.php', [
            'store_id'         => $creds['store_id'],
            'store_passwd'     => $creds['secret'],
            'total_amount'     => number_format((float) $payment->amount, 2, '.', ''),
            'currency'         => 'BDT',
            'tran_id'          => $payment->transaction_id,
            'success_url'      => $urls['success'],
            'fail_url'         => $urls['fail'],
            'cancel_url'       => $urls['cancel'],
            'ipn_url'          => $urls['ipn'],
            'emi_option'       => 0,

            'cus_name'         => $order->customer_name,
            'cus_email'        => $order->customer_email ?: (config('mail.from.address') ?: 'customer@example.com'),
            'cus_phone'        => $order->customer_phone,
            'cus_add1'         => Str::limit((string) $order->shipping_address, 50, ''),
            'cus_city'         => $order->shipping_zone_name ?: 'Dhaka',
            'cus_postcode'     => '1000',
            'cus_country'      => 'Bangladesh',

            // We deliver ourselves, so SSLCommerz is told there is no
            // shipment for it to arrange.
            'shipping_method'  => 'NO',
            'num_of_item'      => max(1, (int) $order->items->sum('quantity')),
            'product_name'     => Str::limit($order->items->pluck('name')->implode(', ') ?: 'Order ' . $order->order_number, 250, ''),
            'product_category' => 'general',
            'product_profile'  => 'general',

            'value_a'          => (string) $payment->id,
            'value_b'          => $order->order_number,
        ]);

        $body = $response->json();

        if (! $response->successful() || ! is_array($body)
            || ($body['status'] ?? '') !== 'SUCCESS' || empty($body['GatewayPageURL'])) {
            throw new RuntimeException('SSLCommerz: ' . (is_array($body) ? ($body['failedreason'] ?? 'no payment page returned') : 'no answer'));
        }

        return $body['GatewayPageURL'];
    }

    public function confirm(array $creds, Payment $payment, array $input): array
    {
        $valId = trim((string) ($input['val_id'] ?? ''));

        if ($valId === '') {
            return [
                'paid'      => false,
                'reference' => null,
                'note'      => 'SSLCommerz sent no validation ID (' . ($input['status'] ?? 'no status') . ').',
                'raw'       => $this->keep($input),
            ];
        }

        $response = Http::timeout(20)->get($this->host($creds) . '/validator/api/validationserverAPI.php', [
            'val_id'       => $valId,
            'store_id'     => $creds['store_id'],
            'store_passwd' => $creds['secret'],
            'format'       => 'json',
        ]);

        $body = $response->json();

        if (! $response->successful() || ! is_array($body)) {
            throw new RuntimeException('SSLCommerz validation did not answer.');
        }

        $status   = (string) ($body['status'] ?? '');
        $amount   = (float) ($body['currency_amount'] ?? $body['amount'] ?? 0);
        $currency = (string) ($body['currency_type'] ?? $body['currency'] ?? '');

        $problems = [];

        if (! in_array($status, ['VALID', 'VALIDATED'], true)) {
            $problems[] = 'status ' . ($status ?: 'missing');
        }
        if (($body['tran_id'] ?? null) !== $payment->transaction_id) {
            $problems[] = 'transaction ID does not match';
        }
        if ($currency !== 'BDT') {
            $problems[] = 'currency ' . ($currency ?: 'missing');
        }
        if (abs($amount - (float) $payment->amount) > 0.01) {
            $problems[] = 'amount ' . $amount . ' instead of ' . $payment->amount;
        }
        // SSLCommerz marks a payment it thinks is risky. Those are held for
        // a person to look at instead of being marked paid automatically.
        if ((string) ($body['risk_level'] ?? '0') === '1') {
            $problems[] = 'flagged as risky by SSLCommerz — check in the merchant panel';
        }

        return [
            'paid'      => $problems === [],
            'reference' => $body['bank_tran_id'] ?? $valId,
            'note'      => $problems === []
                ? 'Confirmed by SSLCommerz (' . ($body['card_type'] ?? 'online') . ').'
                : 'SSLCommerz: ' . implode('; ', $problems) . '.',
            'raw'       => $this->keep($body),
        ];
    }

    /** Only what is useful later — never the store password or full card data. */
    private function keep(array $data): array
    {
        return array_intersect_key($data, array_flip([
            'status', 'tran_id', 'val_id', 'amount', 'store_amount', 'currency', 'currency_type',
            'currency_amount', 'bank_tran_id', 'card_type', 'card_issuer', 'card_brand',
            'tran_date', 'risk_level', 'risk_title', 'error',
        ]));
    }
}
