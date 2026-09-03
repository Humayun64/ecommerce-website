@extends('site.layouts.app')

@section('title', $heading . ' — ' . ($settings['store_name'] ?? 'AMJR Global'))

@section('content')

<div class="crumbs"><div class="wrap">
  <a href="{{ route('home') }}">{{ __('Home') }}</a>
  <span>/</span>
  @if ($category?->parent)
    <a href="{{ route('shop.category', $category->parent) }}">{{ $category->parent->name }}</a><span>/</span>
  @endif
  <strong>{{ $heading }}</strong>
</div></div>

<div class="wrap listing">

  <aside class="filters-col">
    <form method="GET" id="filterForm" action="{{ url()->current() }}">
      @if (request('q'))<input type="hidden" name="q" value="{{ request('q') }}">@endif

      <div class="fblock">
        <h3>{{ __('Price') }}</h3>
        <div class="prange">
          <input type="number" name="min" min="0" value="{{ request('min') }}" placeholder="{{ __('Min') }}">
          <span>–</span>
          <input type="number" name="max" min="0" value="{{ request('max') }}" placeholder="{{ __('Max') }}">
        </div>
      </div>

      @unless ($brand)
      <div class="fblock">
        <h3>{{ __('Brand') }}</h3>
        @foreach ($allBrands as $b)
          <label class="fcheck">
            <input type="checkbox" name="brands[]" value="{{ $b->id }}"
                   @checked(in_array($b->id, (array) request('brands', [])))>
            <span>{{ $b->name }}</span>
          </label>
        @endforeach
      </div>
      @endunless

      @foreach ($attributes as $attribute)
        @continue($attribute->values->isEmpty())
        <div class="fblock">
          <h3>{{ $attribute->name }}</h3>
          @foreach ($attribute->values as $value)
            <label class="fcheck">
              <input type="checkbox" name="values[]" value="{{ $value->id }}"
                     @checked(in_array($value->id, (array) request('values', [])))>
              <span>{{ $value->value }}</span>
            </label>
          @endforeach
        </div>
      @endforeach

      <div class="fblock">
        <label class="fcheck">
          <input type="checkbox" name="in_stock" value="1" @checked(request()->boolean('in_stock'))>
          <span>{{ __('In stock only') }}</span>
        </label>
      </div>

      <button type="submit" class="btn btn-navy" style="width:100%">{{ __('Apply filters') }}</button>
      @if (request()->hasAny(['brands', 'values', 'min', 'max', 'in_stock']))
        <a href="{{ url()->current() }}{{ request('q') ? '?q=' . urlencode(request('q')) : '' }}"
           class="clearlink">{{ __('Clear all filters') }}</a>
      @endif
    </form>
  </aside>

  <div class="results">
    <div class="rbar">
      <div>
        <h1>{{ $heading }}</h1>
        <p class="sub">
          @if (request('q'))
            {{ __(':n results for ":term"', ['n' => $products->total(), 'term' => request('q')]) }}
          @else
            {{ __(':n products', ['n' => $products->total()]) }}
          @endif
        </p>
      </div>

      <form method="GET" class="sortform">
        @foreach (request()->except(['sort', 'page']) as $key => $value)
          @if (is_array($value))
            @foreach ($value as $item)<input type="hidden" name="{{ $key }}[]" value="{{ $item }}">@endforeach
          @else
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
          @endif
        @endforeach
        <select name="sort" onchange="this.form.submit()" aria-label="{{ __('Sort by') }}">
          <option value="">{{ __('Newest first') }}</option>
          <option value="price_asc" @selected(request('sort') === 'price_asc')>{{ __('Price: low to high') }}</option>
          <option value="price_desc" @selected(request('sort') === 'price_desc')>{{ __('Price: high to low') }}</option>
          <option value="name" @selected(request('sort') === 'name')>{{ __('Name A–Z') }}</option>
        </select>
      </form>
    </div>

    @if ($products->isEmpty())
      <div class="noresults">
        <h2>{{ __('Nothing matched') }}</h2>
        <p>{{ __('Try widening the price range or clearing a filter.') }}</p>
        <a href="{{ route('shop') }}" class="btn btn-gold">{{ __('Browse everything') }}</a>
      </div>
    @else
      <div class="grid">
        @foreach ($products as $product)
          @include('site.partials.product-card')
        @endforeach
      </div>

      <div class="pagination">{{ $products->onEachSide(1)->links('vendor.pagination.site') }}</div>
    @endif
  </div>

</div>

@endsection
