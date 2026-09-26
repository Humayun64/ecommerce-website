<?php

namespace App\Services;

use App\Models\Setting;

/**
 * Builds the tracking snippets from what is saved in admin.
 *
 * Everything is escaped and shape-checked before it reaches the page: an ID
 * typed into an admin box ends up inside a <script> tag on every storefront
 * page, so a stray quote there would break the whole site, and a deliberate
 * one would be an injection. Nothing that fails its pattern is written out.
 */
class TrackingCodes
{
    /** What each ID has to look like before it is allowed on the page. */
    private const SHAPES = [
        'fb_pixel_id'      => '/^\d{8,20}$/',
        'ga4_id'           => '/^G-[A-Z0-9]{4,20}$/i',
        'gtm_id'           => '/^GTM-[A-Z0-9]{4,12}$/i',
        'google_ads_id'    => '/^AW-\d{6,15}$/i',
        'google_ads_label' => '/^[A-Za-z0-9_\-]{5,40}$/',
        'tiktok_pixel_id'  => '/^[A-Z0-9]{10,30}$/i',
    ];

    public function get(string $key): ?string
    {
        $value = trim((string) Setting::get($key, ''));

        if ($value === '') {
            return null;
        }

        return preg_match(self::SHAPES[$key] ?? '/^.*$/', $value) ? $value : null;
    }

    public function all(): array
    {
        $out = [];

        foreach (array_keys(self::SHAPES) as $key) {
            $out[$key] = $this->get($key);
        }

        return $out;
    }

    /** Is this ID saved and the right shape? Drives the status panel. */
    public function isSet(string $key): bool
    {
        return $this->get($key) !== null;
    }

    /** Saved, but not a shape the provider would recognise. */
    public function isWrong(string $key): bool
    {
        return trim((string) Setting::get($key, '')) !== '' && ! $this->isSet($key);
    }

    public function anySet(): bool
    {
        foreach (array_keys(self::SHAPES) as $key) {
            if ($this->isSet($key)) {
                return true;
            }
        }

        return false;
    }

    /** Everything that belongs in <head>. */
    public function head(): string
    {
        $out = [];

        if ($id = $this->get('gtm_id')) {
            $out[] = $this->gtmHead($id);
        }

        if ($id = $this->get('ga4_id')) {
            $out[] = $this->gtag($id);
        }

        if ($id = $this->get('google_ads_id')) {
            // gtag.js is loaded once; a second product just gets configured.
            $out[] = $this->get('ga4_id') ? $this->gtagConfig($id) : $this->gtag($id);
        }

        if ($id = $this->get('fb_pixel_id')) {
            $out[] = $this->metaPixel($id);
        }

        if ($id = $this->get('tiktok_pixel_id')) {
            $out[] = $this->tiktokPixel($id);
        }

        if ($out) {
            $out[] = '<script>window.amjrTrack='
                . json_encode([
                    'fb'    => $this->get('fb_pixel_id'),
                    'ga4'   => $this->get('ga4_id'),
                    'ads'   => $this->get('google_ads_id'),
                    'label' => $this->get('google_ads_label'),
                    'tt'    => $this->get('tiktok_pixel_id'),
                ], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)
                . ';</script>';
        }

        return implode("\n", $out);
    }

    /** Everything that belongs immediately after <body>. */
    public function body(): string
    {
        $out = [];

        if ($id = $this->get('gtm_id')) {
            $out[] = '<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=' . e($id)
                . '" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>';
        }

        if ($id = $this->get('fb_pixel_id')) {
            $out[] = '<noscript><img height="1" width="1" style="display:none" alt=""'
                . ' src="https://www.facebook.com/tr?id=' . e($id) . '&ev=PageView&noscript=1"></noscript>';
        }

        return implode("\n", $out);
    }

    /**
     * Goes just before </body>, not after <body>: it reads the event block
     * the page prints, so it has to run once that block has been parsed.
     */
    public function footer(): string
    {
        return $this->anySet()
            ? '<script src="' . e(asset('js/track.js')) . '" defer></script>'
            : '';
    }

    /* ---------------- the snippets themselves ---------------- */

    private function gtmHead(string $id): string
    {
        $id = e($id);

        return <<<HTML
        <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});
        var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';
        j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','{$id}');</script>
        HTML;
    }

    private function gtag(string $id): string
    {
        $id = e($id);

        return <<<HTML
        <script async src="https://www.googletagmanager.com/gtag/js?id={$id}"></script>
        <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}
        gtag('js',new Date());gtag('config','{$id}');</script>
        HTML;
    }

    private function gtagConfig(string $id): string
    {
        $id = e($id);

        return "<script>gtag('config','{$id}');</script>";
    }

    private function metaPixel(string $id): string
    {
        $id = e($id);

        return <<<HTML
        <script>!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
        n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,
        'script','https://connect.facebook.net/en_US/fbevents.js');
        fbq('init','{$id}');fbq('track','PageView');</script>
        HTML;
    }

    private function tiktokPixel(string $id): string
    {
        $id = e($id);

        return <<<HTML
        <script>!function(w,d,t){w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];
        ttq.methods=['page','track','identify','instances','debug','on','off','once','ready','alias','group','enableCookie','disableCookie'];
        ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};
        for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);
        ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e};
        ttq.load=function(e,n){var r='https://analytics.tiktok.com/i18n/pixel/events.js';
        ttq._i=ttq._i||{};ttq._i[e]=[];ttq._i[e]._u=r;ttq._t=ttq._t||{};ttq._t[e]=+new Date;
        ttq._o=ttq._o||{};ttq._o[e]=n||{};var o=d.createElement('script');o.type='text/javascript';
        o.async=!0;o.src=r+'?sdkid='+e+'&lib='+t;var a=d.getElementsByTagName('script')[0];
        a.parentNode.insertBefore(o,a)};ttq.load('{$id}');ttq.page();}(window,document,'ttq');</script>
        HTML;
    }
}
