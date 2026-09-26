@extends('admin.layouts.app')
@section('title', 'Marketing & Ads')

@section('content')

<style>
.mk-grid{display:grid;grid-template-columns:minmax(0,1fr) 340px;gap:18px;align-items:start}
@media(max-width:1100px){.mk-grid{grid-template-columns:1fr}}

.mk-src{border:1px solid var(--line);border-radius:12px;padding:16px 18px;margin-bottom:14px}
.mk-src:last-of-type{margin-bottom:0}
.mk-top{display:flex;align-items:flex-start;gap:12px;margin-bottom:14px}
.mk-ic{width:32px;height:32px;border-radius:9px;flex:0 0 auto;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:14px}
.mk-meta b{display:block;font-size:14.5px;font-weight:600;line-height:1.3}
.mk-meta span{display:block;color:var(--ink-mute);font-size:12.5px;margin-top:2px}
.mk-row2{display:grid;grid-template-columns:1fr 1fr;gap:12px}
@media(max-width:620px){.mk-row2{grid-template-columns:1fr}}
.mk-f label{display:block;font-size:12.5px;font-weight:600;margin-bottom:5px}
.mk-f input{
  width:100%;font:inherit;font-size:14px;padding:9px 11px;background:#fff;
  border:1px solid var(--line);border-radius:9px;outline:0;
  font-family:ui-monospace,'SF Mono',Menlo,Consolas,monospace;
}
.mk-f input:focus{border-color:var(--navy)}
.mk-f .where{font-size:12px;color:var(--ink-mute);margin-top:6px}
.mk-f .err{color:var(--bad);font-size:12.5px;margin-top:5px}

