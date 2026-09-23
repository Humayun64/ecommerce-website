@extends('site.layouts.app')

@section('title', $product->seo_title . ' — ' . ($settings['store_name'] ?? 'AMJR Global'))
@section('meta_description', $product->seo_description)
@section('og_title', $product->share_title)
@section('og_description', $product->share_description)
@section('og_image')
  @if ($product->primaryImage)
    <meta property="og:image" content="{{ url($product->primaryImage->url) }}">
  @endif
@endsection

@push('head')
  @unless ($product->is_indexable)<meta name="robots" content="noindex">@endunless
  @if ($product->canonical_url)<link rel="canonical" href="{{ $product->canonical_url }}">@endif
  <link rel="stylesheet" href="{{ asset('css/product.css') }}">
  <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')

@php
  $ratingCount = $product->rating_count;
  $ratingAvg   = $product->rating_average;
  $breakdown   = $product->rating_breakdown;
@endphp

<div class="crumbs"><div class="wrap">
  <a href="{{ route('home') }}">{{ __('Home') }}</a><span>/</span>
  @if ($product->category?->parent)
    <a href="{{ route('shop.category', $product->category->parent) }}">{{ $product->category->parent->name }}</a><span>/</span>
  @endif
  @if ($product->category)
    <a href="{{ route('shop.category', $product->category) }}">{{ $product->category->name }}</a><span>/</span>
  @endif
  <strong>{{ $product->name }}</strong>
</div></div>

