@extends('site.layouts.app')

@section('content')

{{-- hero --}}
<div class="hero">
  <svg class="arcs" viewBox="0 0 820 820" fill="none" aria-hidden="true">
    <circle cx="410" cy="410" r="320" stroke="#DFA327" stroke-width="1.1" stroke-opacity=".36"/>
    <circle cx="410" cy="410" r="242" stroke="#DFA327" stroke-width="1.1" stroke-opacity=".26"/>
    <ellipse cx="410" cy="410" rx="140" ry="320" stroke="#DFA327" stroke-width="1.1" stroke-opacity=".28"/>
    <path d="M90 410h640M136 290h548M136 530h548" stroke="#DFA327" stroke-width="1.1" stroke-opacity=".22"/>
    <path d="M40 540c140 126 480 118 660-150" stroke="#DFA327" stroke-width="2.4" stroke-opacity=".5" stroke-linecap="round"/>
  </svg>

  <div class="wrap">
    <div>
      @if (!empty($settings['hero_eyebrow']))
        <div class="flag">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 21s7-6.2 7-11a7 7 0 10-14 0c0 4.8 7 11 7 11z"/><circle cx="12" cy="10" r="2.4"/></svg>
          {{ $settings['hero_eyebrow'] }}
        </div>
      @endif
      <h1>{{ $settings['hero_heading'] ?? __('Real formulas, not lookalikes.') }}</h1>
      @if (!empty($settings['hero_text']))
        <p>{{ $settings['hero_text'] }}</p>
      @endif
      <div class="hcta">
        <a href="{{ route('shop') }}" class="btn btn-gold">{{ __('Shop now') }}</a>
        @if (!empty($settings['store_whatsapp']))
          <a href="https://wa.me/{{ $settings['store_whatsapp'] }}" class="btn btn-ghost" target="_blank" rel="noopener">{{ __('Order on WhatsApp') }}</a>
        @endif
      </div>
    </div>

    @if ($featured->isNotEmpty())
      @php $star = $featured->first(); @endphp
      <div class="feat">
        <div class="feat-shot">
          @if ($star->primaryImage)
            <img src="{{ $star->primaryImage->url }}" alt="{{ $star->name }}">
          @else
            <svg width="82" height="140" viewBox="0 0 88 150" fill="none" aria-hidden="true"><rect x="26" y="4" width="36" height="20" rx="3" fill="#14305A" opacity=".22"/><rect x="14" y="24" width="60" height="122" rx="8" fill="#14305A" opacity=".13"/><circle cx="44" cy="104" r="15" fill="#DFA327" opacity=".28"/></svg>
          @endif
        </div>
        @if ($star->brand)<div class="feat-eb">{{ $star->brand->name }} · {{ __("this week's pick") }}</div>@endif
        <h3>{{ $star->name }}</h3>
        <div class="pr" style="margin:14px 0 16px">
          @if ($star->has_variants)<span class="from">{{ __('from') }}</span>@endif
          <span class="price" style="font-size:26px">৳{{ number_format($star->display_price) }}</span>
          @if ($star->compare_price && ! $star->has_variants)
            <span class="was" style="font-size:15px">৳{{ number_format($star->compare_price) }}</span>
          @endif
        </div>
        <a href="{{ route('shop.product', $star) }}" class="btn btn-gold" style="width:100%">{{ __('View product') }}</a>
      </div>
    @endif
  </div>
</div>

{{-- trust band --}}
<div class="trust"><div class="wrap">
  <div class="ti">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="6" width="20" height="13" rx="2"/><circle cx="12" cy="12.5" r="2.6"/></svg>
    <div><b>{{ __('Pay on delivery') }}</b><small>{{ __('Cash or bKash when it arrives') }}</small></div>
  </div>
  <div class="ti">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 2.5l8 3.2v6c0 5-3.4 8.6-8 10-4.6-1.4-8-5-8-10v-6z"/><path d="M8.8 12.2l2.3 2.3 4.3-4.6"/></svg>
    <div><b>{{ __('Batch code on every box') }}</b><small>{{ __("Verify on the brand's own site") }}</small></div>
  </div>
  <div class="ti">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="1.5" y="6" width="13" height="11" rx="1.5"/><path d="M14.5 10h4l3 3.2V17h-7z"/><circle cx="6" cy="18.5" r="1.8"/><circle cx="17.5" cy="18.5" r="1.8"/></svg>
    <div><b>{{ __('Dhaka in 24 hours') }}</b><small>{{ __('Two to three days elsewhere') }}</small></div>
  </div>
  <div class="ti">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 12a9 9 0 102.6-6.4"/><path d="M3 4v5h5"/></svg>
    <div><b>{{ __(':n days to return', ['n' => $settings['return_days'] ?? 7]) }}</b><small>{{ __('Unopened items, refunded in full') }}</small></div>
  </div>
