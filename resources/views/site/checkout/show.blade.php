@extends('site.layouts.app')
@section('title', __('Checkout') . ' — ' . ($settings['store_name'] ?? 'AMJR Global'))
@push('head')<meta name="robots" content="noindex">@endpush

@section('content')

<div class="crumbs"><div class="wrap">
  <a href="{{ route('cart.index') }}">{{ __('Cart') }}</a><span>/</span>
  <strong>{{ __('Checkout') }}</strong>
</div></div>

<div class="wrap cartpage">

  @if ($errors->any())
    <div class="warnbox bad">
      <ul style="margin:0;padding-left:18px">
        @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('checkout.store') }}" id="checkoutForm">
    @csrf
    <div class="cartgrid">

      <div class="cobox">
        <h2>{{ __('Delivery details') }}</h2>

        <div class="cofield">
          <label for="customer_name">{{ __('Full name') }} <span class="req">*</span></label>
          <input type="text" id="customer_name" name="customer_name" required
                 value="{{ old('customer_name', $address->name ?? auth()->user()?->name) }}">
        </div>

        <div class="corow">
          <div class="cofield">
            <label for="customer_phone">{{ __('Mobile number') }} <span class="req">*</span></label>
            <input type="tel" id="customer_phone" name="customer_phone" required placeholder="01XXXXXXXXX"
                   value="{{ old('customer_phone', $address->phone ?? auth()->user()?->phone) }}">
            <small>{{ __('We call this number before delivery.') }}</small>
          </div>
          <div class="cofield">
            <label for="customer_email">{{ __('Email') }} <small>({{ __('optional') }})</small></label>
            <input type="email" id="customer_email" name="customer_email"
                   value="{{ old('customer_email', auth()->user()?->email) }}">
          </div>
        </div>

        <div class="cofield">
          <label for="shipping_address">{{ __('Full address') }} <span class="req">*</span></label>
          <textarea id="shipping_address" name="shipping_address" required
                    placeholder="{{ __('House, road, block, area, and any landmark') }}">{{ old('shipping_address', $address->address ?? '') }}</textarea>
        </div>

        <div class="cofield">
          <label for="shipping_area">{{ __('Area or thana') }}</label>
          <input type="text" id="shipping_area" name="shipping_area"
                 value="{{ old('shipping_area', $address->area ?? '') }}" placeholder="{{ __('Uttara, Mirpur, Gulshan…') }}">
        </div>

        <div class="cofield">
          <label>{{ __('Delivery area') }} <span class="req">*</span></label>
          <div class="zonelist">
            @foreach ($zones as $zone)
              <label class="zone-opt">
                <input type="radio" name="shipping_zone_id" value="{{ $zone->id }}"
                       data-rate="{{ $zone->rate }}"
                       @checked(old('shipping_zone_id', $address->shipping_zone_id ?? null) == $zone->id)>
                <span class="zone-body">
                  <b>{{ $zone->name }}</b>
                  <small>{{ $zone->delivery_time }}</small>
                </span>
                <span class="zone-rate">৳{{ number_format($zone->rate) }}</span>
              </label>
            @endforeach
          </div>
        </div>

        <div class="cofield">
          <label for="customer_note">{{ __('Note for us') }} <small>({{ __('optional') }})</small></label>
          <textarea id="customer_note" name="customer_note" style="min-height:70px"
                    placeholder="{{ __('Delivery timing, gift wrapping, anything else') }}">{{ old('customer_note') }}</textarea>
        </div>

        @auth
          <label class="fcheck" style="margin-top:4px">
            <input type="checkbox" name="save_address" value="1" checked>
            <span>{{ __('Save this address for next time') }}</span>
          </label>
        @endauth

        <h2 style="margin-top:28px">{{ __('Payment') }}</h2>

        <label class="zone-opt selected">
          <input type="radio" name="payment_method" value="cod" checked>
          <span class="zone-body">
            <b>{{ __('Cash on delivery') }}</b>
            <small>{{ __('Pay the courier in cash or bKash when the parcel arrives.') }}</small>
          </span>
        </label>

        <label class="zone-opt disabled">
          <input type="radio" disabled>
          <span class="zone-body">
            <b>{{ __('Pay online') }}</b>
            <small>{{ __('bKash, Nagad and cards — coming soon.') }}</small>
          </span>
        </label>
      </div>

      <aside class="cartsum">
        <div class="sumbox">
          <h2>{{ __('Your order') }}</h2>

          <div class="colines">
            @foreach ($cart->items as $item)
              <div class="coline">
                <span class="coqty">{{ $item->quantity }}×</span>
                <span class="coname">
                  {{ $item->product->name }}
                  @if ($item->label)<em>{{ $item->label }}</em>@endif
                </span>
                <span class="cotot">৳{{ number_format($item->line_total) }}</span>
              </div>
            @endforeach
          </div>

          @php
            $freeOver = (float) ($settings['free_delivery_over'] ?? 2000);
            $qualifies = $cart->subtotal >= $freeOver;
          @endphp

          <div class="sumline">
            <span>{{ __('Subtotal') }}</span>
            <b>৳{{ number_format($cart->subtotal) }}</b>
          </div>
          <div class="sumline">
            <span>{{ __('Delivery') }}</span>
            <b id="deliveryCell">{{ $qualifies ? __('Free') : __('Choose an area') }}</b>
          </div>

          <div class="sumtotal">
            <span>{{ __('Total to pay') }}</span>
            <b id="totalCell">৳{{ number_format($cart->subtotal) }}</b>
          </div>

          <p class="sumnote">
            {{ __('You pay nothing now. The courier collects :total when the parcel reaches you.', ['total' => '৳' . number_format($cart->subtotal)]) }}
          </p>

          <button type="submit" class="btn btn-gold" style="width:100%" id="placeOrder">
            {{ __('Place order') }}
          </button>

          <a href="{{ route('cart.index') }}" class="keepshopping">{{ __('Back to cart') }}</a>
        </div>
      </aside>

    </div>
  </form>
</div>

@endsection

@push('scripts')
<script id="checkoutData" type="application/json">@json([
  'subtotal' => (float) $cart->subtotal,
  'freeOver' => (float) ($settings['free_delivery_over'] ?? 2000),
  'free'     => __('Free'),
  'choose'   => __('Choose an area'),
])</script>
<script src="{{ asset('js/checkout.js') }}"></script>
@endpush
