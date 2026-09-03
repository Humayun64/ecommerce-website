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
@endpush

@section('content')

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

<div class="wrap pdp">

  {{-- gallery --}}
  <div class="gallery">
    <div class="gmain" id="galleryMain">
      @if ($product->images->isNotEmpty())
        <img src="{{ $product->images->first()->url }}" alt="{{ $product->name }}" id="galleryImage">
      @else
        <svg width="150" height="255" viewBox="0 0 88 150" fill="none" aria-hidden="true">
          <rect x="26" y="4" width="36" height="20" rx="3" fill="#14305A" opacity=".2"/>
          <rect x="14" y="24" width="60" height="122" rx="8" fill="#14305A" opacity=".12"/>
          <circle cx="44" cy="100" r="14" fill="#DFA327" opacity=".26"/>
        </svg>
      @endif
    </div>

    @if ($product->images->count() > 1)
      <div class="gthumbs">
        @foreach ($product->images as $i => $image)
          <button type="button" class="gthumb {{ $i === 0 ? 'on' : '' }}" data-src="{{ $image->url }}">
            <img src="{{ $image->url }}" alt="" loading="lazy">
          </button>
        @endforeach
      </div>
    @endif
  </div>

  {{-- buy box --}}
  <div class="buybox">
    @if ($product->brand)
      <a href="{{ route('shop.brand', $product->brand) }}" class="pbrand">{{ $product->brand->name }}</a>
    @endif

    <h1>{{ $product->name }}</h1>

    <div class="pmeta">
      @if ($product->origin)
        <span class="ptag">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 21s7-6.2 7-11a7 7 0 10-14 0c0 4.8 7 11 7 11z"/><circle cx="12" cy="10" r="2.4"/></svg>
          {{ $product->origin }}
        </span>
      @endif
      <span class="psku">{{ __('SKU') }}: <b id="skuLabel">{{ $product->sku }}</b></span>
    </div>

    @if ($product->short_description)
      <p class="pshort">{{ $product->short_description }}</p>
    @endif

    <div class="pprice">
      <span class="price" id="priceMain">৳{{ number_format($product->display_price) }}</span>
      <span class="was" id="priceWas" style="{{ $product->compare_price && ! $product->has_variants ? '' : 'display:none' }}">
        ৳{{ number_format($product->compare_price ?? 0) }}
      </span>
      <span class="save" id="priceSave" style="{{ $product->discount_percent && ! $product->has_variants ? '' : 'display:none' }}">
        {{ __('Save') }} {{ $product->discount_percent }}%
      </span>
    </div>

    @if ($product->has_variants)
      <div class="axes-pick">
        @foreach ($product->productAttributes as $attribute)
          @php
            $used = $product->variants->flatMap->values
                        ->where('attribute_id', $attribute->id)->unique('id')->sortBy('sort_order');
          @endphp
          @continue($used->isEmpty())
          <div class="axis-pick" data-axis="{{ $attribute->id }}">
            <div class="axis-label">{{ $attribute->name }}</div>
            <div class="axis-opts">
              @foreach ($used as $value)
                <button type="button" class="opt" data-axis="{{ $attribute->id }}" data-value="{{ $value->id }}">
                  {{ $value->value }}
                </button>
              @endforeach
            </div>
          </div>
        @endforeach
      </div>
    @endif

    <div class="pstock" id="stockLine">
      @if ($product->is_out_of_stock)
        <span class="out">{{ __('Out of stock') }}</span>
      @elseif ($product->is_low_stock)
        <span class="low">{{ __('Only :n left', ['n' => $product->total_stock]) }}</span>
      @elseif ($product->has_variants)
        <span class="muted">{{ __('Choose an option to see stock') }}</span>
      @else
        <span class="ok">{{ __('In stock') }}</span>
      @endif
    </div>

    <form method="POST" action="{{ route('cart.store') }}" id="addForm">
      @csrf
      <input type="hidden" name="product_id" value="{{ $product->id }}">
      <input type="hidden" name="variant_id" id="variantId" value="">

      <div class="pbuy">
        <div class="qty">
          <button type="button" id="qtyDown" aria-label="{{ __('Decrease') }}">−</button>
          <input type="number" name="quantity" id="qty" value="1" min="1" aria-label="{{ __('Quantity') }}">
          <button type="button" id="qtyUp" aria-label="{{ __('Increase') }}">+</button>
        </div>
        <button type="submit" class="btn btn-gold buybtn" id="addToCart"
                @disabled($product->is_out_of_stock || $product->has_variants)>
          {{ $product->is_out_of_stock ? __('Sold out') : __('Add to cart') }}
        </button>
      </div>
    </form>

    @if (!empty($settings['store_whatsapp']))
      <a href="https://wa.me/{{ $settings['store_whatsapp'] }}?text={{ urlencode(__('Hi, I want to order: ') . $product->name) }}"
         class="btn btn-line wabtn" target="_blank" rel="noopener">
        {{ __('Or order on WhatsApp') }}
      </a>
    @endif

    <ul class="passure">
      <li>{{ __('Cash on delivery available') }}</li>
      <li>{{ __('Dhaka in 24 hours, two to three days elsewhere') }}</li>
      <li>{{ __(':n day returns on unopened items', ['n' => $settings['return_days'] ?? 7]) }}</li>
      <li>{{ __('Sealed with the batch code intact') }}</li>
    </ul>
  </div>