</div></div>

{{-- departments --}}
@if ($departments->isNotEmpty())
<section>
  <div class="wrap">
    <div class="sh"><div><h2>{{ __('Shop by department') }}</h2></div></div>
    <div class="cats">
      @foreach ($departments as $i => $department)
        <a href="{{ route('shop.category', $department) }}" class="cat c{{ ($i % 4) + 1 }}">
          <span class="cat-n">{{ str_pad($department->products_count, 2, '0', STR_PAD_LEFT) }}</span>
          <h3>{{ $department->name }}</h3>
          @if ($department->description)<p>{{ $department->description }}</p>@endif
        </a>
      @endforeach
    </div>
  </div>
</section>
@endif

{{-- brands --}}
@if ($brands->isNotEmpty())
<section class="onwhite">
  <div class="wrap">
    <div class="sh"><div><h2>{{ __('Brands we import ourselves') }}</h2><p>{{ __('No local distributor in between, which is why the batch codes check out.') }}</p></div></div>
    <div class="brow">
      @foreach ($brands as $brand)
        <a href="{{ route('shop.brand', $brand) }}" class="bcell">{{ $brand->name }}</a>
      @endforeach
    </div>
  </div>
</section>
@endif

{{-- featured --}}
@if ($featured->isNotEmpty())
<section id="featured">
  <div class="wrap">
    <div class="sh"><div><h2>{{ __('Best sellers') }}</h2><p>{{ __('Ranked by reorders, not by what we want to clear.') }}</p></div></div>
    <div class="grid">
      @foreach ($featured as $product)
        @include('site.partials.product-card')
      @endforeach
    </div>
  </div>
</section>
@endif

{{-- newest --}}
@if ($newest->isNotEmpty())
<section class="tight">
  <div class="wrap">
    <div class="sh"><div><h2>{{ __('Just landed') }}</h2><p>{{ __('The most recent additions to the catalog.') }}</p></div></div>
    <div class="grid">
      @foreach ($newest as $product)
        @include('site.partials.product-card')
      @endforeach
    </div>
  </div>
</section>
@endif

{{-- delivery --}}
<section class="onwhite">
  <div class="wrap">
    <div class="sh">
      <div>
        <h2>{{ __('Delivery charges, stated up front') }}</h2>
        <p>{{ __('No surprise at checkout. Free delivery anywhere on orders above ৳:n.', ['n' => number_format($settings['free_delivery_over'] ?? 2000)]) }}</p>
      </div>
    </div>
    <div class="zones">
      <div class="zone"><b>{{ __('Inside Dhaka city') }}</b><div class="days">{{ __('Next day, often same day') }}</div><div class="fee">৳{{ number_format($settings['delivery_dhaka'] ?? 60) }}</div></div>
      <div class="zone"><b>{{ __('Dhaka sub-district') }}</b><div class="days">{{ __('One to two days') }}</div><div class="fee">৳{{ number_format($settings['delivery_suburb'] ?? 90) }}</div></div>
      <div class="zone"><b>{{ __('Divisional cities') }}</b><div class="days">{{ __('Two days') }}</div><div class="fee">৳{{ number_format($settings['delivery_city'] ?? 120) }}</div></div>
      <div class="zone"><b>{{ __('Rest of Bangladesh') }}</b><div class="days">{{ __('Two to three days') }}</div><div class="fee">৳{{ number_format($settings['delivery_outside'] ?? 150) }}</div></div>
    </div>
  </div>
</section>

@endsection