<div class="pp">

  <div class="wrap pp-main">

    {{-- ---------- gallery ---------- --}}
    <div class="pp-gallery">
      <div class="pp-stage">
        <div class="pp-flags">
          @if ($product->origin)
            <span class="pp-flag">{{ $product->origin === 'South Korea' ? __('Made in Korea') : $product->origin }}</span>
          @endif
          @if ($product->discount_percent && ! $product->has_variants)
            <span class="pp-flag sale">{{ $product->discount_percent }}% {{ __('off') }}</span>
          @endif
        </div>

        @if ($product->images->isNotEmpty())
          <img src="{{ $product->images->first()->url }}" alt="{{ $product->name }}" id="galleryImage">
        @else
          <svg width="170" height="290" viewBox="0 0 88 150" fill="none" aria-hidden="true">
            <rect x="26" y="4" width="36" height="20" rx="3" fill="#14305A" opacity=".2"/>
            <rect x="14" y="24" width="60" height="122" rx="8" fill="#14305A" opacity=".12"/>
            <circle cx="44" cy="100" r="14" fill="#DFA327" opacity=".26"/>
          </svg>
        @endif
      </div>

      @if ($product->images->count() > 1)
        <div class="pp-thumbs">
          @foreach ($product->images as $i => $image)
            <button type="button" class="pp-thumb {{ $i === 0 ? 'on' : '' }}" data-src="{{ $image->url }}">
              <img src="{{ $image->url }}" alt="" loading="lazy">
            </button>
          @endforeach
        </div>
      @endif
    </div>

    {{-- ---------- buy box ---------- --}}
    <div class="pp-buy">
      @if ($product->brand)
        <a href="{{ route('shop.brand', $product->brand) }}" class="pp-brand">{{ $product->brand->name }}</a>
      @endif

      <h1 class="pp-title">{{ $product->name }}</h1>

      <div class="pp-ratingrow">
        @if ($ratingCount > 0)
          @include('site.partials.stars', ['rating' => $ratingAvg])
          <a href="#reviews" class="pp-ratinglink" data-gotab="reviews">
            <b>{{ number_format($ratingAvg, 1) }}</b> · {{ trans_choice(':count review|:count reviews', $ratingCount) }}
          </a>
        @else
          @include('site.partials.stars', ['rating' => 0])
          <span class="pp-noreviews">{{ __('No reviews yet') }}</span>
        @endif
      </div>

      <div class="pp-meta">
        @if ($product->size_label)
          <span class="pp-chip">{{ $product->size_label }}</span>
        @endif
        <span>{{ __('SKU') }}: <b id="skuLabel">{{ $product->sku }}</b></span>
      </div>

      @if ($product->short_description)
        <p class="pp-short">{{ $product->short_description }}</p>
      @endif

      <div class="pp-price">
        @if ($product->has_variants)<span class="pp-from">{{ __('from') }}</span>@endif
        <span class="pp-now" id="priceMain">৳{{ number_format($product->display_price) }}</span>
        <span class="pp-was" id="priceWas" style="{{ $product->compare_price && ! $product->has_variants ? '' : 'display:none' }}">৳{{ number_format($product->compare_price ?? 0) }}</span>
        <span class="pp-save" id="priceSave" style="{{ $product->discount_percent && ! $product->has_variants ? '' : 'display:none' }}">{{ __('Save') }} {{ $product->discount_percent }}%</span>
      </div>

      @if ($product->has_variants)
        <div class="pp-axes">
          @foreach ($product->productAttributes as $attribute)
            @php $used = $product->variants->flatMap->values->where('attribute_id', $attribute->id)->unique('id')->sortBy('sort_order'); @endphp
            @continue($used->isEmpty())
            <div class="pp-axis" data-axis="{{ $attribute->id }}">
              <div class="pp-axislabel">{{ $attribute->name }}</div>
              <div class="pp-opts">
                @foreach ($used as $value)
                  <button type="button" class="pp-opt" data-axis="{{ $attribute->id }}" data-value="{{ $value->id }}">{{ $value->value }}</button>
                @endforeach
              </div>
            </div>
          @endforeach
        </div>
      @endif

      <div class="pp-stock {{ $product->is_out_of_stock ? 'out' : ($product->is_low_stock ? 'low' : ($product->has_variants ? 'muted' : 'ok')) }}" id="stockLine">
        <span class="dot"></span>
        @if ($product->is_out_of_stock)
          {{ __('Out of stock') }}
        @elseif ($product->is_low_stock)
          {{ __('Only :n left', ['n' => $product->total_stock]) }}
        @elseif ($product->has_variants)
          {{ __('Choose an option to see stock') }}
        @else
          {{ __('In stock') }}
        @endif
      </div>

      <form method="POST" action="{{ route('cart.store') }}" id="addForm">
        @csrf
        <input type="hidden" name="product_id" value="{{ $product->id }}">
        <input type="hidden" name="variant_id" id="variantId" value="">

        <div class="pp-actions">
          <div class="pp-qty">
            <button type="button" id="qtyDown" aria-label="{{ __('Decrease') }}">−</button>
            <input type="number" name="quantity" id="qty" value="1" min="1" aria-label="{{ __('Quantity') }}">
            <button type="button" id="qtyUp" aria-label="{{ __('Increase') }}">+</button>
          </div>

          <button type="submit" class="pp-cta pp-cta-gold" id="addToCart" @disabled($product->is_out_of_stock || $product->has_variants)>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 7h13l-1.4 9.3a2 2 0 01-2 1.7H9.4a2 2 0 01-2-1.7L5.6 4H3"/><circle cx="10" cy="20.5" r="1.3" fill="currentColor" stroke="none"/><circle cx="17" cy="20.5" r="1.3" fill="currentColor" stroke="none"/></svg>
            <span>{{ $product->is_out_of_stock ? __('Sold out') : __('Add to cart') }}</span>
          </button>

          <button type="submit" name="buy_now" value="1" data-buynow class="pp-cta pp-cta-navy pp-buynow" id="buyNow" @disabled($product->is_out_of_stock || $product->has_variants)>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2L4.5 13.5H11l-1 8.5 8.5-11.5H12z"/></svg>
            <span>{{ __('Buy now') }}</span>
          </button>
        </div>
      </form>

      @if (!empty($settings['store_whatsapp']))
        @php $waText = urlencode(__('Hi, I want to order: ') . $product->name); @endphp
        <a href="https://wa.me/{{ $settings['store_whatsapp'] }}?text={{ $waText }}" class="pp-wa" target="_blank" rel="noopener">
          <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 00-8.6 15L2 22l5.2-1.4A10 10 0 1012 2zm0 18.2a8.2 8.2 0 01-4.2-1.2l-.3-.2-3.1.8.8-3-.2-.3A8.2 8.2 0 1112 20.2zm4.5-6.1c-.2-.1-1.5-.7-1.7-.8s-.4-.1-.6.1-.6.8-.8 1-.3.2-.5.1a6.7 6.7 0 01-3.3-2.9c-.2-.4.2-.4.6-1.2.1-.2 0-.3 0-.5s-.6-1.4-.8-2c-.2-.5-.4-.4-.6-.4h-.5a1 1 0 00-.7.3A3 3 0 006 8.6a5.2 5.2 0 001.1 2.7 11.9 11.9 0 004.6 4 5.3 5.3 0 002.4.5 2.8 2.8 0 001.9-1.3 2.3 2.3 0 00.2-1.3c-.1-.1-.3-.2-.5-.3z"/></svg>
          {{ __('Or order on WhatsApp') }}
        </a>
      @endif

      <ul class="pp-assure">
        <li>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><rect x="2" y="6" width="20" height="13" rx="2"/><circle cx="12" cy="12.5" r="2.6"/></svg>
          <span><b>{{ __('Cash on delivery') }}</b>{{ __('Pay when it reaches you') }}</span>
        </li>
        <li>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M12 2.5l8 3.2v6c0 5-3.4 8.6-8 10-4.6-1.4-8-5-8-10v-6z"/><path d="M8.8 12.2l2.3 2.3 4.3-4.6"/></svg>
          <span><b>{{ __('Batch code intact') }}</b>{{ __('Check it with the brand') }}</span>
        </li>
        <li>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><rect x="1.5" y="6" width="13" height="11" rx="1.5"/><path d="M14.5 10h4l3 3.2V17h-7z"/><circle cx="6" cy="18.5" r="1.8"/><circle cx="17.5" cy="18.5" r="1.8"/></svg>
          <span><b>{{ __('Dhaka in 24 hours') }}</b>{{ __('2–3 days elsewhere') }}</span>
        </li>
        <li>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M3 12a9 9 0 102.6-6.4"/><path d="M3 4v5h5"/></svg>
          <span><b>{{ __(':n day returns', ['n' => $settings['return_days'] ?? 7]) }}</b>{{ __('Unopened items') }}</span>
        </li>
      </ul>
    </div>
  </div>

  {{-- ---------- tabs ---------- --}}
  <div class="wrap pp-tabsec" id="reviews">
    <div class="pp-tabbar" role="tablist">
      <button type="button" class="pp-tab" role="tab" aria-selected="true" data-tab="description">{{ __('About this product') }}</button>
      <button type="button" class="pp-tab" role="tab" aria-selected="false" data-tab="reviews">{{ __('Reviews') }}<span class="n">{{ $ratingCount }}</span></button>
      <button type="button" class="pp-tab" role="tab" aria-selected="false" data-tab="delivery">{{ __('Delivery & returns') }}</button>
    </div>

    <div class="pp-panel on" data-panel="description">
      <div class="pp-prose">
        @if ($product->description)
          {!! nl2br(e($product->description)) !!}
        @else
          <p>{{ $product->short_description ?: __('Full details for this product are coming shortly.') }}</p>
        @endif
      </div>

      <ul class="pp-specs">
        @if ($product->brand)<li><span>{{ __('Brand') }}</span><b>{{ $product->brand->name }}</b></li>@endif
        @if ($product->size_label)<li><span>{{ __('Size') }}</span><b>{{ $product->size_label }}</b></li>@endif
        @if ($product->origin)<li><span>{{ __('Country of origin') }}</span><b>{{ $product->origin }}</b></li>@endif
        @if ($product->category)<li><span>{{ __('Category') }}</span><b>{{ $product->category->name }}</b></li>@endif
        <li><span>{{ __('SKU') }}</span><b>{{ $product->sku }}</b></li>
      </ul>
    </div>

    <div class="pp-panel" data-panel="reviews">
      @if (session('status'))
        <div class="pp-flash">{{ session('status') }}</div>
      @endif

      @if ($errors->any())
        <div class="pp-errors">
          <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
      @endif

      @if ($ratingCount > 0)
        <div class="pp-revtop">
          <div class="pp-score">
            <div class="pp-scorenum">{{ number_format($ratingAvg, 1) }}</div>
            <div class="pp-scoreout">{{ __('out of 5') }}</div>
            @include('site.partials.stars', ['rating' => $ratingAvg])
            <div class="pp-scoreout" style="margin-top:10px">{{ trans_choice(':count review|:count reviews', $ratingCount) }}</div>
          </div>

          <div class="pp-bars">
            @foreach ($breakdown as $starValue => $n)
              <div class="pp-bar">
                <span class="lbl">{{ $starValue }} <svg viewBox="0 0 24 24"><path d="M12 2.6l2.9 5.9 6.5.9-4.7 4.6 1.1 6.5L12 17.4 6.2 20.5l1.1-6.5L2.6 9.4l6.5-.9z"/></svg></span>
                <span class="track"><i class="fill" style="width:{{ $ratingCount ? round($n / $ratingCount * 100) : 0 }}%"></i></span>
                <span class="num">{{ $n }}</span>
              </div>
            @endforeach
          </div>
        </div>
      @endif

      <div class="pp-revactions">
        <h3>{{ $ratingCount > 0 ? __('What customers said') : __('Be the first to review this') }}</h3>
        <button type="button" class="pp-btn pp-btn-gold" id="writeReview">{{ __('Write a review') }}</button>
      </div>

      <form method="POST" action="{{ route('reviews.store', $product) }}" class="pp-form" id="reviewForm" {{ $errors->any() ? '' : 'hidden' }}>
        @csrf
        <h3>{{ __('Write a review') }}</h3>
        <p class="hint">{{ __('Reviews are read before they go up, so give it a day or so.') }}</p>

        <div class="pp-field">
          <label>{{ __('Your rating') }}</label>
          <div class="pp-rate">
            @foreach ([5, 4, 3, 2, 1] as $starValue)
              <input type="radio" name="rating" id="star{{ $starValue }}" value="{{ $starValue }}" @checked(old('rating') == $starValue) required>
              <label for="star{{ $starValue }}" title="{{ $starValue }}">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2.6l2.9 5.9 6.5.9-4.7 4.6 1.1 6.5L12 17.4 6.2 20.5l1.1-6.5L2.6 9.4l6.5-.9z"/></svg>
              </label>
            @endforeach
          </div>
        </div>

        <div class="pp-two">
          <div class="pp-field">
            <label for="reviewer_name">{{ __('Your name') }}</label>
            <input type="text" id="reviewer_name" name="reviewer_name" value="{{ old('reviewer_name', auth()->user()?->name) }}" required>
          </div>
          <div class="pp-field">
            <label for="review_phone">{{ __('Mobile number') }}</label>
            <input type="tel" id="review_phone" name="phone" value="{{ old('phone', auth()->user()?->phone) }}" placeholder="01XXXXXXXXX">
            <small>{{ __('Optional. If you ordered on this number you get a Verified purchase badge.') }}</small>
          </div>
        </div>

        <div class="pp-field">
          <label for="review_title">{{ __('Headline') }}</label>
          <input type="text" id="review_title" name="title" value="{{ old('title') }}" placeholder="{{ __('Sum it up in a few words') }}">
        </div>

        <div class="pp-field">
          <label for="review_body">{{ __('Your review') }}</label>
          <textarea id="review_body" name="body" required placeholder="{{ __('How did you use it, and what happened?') }}">{{ old('body') }}</textarea>
        </div>

        <div class="pp-formactions">
          <button type="submit" class="pp-btn pp-btn-gold">{{ __('Submit review') }}</button>
          <button type="button" class="pp-btn pp-btn-line" id="cancelReview">{{ __('Cancel') }}</button>
        </div>
      </form>

      @if ($reviews->isEmpty())
        <div class="pp-empty">
          <b>{{ __('No reviews yet') }}</b>
          <p>{{ __('Nobody has written about this one. If you have tried it, your review helps the next person decide.') }}</p>
        </div>
      @else
        <div class="pp-revlist">
          @foreach ($reviews as $review)
            <article class="pp-rev">
              <div class="pp-revhead">
                <div class="pp-av">{{ $review->initials }}</div>
                <div class="pp-revwho">
                  <div class="pp-revname">
                    {{ $review->display_name }}
                    @if ($review->is_verified)
                      <span class="pp-verified">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M4 12.5l5 5L20 6.5"/></svg>
                        {{ __('Verified purchase') }}
                      </span>
                    @endif
                  </div>
                  <div class="pp-revwhen">{{ $review->created_at->format('j M Y') }}</div>
                </div>
                <div class="pp-revstars">@include('site.partials.stars', ['rating' => $review->rating])</div>
              </div>

              @if ($review->title)<h4 class="pp-revtitle">{{ $review->title }}</h4>@endif
              <p class="pp-revbody">{{ $review->body }}</p>

              @if ($review->admin_reply)
                <div class="pp-reply">
                  <b>{{ $settings['store_name'] ?? 'AMJR Global' }} {{ __('replied') }}</b>
                  {{ $review->admin_reply }}
                </div>
              @endif
            </article>
          @endforeach
        </div>
      @endif
    </div>

    <div class="pp-panel" data-panel="delivery">
      @php $band = $product->deliveryTier; @endphp

      <ul class="pp-specs" style="max-width:640px;margin-top:0">
        @foreach ($zones as $zone)
          @php $charge = $band?->rateFor($zone->id) ?? $zone->rate; @endphp
          <li>
            <span>{{ $zone->name }}@if ($zone->delivery_time) — {{ $zone->delivery_time }}@endif</span>
            <b>৳{{ number_format($charge) }}</b>
          </li>
        @endforeach
      </ul>

      <div class="pp-prose" style="margin-top:22px">
        @if (($settings['free_delivery_over'] ?? 0) > 0)
          <p>{{ __('Delivery is free anywhere on orders above ৳:n.', ['n' => number_format($settings['free_delivery_over'])]) }}</p>
        @endif
        <p>{{ __('You pay the courier when the parcel arrives — cash or bKash. Nothing is taken up front.') }}</p>
        <p><strong>{{ __('Returns') }}</strong> — {{ __('Unopened items can go back within :n days for a full refund. Call us first so we can arrange the pickup.', ['n' => $settings['return_days'] ?? 7]) }}</p>
      </div>
    </div>
  </div>

  {{-- ---------- related ---------- --}}
  @if ($related->isNotEmpty())
    <div class="wrap pp-related">
      <h2 class="pp-h">{{ __('You might also like') }}</h2>
      <p class="sub">{{ __('Other things people buy alongside this.') }}</p>
      <div class="grid">
        @foreach ($related as $relatedProduct)
          @include('site.partials.product-card', ['product' => $relatedProduct])
        @endforeach
      </div>
    </div>
  @endif

</div>

@endsection

@push('scripts')
<script id="variantData" type="application/json">{!! json_encode($variantData) !!}</script>
<script id="pdpStrings" type="application/json">{!! json_encode($strings) !!}</script>
<script src="{{ asset('js/product-page.js') }}"></script>
<script src="{{ asset('js/cart.js') }}"></script>
@endpush