.mk-stat{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:9px 0;border-bottom:1px solid var(--line)}
.mk-stat:last-child{border-bottom:0}
.mk-stat span{font-size:13.5px}
.mk-tag{font-size:11px;font-weight:700;border-radius:999px;padding:3px 10px;white-space:nowrap}
.mk-on{background:#E4F3EA;color:#14663E}
.mk-off{background:#FBE7E4;color:#9E3423}
.mk-bad{background:#FDF3DC;color:#8A6410}

.mk-ev{display:flex;align-items:flex-start;gap:10px;padding:10px 0;border-bottom:1px solid var(--line)}
.mk-ev:last-child{border-bottom:0}
.mk-ev i{width:7px;height:7px;border-radius:50%;background:var(--gold);margin-top:6px;flex:0 0 auto;display:block}
.mk-ev b{display:block;font-size:13.5px;font-weight:600}
.mk-ev span{display:block;color:var(--ink-mute);font-size:12px;margin-top:2px}
.mk-ev em{
  margin-left:auto;font-style:normal;background:var(--paper);color:var(--ink-mute);
  font-size:10.5px;font-weight:700;border-radius:999px;padding:2px 8px;flex:0 0 auto;
}
.mk-guide{font-size:12.5px;line-height:1.7;color:var(--ink-mute)}
.mk-guide b{display:block;color:var(--ink);font-size:13px;margin:14px 0 4px}
.mk-guide b:first-child{margin-top:0}

.mk-feed{display:flex;align-items:flex-start;gap:13px;padding:16px 0;border-bottom:1px solid var(--line)}
.mk-feed:last-of-type{border-bottom:0}
.mk-feed .body{flex:1 1 auto;min-width:0}
.mk-feed b{display:block;font-size:14.5px;font-weight:600}
.mk-feed span{display:block;color:var(--ink-mute);font-size:12.5px;margin-top:3px;line-height:1.55}
.mk-url{
  display:block;margin-top:9px;font-family:ui-monospace,'SF Mono',Menlo,Consolas,monospace;
  font-size:12.5px;background:var(--paper);border:1px solid var(--line);border-radius:8px;
  padding:8px 10px;word-break:break-all;color:var(--navy);text-decoration:none;
}
.mk-url:hover{border-color:var(--navy)}
.mk-tick{display:flex;align-items:center;gap:8px;flex:0 0 auto;font-size:13px;font-weight:600}
.mk-warn{
  background:#FDF3DC;border:1px solid #EBD79B;color:#7A5A0E;
  border-radius:11px;padding:13px 15px;font-size:13px;line-height:1.6;margin-bottom:16px;
}
</style>

@if (! $codes->anySet())
  <div class="mk-warn">
    <b>Nothing is being tracked yet.</b>
    Until you put at least one ID in below, no pixel loads on the storefront at all — so your
    Facebook ads cannot see who bought, and they cannot retarget anybody.
  </div>
@endif

<div class="mk-grid">

  {{-- ---------------- left: the codes ---------------- --}}
  <div>
    <form method="POST" action="{{ route('admin.marketing.codes') }}">
      @csrf
      @method('PATCH')

      <div class="panel">
        <div class="panel-head">
          <div>
            <h2>Tracking codes</h2>
            <div class="sub">Paste the IDs. The snippets are written for you and load on every shop page.</div>
          </div>
        </div>

        <div class="panel-body">

          <div class="mk-src">
            <div class="mk-top">
              <span class="mk-ic" style="background:#1877F2">f</span>
              <span class="mk-meta">
                <b>Meta Pixel (Facebook &amp; Instagram)</b>
                <span>Tracks visitors, powers retargeting and dynamic product ads.</span>
              </span>
            </div>
            <div class="mk-f">
              <label for="fb_pixel_id">Pixel ID</label>
              <input type="text" id="fb_pixel_id" name="fb_pixel_id" placeholder="1234567890123456"
                     value="{{ old('fb_pixel_id', $settings['fb_pixel_id'] ?? '') }}">
              <div class="where">Events Manager → Data Sources → your pixel → the number under its name.</div>
              @error('fb_pixel_id')<div class="err">{{ $message }}</div>@enderror
            </div>
          </div>

          <div class="mk-src">
            <div class="mk-top">
              <span class="mk-ic" style="background:#E37400">G</span>
              <span class="mk-meta">
                <b>Google Analytics 4</b>
                <span>Where your visitors come from and what they do.</span>
              </span>
            </div>
            <div class="mk-f">
              <label for="ga4_id">Measurement ID</label>
              <input type="text" id="ga4_id" name="ga4_id" placeholder="G-XXXXXXXXXX"
                     value="{{ old('ga4_id', $settings['ga4_id'] ?? '') }}">
              <div class="where">Analytics → Admin → Data Streams → your web stream.</div>
              @error('ga4_id')<div class="err">{{ $message }}</div>@enderror
            </div>
          </div>

          <div class="mk-src">
            <div class="mk-top">
              <span class="mk-ic" style="background:#4285F4">T</span>
              <span class="mk-meta">
                <b>Google Tag Manager</b>
                <span>Optional. Only if you already run your tags through GTM.</span>
              </span>
            </div>
            <div class="mk-f">
              <label for="gtm_id">Container ID</label>
              <input type="text" id="gtm_id" name="gtm_id" placeholder="GTM-XXXXXXX"
                     value="{{ old('gtm_id', $settings['gtm_id'] ?? '') }}">
              <div class="where">Tag Manager → your container → top right.</div>
              @error('gtm_id')<div class="err">{{ $message }}</div>@enderror
            </div>
          </div>

          <div class="mk-src">
            <div class="mk-top">
              <span class="mk-ic" style="background:#34A853">A</span>
              <span class="mk-meta">
                <b>Google Ads conversion</b>
                <span>Tells Google Ads which clicks turned into orders.</span>
              </span>
            </div>
            <div class="mk-row2">
              <div class="mk-f">
                <label for="google_ads_id">Conversion ID</label>
                <input type="text" id="google_ads_id" name="google_ads_id" placeholder="AW-123456789"
                       value="{{ old('google_ads_id', $settings['google_ads_id'] ?? '') }}">
                @error('google_ads_id')<div class="err">{{ $message }}</div>@enderror
              </div>
              <div class="mk-f">
                <label for="google_ads_label">Conversion label</label>
                <input type="text" id="google_ads_label" name="google_ads_label" placeholder="AbC-D_efGh12"
                       value="{{ old('google_ads_label', $settings['google_ads_label'] ?? '') }}">
                @error('google_ads_label')<div class="err">{{ $message }}</div>@enderror
              </div>
            </div>
            <div class="where" style="margin-top:8px">
              Google Ads → Goals → Conversions → your Purchase action → Tag setup. The tag shows
              <code>AW-123456789/AbC-D_efGh12</code> — the part before the slash is the ID, after it the label.
              Without the label, purchases are counted in Analytics but never reported as conversions.
            </div>
          </div>

          <div class="mk-src">
            <div class="mk-top">
              <span class="mk-ic" style="background:#111">t</span>
              <span class="mk-meta">
                <b>TikTok Pixel</b>
                <span>Optional. Worth having if you advertise on TikTok.</span>
              </span>
            </div>
            <div class="mk-f">
              <label for="tiktok_pixel_id">Pixel ID</label>
              <input type="text" id="tiktok_pixel_id" name="tiktok_pixel_id" placeholder="CABCDEFGHIJKLMNOP"
                     value="{{ old('tiktok_pixel_id', $settings['tiktok_pixel_id'] ?? '') }}">
              <div class="where">TikTok Ads Manager → Tools → Events → Web Events.</div>
              @error('tiktok_pixel_id')<div class="err">{{ $message }}</div>@enderror
            </div>
          </div>

          <div style="margin-top:18px">
            <button class="btn btn-navy">Save tracking codes</button>
          </div>

        </div>
      </div>
    </form>

    {{-- ---------------- product feeds ---------------- --}}
    <form method="POST" action="{{ route('admin.marketing.feeds') }}" style="margin-top:18px">
      @csrf
      @method('PATCH')

      <div class="panel">
        <div class="panel-head">
          <div>
            <h2>Product feeds</h2>
            <div class="sub">{{ $feedCount }} {{ Str::plural('product', $feedCount) }} would be sent. Switched-off and zero-price products are left out.</div>
          </div>
        </div>

        <div class="panel-body">

          <div class="mk-feed">
            <div class="body">
              <b>Facebook Product Catalog</b>
              <span>Paste this URL into Commerce Manager → Catalogue → Data Sources → Scheduled Feed. Facebook re-reads it on its own, so new products appear in your ads without you uploading anything.</span>
              <a href="{{ route('feeds.facebook') }}" target="_blank" rel="noopener" class="mk-url">{{ route('feeds.facebook') }}</a>
            </div>
            <label class="mk-tick">
              <input type="hidden" name="feed_facebook_enabled" value="0">
              <input type="checkbox" name="feed_facebook_enabled" value="1" @checked($feeds['facebook'])>
              Enable
            </label>
          </div>

          <div class="mk-feed">
            <div class="body">
              <b>Google Merchant Center</b>
              <span>Merchant Center → Products → Feeds → add a scheduled fetch pointing at this URL. Needed for Shopping ads and free product listings.</span>
              <a href="{{ route('feeds.google') }}" target="_blank" rel="noopener" class="mk-url">{{ route('feeds.google') }}</a>
            </div>
            <label class="mk-tick">
              <input type="hidden" name="feed_google_enabled" value="0">
              <input type="checkbox" name="feed_google_enabled" value="1" @checked($feeds['google'])>
              Enable
            </label>
          </div>

          <div style="display:flex;gap:10px;margin-top:18px;align-items:center;flex-wrap:wrap">
            <button class="btn btn-navy">Save feeds</button>
            <a href="{{ route('admin.marketing.refresh') }}"
               onclick="event.preventDefault();document.getElementById('refreshFeeds').submit()"
               class="btn btn-line">Rebuild now</a>
            <span class="sub" style="font-size:12.5px">Feeds are cached for an hour. Rebuild after a price change.</span>
          </div>

        </div>
      </div>
    </form>

    <form method="POST" action="{{ route('admin.marketing.refresh') }}" id="refreshFeeds" style="display:none">
      @csrf
    </form>
  </div>

  {{-- ---------------- right: status ---------------- --}}
  <div>
    <div class="panel">
      <div class="panel-head"><div><h2>Current status</h2></div></div>
      <div class="panel-body">
        @php
          $sources = [
            'fb_pixel_id'     => 'Meta Pixel',
            'ga4_id'          => 'Google Analytics 4',
            'gtm_id'          => 'Google Tag Manager',
            'google_ads_id'   => 'Google Ads',
            'tiktok_pixel_id' => 'TikTok Pixel',
          ];
        @endphp

        @foreach ($sources as $key => $label)
          <div class="mk-stat">
            <span>{{ $label }}</span>
            @if ($codes->isSet($key))
              <span class="mk-tag mk-on">Live</span>
            @elseif ($codes->isWrong($key))
              <span class="mk-tag mk-bad">Wrong format</span>
            @else
              <span class="mk-tag mk-off">Not set</span>
            @endif
          </div>
        @endforeach

        <div class="mk-stat">
          <span>Google Ads label</span>
          @if ($codes->isSet('google_ads_label'))
            <span class="mk-tag mk-on">Live</span>
          @elseif ($codes->isSet('google_ads_id'))
            <span class="mk-tag mk-bad">Needed</span>
          @else
            <span class="mk-tag mk-off">Not set</span>
          @endif
        </div>

        <div class="mk-stat">
          <span>Facebook feed</span>
          <span class="mk-tag {{ $feeds['facebook'] ? 'mk-on' : 'mk-off' }}">{{ $feeds['facebook'] ? 'Live' : 'Off' }}</span>
        </div>
        <div class="mk-stat">
          <span>Google feed</span>
          <span class="mk-tag {{ $feeds['google'] ? 'mk-on' : 'mk-off' }}">{{ $feeds['google'] ? 'Live' : 'Off' }}</span>
        </div>
      </div>
    </div>

    <div class="panel" style="margin-top:18px">
      <div class="panel-head">
        <div>
          <h2>Events sent automatically</h2>
          <div class="sub">Once a pixel ID is set, these fire on their own.</div>
        </div>
      </div>
      <div class="panel-body">
        <div class="mk-ev"><i></i><span><b>PageView</b><span>Every page</span></span><em>AUTO</em></div>
        <div class="mk-ev"><i></i><span><b>ViewContent</b><span>Product page, with its price</span></span><em>AUTO</em></div>
        <div class="mk-ev"><i></i><span><b>AddToCart</b><span>Add-to-cart button</span></span><em>AUTO</em></div>
        <div class="mk-ev"><i></i><span><b>InitiateCheckout</b><span>Checkout page, with the basket</span></span><em>AUTO</em></div>
        <div class="mk-ev"><i></i><span><b>Purchase</b><span>Order confirmed, with the real total</span></span><em>AUTO</em></div>
      </div>
    </div>

    <div class="panel" style="margin-top:18px">
      <div class="panel-head"><div><h2>Where to find the IDs</h2></div></div>
      <div class="panel-body mk-guide">
        <b>Meta Pixel</b>
        business.facebook.com → Events Manager → Data Sources → Create or open a pixel → copy the ID under its name.

        <b>Google Analytics 4</b>
        analytics.google.com → Admin → Data Streams → your website → Measurement ID, top right.

        <b>Google Ads</b>
        Goals → Conversions → create a <em>Purchase</em> action for a website → Tag setup → the ID and label are in the code shown.

        <b>Checking it works</b>
        Install the <em>Meta Pixel Helper</em> extension in Chrome, open your shop, and it should show
        PageView. Open a product and it should show ViewContent as well. In GA4, Reports → Realtime
        shows the same within a minute or two.
      </div>
    </div>
  </div>

</div>

@endsection
