<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\SmsLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Sends one SMS through whichever gateway is configured in admin.
 *
 * Every SMS provider in Bangladesh takes the same shape of request — a URL,
 * an API key, a number and the text — so rather than writing a driver per
 * provider this builds the request from a URL and a few field names you set
 * in admin. Changing provider then means changing settings, not code.
 */
class SmsGateway
{
    /**
     * Starting points for the providers people here actually use. These only
     * fill the form in; nothing is locked to them.
     */
    public const PRESETS = [
        'bulksmsbd' => [
            'label'   => 'BulkSMSBD',
            'url'     => 'http://bulksmsbd.net/api/smsapi',
            'method'  => 'GET',
            'fields'  => ['api_key' => 'api_key', 'to' => 'number', 'text' => 'message', 'from' => 'senderid'],
        ],
        'alpha' => [
            'label'   => 'Alpha SMS',
            'url'     => 'https://api.sms.net.bd/sendsms',
            'method'  => 'POST',
            'fields'  => ['api_key' => 'api_key', 'to' => 'to', 'text' => 'msg', 'from' => 'sender_id'],
        ],
        'mimsms' => [
            'label'   => 'MIM SMS',
            'url'     => 'https://api.mimsms.com/api/SmsSending/SMS',
            'method'  => 'POST',
            'fields'  => ['api_key' => 'ApiKey', 'to' => 'MobileNumber', 'text' => 'Message', 'from' => 'SenderName'],
        ],
        'custom' => [
            'label'   => 'Something else',
            'url'     => '',
            'method'  => 'GET',
            'fields'  => ['api_key' => 'api_key', 'to' => 'to', 'text' => 'message', 'from' => 'sender'],
        ],
    ];

    public function isConfigured(): bool
    {
        return $this->enabled()
            && filter_var($this->url(), FILTER_VALIDATE_URL) !== false
            && trim((string) Setting::get('sms_api_key', '')) !== '';
    }

    public function enabled(): bool
    {
        return (string) Setting::get('sms_enabled', '0') === '1';
    }

    public function url(): string
    {
        return trim((string) Setting::get('sms_url', ''));
    }

    /**
     * Bangladesh numbers, the way gateways expect them.
     *
     * Customers type 01711…, +8801711…, 8801711… and 01711-220044. All four
     * are the same number and all four have to come out as 8801711220044,
     * or the gateway quietly drops the message.
     */
    public function normalise(?string $phone): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $phone);

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '880')) {
            // already in international form
        } elseif (str_starts_with($digits, '0')) {
            $digits = '880' . substr($digits, 1);
        } elseif (strlen($digits) === 10 && str_starts_with($digits, '1')) {
            $digits = '880' . $digits;
        }

        // 880 + 10 digits. Anything else is not a mobile number here.
        return preg_match('/^8801[3-9]\d{8}$/', $digits) ? $digits : null;
    }

    /**
     * Send, and record what happened either way.
     *
     * A gateway being down must never take a checkout down with it, so every
     * failure is caught and written to the log instead of thrown.
     */
    public function send(string $phone, string $message, ?string $event = null, ?int $orderId = null): SmsLog
    {
        $to = $this->normalise($phone);

        $log = new SmsLog([
            'order_id' => $orderId,
            'phone'    => $to ?: $phone,
            'event'    => $event,
            'message'  => $message,
            'parts'    => SmsLog::partsFor($message),
            'status'   => 'queued',
        ]);

        if (! $to) {
            $log->status   = 'failed';
            $log->response = 'Not a Bangladeshi mobile number.';
            $log->save();

            return $log;
        }

        if (! $this->isConfigured()) {
            $log->status   = 'failed';
            $log->response = 'SMS is switched off, or the gateway is not set up.';
            $log->save();

            return $log;
        }

        $log->save();

        try {
            $response = $this->fire($to, $message);

            $log->status   = $response['ok'] ? 'sent' : 'failed';
            $log->response = mb_substr((string) $response['body'], 0, 2000);
            $log->sent_at  = $response['ok'] ? now() : null;
        } catch (\Throwable $e) {
            $log->status   = 'failed';
            $log->response = mb_substr($e->getMessage(), 0, 2000);

            Log::warning('SMS send failed', ['to' => $to, 'error' => $e->getMessage()]);
        }

        $log->save();

        return $log;
    }

    private function fire(string $to, string $message): array
    {
        $fields = $this->fields();

        $payload = [
            $fields['api_key'] => Setting::get('sms_api_key', ''),
            $fields['to']      => $to,
            $fields['text']    => $message,
        ];

        $sender = trim((string) Setting::get('sms_sender', ''));

        if ($sender !== '' && ! empty($fields['from'])) {
            $payload[$fields['from']] = $sender;
        }

        // Anything else the provider wants, as key=value lines in admin.
        foreach ($this->extra() as $key => $value) {
            $payload[$key] = $value;
        }

        // Short timeout on purpose: a slow gateway must not hold up an order.
        $request = Http::timeout(8)->connectTimeout(5);

        $response = strtoupper((string) Setting::get('sms_method', 'GET')) === 'POST'
            ? $request->asForm()->post($this->url(), $payload)
            : $request->get($this->url(), $payload);

        $body = (string) $response->body();

        return [
            'ok'   => $response->successful() && ! $this->looksLikeFailure($body),
            'body' => $body,
        ];
    }

    /**
     * Most of these gateways answer HTTP 200 whatever happens and put the
     * real result in the body, so a 200 on its own proves nothing.
     */
    private function looksLikeFailure(string $body): bool
    {
        $lower = strtolower($body);

        foreach (['error', 'invalid', 'fail', 'insufficient', 'unauthorized', 'not found'] as $word) {
            if (str_contains($lower, $word)) {
                return true;
            }
        }

        return false;
    }

    public function fields(): array
    {
        $saved = json_decode((string) Setting::get('sms_fields', ''), true);

        if (is_array($saved) && isset($saved['api_key'], $saved['to'], $saved['text'])) {
            return $saved + ['from' => ''];
        }

        return self::PRESETS['custom']['fields'];
    }

    /** Extra parameters, one `key=value` per line. */
    public function extra(): array
    {
        $raw = (string) Setting::get('sms_extra', '');
        $out = [];

        foreach (preg_split('/\r\n|\r|\n/', $raw) as $line) {
            $line = trim($line);

            if ($line === '' || ! str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);

            if ($key !== '') {
                $out[$key] = trim($value);
            }
        }

        return $out;
    }
}
