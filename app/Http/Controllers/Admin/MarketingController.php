<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\ProductFeed;
use App\Services\TrackingCodes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MarketingController extends Controller
{
    public function __construct(
        private TrackingCodes $codes,
        private ProductFeed $feed,
    ) {
    }

    public function index()
    {
        return view('admin.marketing.index', [
            'codes'    => $this->codes,
            'settings' => Setting::all_cached(),
            'feeds'    => [
                'facebook' => $this->feed->enabled('facebook'),
                'google'   => $this->feed->enabled('google'),
            ],
            'feedCount' => $this->feed->products()->count(),
        ]);
    }

    public function updateCodes(Request $request)
    {
        // People paste the whole <meta ...> tag Google shows them as often as
        // the code inside it. Either is fine — keep just the code.
        $pasted = trim((string) $request->input('gsc_verification'));

        if (preg_match('/content\s*=\s*["\']([^"\']+)["\']/i', $pasted, $m)) {
            $pasted = trim($m[1]);
        }

        $request->merge(['gsc_verification' => $pasted]);

        $data = $request->validate([
            'fb_pixel_id'      => ['nullable', 'string', 'regex:/^\d{8,20}$/'],
            'ga4_id'           => ['nullable', 'string', 'regex:/^G-[A-Z0-9]{4,20}$/i'],
            'gtm_id'           => ['nullable', 'string', 'regex:/^GTM-[A-Z0-9]{4,12}$/i'],
            'google_ads_id'    => ['nullable', 'string', 'regex:/^AW-\d{6,15}$/i'],
            'google_ads_label' => ['nullable', 'string', 'regex:/^[A-Za-z0-9_\-]{5,40}$/'],
            'tiktok_pixel_id'  => ['nullable', 'string', 'regex:/^[A-Z0-9]{10,30}$/i'],
            'gsc_verification' => ['nullable', 'string', 'regex:/^[A-Za-z0-9_\-]{20,100}$/'],
        ], [
            'fb_pixel_id.regex'      => __('A Meta Pixel ID is 8 to 20 digits, nothing else.'),
            'ga4_id.regex'           => __('A GA4 Measurement ID looks like G-XXXXXXXXXX.'),
            'gtm_id.regex'           => __('A GTM Container ID looks like GTM-XXXXXXX.'),
            'google_ads_id.regex'    => __('A Google Ads Conversion ID looks like AW-123456789.'),
            'google_ads_label.regex' => __('The conversion label is the short code after the slash in Google Ads.'),
            'tiktok_pixel_id.regex'  => __('A TikTok Pixel ID is letters and numbers, 10 to 30 characters.'),
            'gsc_verification.regex' => __('Paste the HTML tag from Search Console, or just the code inside content="…".'),
        ]);

        Setting::put(array_map(fn ($value) => trim((string) $value), $data));

        return back()->with('status', __('Tracking codes saved. They are live on the storefront now.'));
    }

    public function updateFeeds(Request $request)
    {
        Setting::put([
            'feed_facebook_enabled' => $request->boolean('feed_facebook_enabled') ? '1' : '0',
            'feed_google_enabled'   => $request->boolean('feed_google_enabled') ? '1' : '0',
        ]);

        // A feed the shop owner just switched on should not serve an hour-old
        // copy built before they changed anything.
        Cache::forget('feed.facebook');
        Cache::forget('feed.google');

        return back()->with('status', __('Product feeds updated.'));
    }

    /** Rebuild now, for when a price changed and the ad still shows the old one. */
    public function refreshFeeds()
    {
        Cache::forget('feed.facebook');
        Cache::forget('feed.google');

        return back()->with('status', __('Feeds rebuilt. Facebook and Google will pick up the new version on their next fetch.'));
    }
}