</div>

@if ($product->description)
  <div class="wrap">
    <div class="pdesc">
      <h2>{{ __('About this product') }}</h2>
      <div class="prose">{!! nl2br(e($product->description)) !!}</div>
    </div>
  </div>
@endif

@if ($related->isNotEmpty())
  <section class="tight">
    <div class="wrap">
      <div class="sh"><div><h2>{{ __('You might also like') }}</h2></div></div>
      <div class="grid">
        @foreach ($related as $relatedProduct)
          @include('site.partials.product-card', ['product' => $relatedProduct])
        @endforeach
      </div>
    </div>
  </section>
@endif

@endsection

@push('scripts')
<script id="variantData" type="application/json">@json($product->variants->map(fn ($v) => [
    'id'      => $v->id,
    'sku'     => $v->sku,
    'price'   => (float) $v->price,
    'compare' => $v->compare_price ? (float) $v->compare_price : null,
    'stock'   => (int) $v->stock,
    'values'  => $v->values->pluck('id')->sort()->values(),
]))</script>
<script id="pdpStrings" type="application/json">@json([
    'inStock'   => __('In stock'),
    'lowStock'  => __('Only :n left', ['n' => ':n']),
    'outStock'  => __('Out of stock'),
    'choose'    => __('Choose an option to see stock'),
    'save'      => __('Save'),
    'soldOut'   => __('Sold out'),
    'addToCart' => __('Add to cart'),
])</script>
<script src="{{ asset('js/product-page.js') }}"></script>
<script src="{{ asset('js/cart.js') }}"></script>
@endpush

@push('head')
<script type="application/ld+json">@json([
    '@context'    => 'https://schema.org',
    '@type'       => 'Product',
    'name'        => $product->name,
    'description' => $product->seo_description,
    'sku'         => $product->sku,
    'brand'       => $product->brand ? ['@type' => 'Brand', 'name' => $product->brand->name] : null,
    'image'       => $product->primaryImage ? url($product->primaryImage->url) : null,
    'offers'      => [
        '@type'         => 'Offer',
        'price'         => (float) $product->display_price,
        'priceCurrency' => 'BDT',
        'availability'  => $product->is_out_of_stock
                            ? 'https://schema.org/OutOfStock'
                            : 'https://schema.org/InStock',
        'url'           => route('shop.product', $product),
    ],
], JSON_UNESCAPED_SLASHES)</script>
@endpush
