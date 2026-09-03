@php $discount = $product->discount_percent; @endphp
<article class="card">
  <a href="{{ route('shop.product', $product) }}" class="shot">
    @if ($product->origin)<span class="tag">{{ $product->origin === 'South Korea' ? __('Korea') : $product->origin }}</span>@endif
    @if ($discount)<span class="tag tag-sale">{{ $discount }}% {{ __('off') }}</span>@endif

    @if ($product->primaryImage)
      <img src="{{ $product->primaryImage->url }}" alt="{{ $product->name }}" loading="lazy">
    @else
      <svg width="62" height="105" viewBox="0 0 88 150" fill="none" aria-hidden="true">
        <rect x="26" y="4" width="36" height="20" rx="3" fill="#14305A" opacity=".2"/>
        <rect x="14" y="24" width="60" height="122" rx="8" fill="#14305A" opacity=".12"/>
        <circle cx="44" cy="100" r="14" fill="#DFA327" opacity=".26"/>
      </svg>
    @endif
  </a>

  <div class="cb">
    @if ($product->brand)
      <div class="bl"><a href="{{ route('shop.brand', $product->brand) }}">{{ $product->brand->name }}</a></div>
    @endif
    <h3><a href="{{ route('shop.product', $product) }}">{{ $product->name }}</a></h3>
    @if ($product->size_label)<div class="sz">{{ $product->size_label }}</div>@endif

    <div class="cf">
      <div class="pr">
        @if ($product->has_variants)<span class="from">{{ __('from') }}</span>@endif
        <span class="price">৳{{ number_format($product->display_price) }}</span>
        @if ($product->compare_price && ! $product->has_variants)
          <span class="was">৳{{ number_format($product->compare_price) }}</span>
        @endif
      </div>

      @if ($product->is_out_of_stock)
        <div class="stock out">{{ __('Out of stock') }}</div>
      @elseif ($product->is_low_stock)
        <div class="stock low">{{ __('Only :n left', ['n' => $product->total_stock]) }}</div>
      @else
        <div class="stock">{{ __('In stock') }}</div>
      @endif

      @if ($product->has_variants || $product->is_out_of_stock)
        <a href="{{ route('shop.product', $product) }}" class="add">
          {{ $product->is_out_of_stock ? __('View details') : __('Choose options') }}
        </a>
      @else
        @include('site.partials.add-to-cart')
      @endif
    </div>
  </div>
</article>
