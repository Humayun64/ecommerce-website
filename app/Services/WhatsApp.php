<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Two separate things, both set in Admin > WhatsApp:
 *
 *  1. The chat button on the storefront. It is only a link to wa.me, so it
 *     needs no account and no key.
 *  2. A WhatsApp message to the owner when an order comes in. WhatsApp only
 *     lets a website send through an API, so this needs a provider key.
 *
 * Keys are stored encrypted. A provider that is slow or down must never get
 * in the way of an order, so every send is guarded and short.
 */
class WhatsApp
{
    public const PROVIDERS = [
        'callmebot' => 'CallMeBot (free)',
        'meta'      => 'WhatsApp Cloud API (official)',
    ];

    public function all(): array
    {
        return [
            'chat_on'       => Setting::get('wa_chat_on', '0') === '1',
            'chat_number'   => $this->digits((string) Setting::get('wa_chat_number', '')),
            'chat_text'     => (string) Setting::get('wa_chat_text', ''),

            'alert_on'      => Setting::get('wa_alert_on', '0') === '1',
            'provider'      => array_key_exists(Setting::get('wa_alert_provider'), self::PROVIDERS) ? Setting::get('wa_alert_provider') : 'callmebot',
            'alert_number'  => $this->digits((string) Setting::get('wa_alert_number', '')),
            'key'           => $this->secret('wa_alert_key'),
            'meta_phone_id' => preg_replace('/\D+/', '', (string) Setting::get('wa_meta_phone_id', '')),
            'meta_template' => (string) Setting::get('wa_meta_template', ''),
            'meta_lang'     => (string) Setting::get('wa_meta_lang', 'en'),
        ];
    }

    public function save(array $input): void
    {
        $pairs = [
            'wa_chat_on'        => ! empty($input['chat_on']) ? '1' : '0',
            'wa_chat_number'    => $this->digits((string) ($input['chat_number'] ?? '')),
            'wa_chat_text'      => trim((string) ($input['chat_text'] ?? '')),
            'wa_alert_on'       => ! empty($input['alert_on']) ? '1' : '0',
            'wa_alert_provider' => array_key_exists($input['provider'] ?? '', self::PROVIDERS) ? $input['provider'] : 'callmebot',
            'wa_alert_number'   => $this->digits((string) ($input['alert_number'] ?? '')),
            'wa_meta_phone_id'  => preg_replace('/\D+/', '', (string) ($input['meta_phone_id'] ?? '')),
            'wa_meta_template'  => trim((string) ($input['meta_template'] ?? '')),
            'wa_meta_lang'      => trim((string) ($input['meta_lang'] ?? 'en')) ?: 'en',
        ];

        // An empty box means "keep the key already saved".
        if ((string) ($input['key'] ?? '') !== '') {
            $pairs['wa_alert_key'] = Crypt::encryptString(trim((string) $input['key']));
        }

        Setting::put($pairs);
    }

    /* ---------- storefront chat button ---------- */

    public function chatReady(): bool
    {
        $s = $this->all();

        return $s['chat_on'] && strlen($s['chat_number']) >= 10;
    }

    /** The wa.me link, with an opening line already typed for the customer. */
    public function chatUrl(?string $text = null): ?string
    {
        if (! $this->chatReady()) {
            return null;
        }

        $s    = $this->all();
        $text = trim((string) ($text ?? $s['chat_text']));

        return 'https://wa.me/' . $s['chat_number'] . ($text !== '' ? '?text=' . rawurlencode(Str::limit($text, 500, '')) : '');
    }

    /* ---------- order alerts to the owner ---------- */

    public function alertReady(): bool
    {
        $s = $this->all();

        if (! $s['alert_on'] || strlen($s['alert_number']) < 10 || $s['key'] === '') {
            return false;
        }

        return $s['provider'] !== 'meta' || ($s['meta_phone_id'] !== '' && $s['meta_template'] !== '');
    }

    /** Called after an order is saved. Never throws. */
    public function orderPlaced(Order $order): void
    {
        try {
            if (! $this->alertReady()) {
                return;
            }

            $where = trim(implode(', ', array_filter([$order->shipping_area, $order->shipping_zone_name])));
            $total = 'Tk ' . number_format((float) $order->total);

            $text = "New order {$order->order_number}\n"
                . "{$order->customer_name} - {$order->customer_phone}\n"
                . "Total: {$total}" . ($order->payment_method ? " ({$order->payment_method})" : '') . "\n"
                . ($where !== '' ? "Area: {$where}\n" : '')
                . route('admin.orders.show', $order);

            [$ok, $why] = $this->send($text, [$order->order_number, $order->customer_name . ' ' . $order->customer_phone, $total]);

            if (! $ok) {
                Log::warning('WhatsApp order alert failed', ['order' => $order->order_number, 'message' => $why]);
            }
        } catch (\Throwable $e) {
            Log::warning('WhatsApp order alert failed', ['order' => $order->order_number, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Send one message to the owner's number.
     *
     * @param  array<int,string>  $vars  The three blanks of the Meta template: order, customer, total.
     * @return array{0:bool,1:string}    Whether it went, and the provider's own words if not.
     */
    public function send(string $text, array $vars = []): array
    {
        $s = $this->all();

        try {
            if ($s['provider'] === 'meta') {
                $response = Http::withToken($s['key'])->timeout(8)->connectTimeout(4)->acceptJson()
                    ->post('https://graph.facebook.com/v21.0/' . $s['meta_phone_id'] . '/messages', [
                        'messaging_product' => 'whatsapp',
                        'to'                => $s['alert_number'],
                        'type'              => 'template',
                        'template'          => [
                            'name'       => $s['meta_template'],
                            'language'   => ['code' => $s['meta_lang']],
                            'components' => [[
                                'type'       => 'body',
                                'parameters' => array_map(
                                    fn ($value) => ['type' => 'text', 'text' => Str::limit((string) $value, 120, '')],
                                    array_values(array_pad(array_slice($vars, 0, 3), 3, '-'))
                                ),
                            ]],
                        ],
                    ]);

                return $response->successful()
                    ? [true, '']
                    : [false, Str::limit((string) ($response->json('error.message') ?? $response->body()), 300)];
            }

            $response = Http::timeout(8)->connectTimeout(4)->get('https://api.callmebot.com/whatsapp.php', [
                'phone'  => '+' . $s['alert_number'],
                'text'   => $text,
                'apikey' => $s['key'],
            ]);

            $body = trim(strip_tags($response->body()));

            // CallMeBot answers 200 even for a wrong key, with the reason in the page.
            $bad = ! $response->successful() || preg_match('/invalid|not valid|error|not activated|not found/i', $body);

            return $bad ? [false, Str::limit($body !== '' ? $body : 'No answer from CallMeBot.', 300)] : [true, ''];
        } catch (\Throwable $e) {
            return [false, Str::limit($e->getMessage(), 300)];
        }
    }

    /** Country code and number, digits only: 8801XXXXXXXXX. A local 01… number gets Bangladesh's 88. */
    public function digits(string $number): string
    {
        $digits = preg_replace('/\D+/', '', $number);

        if (preg_match('/^01\d{9}$/', $digits)) {
            $digits = '88' . $digits;
        }

        return substr($digits, 0, 15);
    }

    private function secret(string $key): string
    {
        $value = (string) Setting::get($key, '');

        if ($value === '') {
            return '';
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Throwable $e) {
            return '';
        }
    }
}
