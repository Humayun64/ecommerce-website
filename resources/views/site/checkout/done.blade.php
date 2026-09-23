@extends('site.layouts.app')
@section('title', __('Order confirmed') . ' — ' . ($settings['store_name'] ?? 'AMJR Global'))
@push('head')<meta name="robots" content="noindex">@endpush

@section('content')

<div class="wrap donepage">

  <div class="donehead">
    <div class="donetick">
      <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M4 12.5l5 5L20 6.5"/></svg>
    </div>
    <h1>{{ __('Order placed') }}</h1>
    <p>{{ __('We will call :phone to confirm before the parcel goes out.', ['phone' => $order->customer_phone]) }}</p>
    <div class="ordernum">
      <span>{{ __('Order number') }}</span>
      <strong>{{ $order->order_number }}</strong>
    </div>
    <p class="donehint">{{ __('Keep this number. You can check progress any time on the tracking page.') }}</p>
  </div>

  <div class="cartgrid">
    <div class="cobox">
      <h2>{{ __('What you ordered') }}</h2>
      <div class="colines">
        @foreach ($order->items as $item)
          <div class="coline">
            <span class="coqty">{{ $item->quantity }}×</span>
            <span class="coname">
              {{ $item->name }}
              @if ($item->variant_label)<em>{{ $item->variant_label }}</em>@endif
            </span>
            <span class="cotot">৳{{ number_format($item->line_total) }}</span>
          </div>
        @endforeach
      </div>

      <div class="sumline"><span>{{ __('Subtotal') }}</span><b>৳{{ number_format($order->subtotal) }}</b></div>

      @if ($order->discount > 0)
        <div class="sumline">
          <span>{{ __('Coupon') }}@if ($order->coupon_code) {{ $order->coupon_code }}@endif</span>
          <b style="color:var(--ok)">− ৳{{ number_format($order->discount) }}</b>
        </div>
      @endif

      <div class="sumline"><span>{{ __('Delivery') }} — {{ $order->shipping_zone_name }}</span>
        <b>{{ $order->delivery_charge > 0 ? '৳' . number_format($order->delivery_charge) : __('Free') }}</b></div>
      <div class="sumtotal"><span>{{ __('Pay on delivery') }}</span><b>৳{{ number_format($order->total) }}</b></div>
    </div>

    <aside class="cartsum">
      <div class="sumbox">
        <h2>{{ __('Delivering to') }}</h2>
        <p class="addrblock">
          <b>{{ $order->customer_name }}</b><br>
          {{ $order->shipping_address }}<br>
          @if ($order->shipping_area){{ $order->shipping_area }}<br>@endif
          {{ $order->customer_phone }}
        </p>

        <div class="sumline"><span>{{ __('Payment') }}</span><b>{{ __('Cash on delivery') }}</b></div>
        <div class="sumline"><span>{{ __('Status') }}</span><b>{{ __($order->status_label) }}</b></div>

        <a href="{{ route('orders.track') }}" class="btn btn-line" style="width:100%;margin-top:14px">{{ __('Track this order') }}</a>
        <a href="{{ route('shop') }}" class="keepshopping">{{ __('Keep shopping') }}</a>
      </div>
    </aside>
  </div>

</div>

@endsection
