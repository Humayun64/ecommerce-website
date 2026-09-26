<?php

namespace App\Services;

use App\Models\Setting;

/**
 * Everything the contact page shows, read from settings.
 *
 * The phone, email and address are the same `store_*` settings the header and
 * footer already use — there is no second copy to keep in step, so changing
 * the shop phone changes it everywhere at once.
 */
class ContactPage
{
    public function heading(): string
    {
        return Setting::get('contact_heading') ?: __('Talk to us');
    }

    public function intro(): string
    {
        return (string) (Setting::get('contact_intro')
            ?: __('A message here reaches the same people who pack the parcels. We answer every one.'));
    }

    public function phone(): ?string
    {
        return Setting::get('store_phone') ?: null;
    }

    public function email(): ?string
    {
        return Setting::get('store_email') ?: null;
    }

    public function whatsapp(): ?string
    {
        $number = Setting::get('store_whatsapp') ?: Setting::get('store_phone');

        if (! $number) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $number);

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '0')) {
            $digits = '88' . $digits;
        }

        return 'https://wa.me/' . $digits;
    }

    public function address(): ?string
    {
        return Setting::get('store_address') ?: null;
    }

    public function replyTime(): string
    {
        return Setting::get('contact_reply_time') ?: __('We usually reply the same day.');
    }

    /** One line per day, as typed. Blank lines are dropped. */
    public function hours(): array
    {
        $raw = (string) Setting::get('contact_hours', '');

        return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $raw))));
    }

    /** The subject dropdown. Falls back to something sensible for a shop. */
    public function topics(): array
    {
        $raw = trim((string) Setting::get('contact_topics', ''));

        if ($raw === '') {
            return [
                __('Question about an order'),
                __('Is this product genuine?'),
                __('Delivery or payment'),
                __('Return or refund'),
                __('Wholesale or reselling'),
                __('Something else'),
            ];
        }

        return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $raw))));
    }

    public function formEnabled(): bool
    {
        return (string) Setting::get('contact_form_enabled', '1') === '1';
    }

    /**
     * The map, only if it is really a map.
     *
     * This value ends up as an iframe `src` on a public page, so anything
     * that is not a Google Maps embed is dropped rather than framed — an
     * admin box is not a good place to accept arbitrary URLs from.
     */
    public function mapUrl(): ?string
    {
        return self::allowedMap(Setting::get('contact_map', ''));
    }

    /** Pure check, so the admin screen can use it without re-reading settings. */
    public static function allowedMap(?string $url): ?string
    {
        $url = trim((string) $url);

        if ($url === '') {
            return null;
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);
        $host   = parse_url($url, PHP_URL_HOST);

        if ($scheme !== 'https' || ! is_string($host)) {
            return null;
        }

        $allowed = ['www.google.com', 'google.com', 'maps.google.com', 'www.google.com.bd', 'maps.app.goo.gl'];

        return in_array(strtolower($host), $allowed, true) ? $url : null;
    }

    /** Someone pasted the whole <iframe …> tag, which is what usually happens. */
    public static function extractMapSrc(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        if (preg_match('/<iframe[^>]+src=["\']([^"\']+)["\']/i', $value, $m)) {
            return $m[1];
        }

        return $value;
    }
}
