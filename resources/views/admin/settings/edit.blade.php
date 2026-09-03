@extends('admin.layouts.app')
@section('title', 'Store settings')

@section('content')

@if ($errors->any())
  <div class="alert alert-bad">
    <ul style="margin:0;padding-left:18px">
      @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
  </div>
@endif

<form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
  @csrf

  <div class="form-grid">
  <div class="form-main">

    <div class="panel">
      <div class="panel-head"><h2>Store identity</h2></div>
      <div class="panel-body">
        <div class="row2">
          <div class="field">
            <label for="store_name">Store name</label>
            <input type="text" id="store_name" name="store_name" value="{{ old('store_name', $settings['store_name'] ?? '') }}" required>
          </div>
          <div class="field">
            <label for="store_tagline">Tagline</label>
            <input type="text" id="store_tagline" name="store_tagline" value="{{ old('store_tagline', $settings['store_tagline'] ?? '') }}">
          </div>
        </div>

        <div class="row2">
          <div class="field">
            <label for="logo">Logo</label>
            @if (!empty($settings['logo']))
              <img src="{{ Storage::url($settings['logo']) }}" alt="" style="height:48px;margin-bottom:9px;background:var(--paper);padding:5px;border-radius:3px">
            @endif
            <input type="file" id="logo" name="logo" accept="image/*">
            <div class="hint">PNG with a transparent background works best. Kept as uploaded, never re-encoded.</div>
          </div>
          <div class="field">
            <label for="favicon">Favicon</label>
            @if (!empty($settings['favicon']))
              <img src="{{ Storage::url($settings['favicon']) }}" alt="" style="height:32px;margin-bottom:9px">
            @endif
            <input type="file" id="favicon" name="favicon" accept="image/*">
            <div class="hint">32×32 or 64×64 PNG.</div>
          </div>
        </div>
      </div>
    </div>

    <div class="panel">
      <div class="panel-head"><h2>Contact</h2></div>
      <div class="panel-body">
        <div class="row2">
          <div class="field">
            <label for="store_phone">Phone</label>
            <input type="text" id="store_phone" name="store_phone" value="{{ old('store_phone', $settings['store_phone'] ?? '') }}">
          </div>
          <div class="field">
            <label for="store_whatsapp">WhatsApp number</label>
            <input type="text" id="store_whatsapp" name="store_whatsapp" value="{{ old('store_whatsapp', $settings['store_whatsapp'] ?? '') }}" placeholder="8801347419040">
            <div class="hint">Country code, no plus sign or spaces. This builds the wa.me link.</div>
          </div>
        </div>
        <div class="row2">
          <div class="field">
            <label for="store_email">Email</label>
            <input type="text" id="store_email" name="store_email" value="{{ old('store_email', $settings['store_email'] ?? '') }}">
          </div>
          <div class="field">
            <label for="store_address">Address</label>
            <input type="text" id="store_address" name="store_address" value="{{ old('store_address', $settings['store_address'] ?? '') }}">
          </div>
        </div>
      </div>
    </div>

    <div class="panel">
      <div class="panel-head"><h2>Homepage words</h2></div>
      <div class="panel-body">
        <div class="field">
          <label for="topbar_message">Top bar message</label>
          <input type="text" id="topbar_message" name="topbar_message" value="{{ old('topbar_message', $settings['topbar_message'] ?? '') }}">
          <div class="hint">The thin navy strip above the logo. The most-read line on the site.</div>
        </div>
        <div class="row2">
          <div class="field">
            <label for="hero_eyebrow">Hero eyebrow</label>
            <input type="text" id="hero_eyebrow" name="hero_eyebrow" value="{{ old('hero_eyebrow', $settings['hero_eyebrow'] ?? '') }}">
          </div>
          <div class="field">
            <label for="hero_heading">Hero heading</label>
            <input type="text" id="hero_heading" name="hero_heading" value="{{ old('hero_heading', $settings['hero_heading'] ?? '') }}">
          </div>
        </div>
        <div class="field">
          <label for="hero_text">Hero paragraph</label>
          <textarea id="hero_text" name="hero_text" style="min-height:80px">{{ old('hero_text', $settings['hero_text'] ?? '') }}</textarea>
        </div>
        <div class="field">
          <label for="footer_about">Footer about text</label>
          <textarea id="footer_about" name="footer_about" style="min-height:70px">{{ old('footer_about', $settings['footer_about'] ?? '') }}</textarea>
        </div>
      </div>
    </div>

    <div class="panel">
      <div class="panel-head"><h2>Delivery charges</h2></div>
      <div class="panel-body">
        <div class="note" style="margin-bottom:16px">
          These are display-only for now. Checkout will calculate from real shipping zones in Batch 9 —
          keep the two in step until then.
        </div>
        <div class="row2">
          <div class="field">
            <label for="delivery_dhaka">Inside Dhaka city (৳)</label>
            <input type="number" step="1" min="0" id="delivery_dhaka" name="delivery_dhaka" value="{{ old('delivery_dhaka', $settings['delivery_dhaka'] ?? '') }}">
          </div>
          <div class="field">
            <label for="delivery_suburb">Dhaka sub-district (৳)</label>
            <input type="number" step="1" min="0" id="delivery_suburb" name="delivery_suburb" value="{{ old('delivery_suburb', $settings['delivery_suburb'] ?? '') }}">
          </div>
        </div>
        <div class="row2">
          <div class="field">
            <label for="delivery_city">Divisional cities (৳)</label>
            <input type="number" step="1" min="0" id="delivery_city" name="delivery_city" value="{{ old('delivery_city', $settings['delivery_city'] ?? '') }}">
          </div>
          <div class="field">
            <label for="delivery_outside">Rest of Bangladesh (৳)</label>
            <input type="number" step="1" min="0" id="delivery_outside" name="delivery_outside" value="{{ old('delivery_outside', $settings['delivery_outside'] ?? '') }}">
          </div>
        </div>
        <div class="row2">
          <div class="field">
            <label for="free_delivery_over">Free delivery above (৳)</label>
            <input type="number" step="1" min="0" id="free_delivery_over" name="free_delivery_over" value="{{ old('free_delivery_over', $settings['free_delivery_over'] ?? '') }}">
          </div>
          <div class="field">
            <label for="return_days">Return window (days)</label>
            <input type="number" step="1" min="0" max="90" id="return_days" name="return_days" value="{{ old('return_days', $settings['return_days'] ?? '') }}">
          </div>
        </div>
      </div>
    </div>

    <div class="panel">
      <div class="panel-head"><h2>Social and search</h2></div>
      <div class="panel-body">
        <div class="field">
          <label for="facebook_url">Facebook page URL</label>
          <input type="text" id="facebook_url" name="facebook_url" value="{{ old('facebook_url', $settings['facebook_url'] ?? '') }}">
        </div>
        <div class="row2">
          <div class="field">
            <label for="instagram_url">Instagram URL</label>
            <input type="text" id="instagram_url" name="instagram_url" value="{{ old('instagram_url', $settings['instagram_url'] ?? '') }}">
          </div>
          <div class="field">
            <label for="youtube_url">YouTube URL</label>
            <input type="text" id="youtube_url" name="youtube_url" value="{{ old('youtube_url', $settings['youtube_url'] ?? '') }}">
          </div>
        </div>

        <div class="divider"><span>Homepage SEO</span></div>

        <div class="field">
          <label for="meta_title">Meta title</label>
          <input type="text" id="meta_title" name="meta_title" maxlength="70" value="{{ old('meta_title', $settings['meta_title'] ?? '') }}">
          <div class="hint">Under 70 characters.</div>
        </div>
        <div class="field">
          <label for="meta_description">Meta description</label>
          <textarea id="meta_description" name="meta_description" maxlength="180" style="min-height:66px">{{ old('meta_description', $settings['meta_description'] ?? '') }}</textarea>
        </div>
      </div>
    </div>

  </div>

  <div class="form-side">
    <div class="panel">
      <div class="panel-head"><h2>Save</h2></div>
      <div class="panel-body">
        <button type="submit" class="btn btn-gold" style="width:100%">Save settings</button>
        <a href="{{ url('/') }}" target="_blank" class="btn btn-line" style="width:100%;margin-top:9px">View storefront</a>
        <p class="sub" style="margin:14px 0 0">
          Settings are cached, so saving clears the cache automatically. No artisan command needed.
        </p>
      </div>
    </div>
  </div>
  </div>
</form>

@endsection
