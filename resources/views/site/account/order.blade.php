@extends('site.layouts.app')
@section('title', $order->order_number . ' — ' . ($settings['store_name'] ?? 'AMJR Global'))
@push('head')<meta name="robots" content="noindex">@endpush

@section('content')
<div class="wrap acctpage">
  @include('site.account._nav')

  <div class="acctmain">
    <div class="accthead">
      <div>
        <a href="{{ route('account.orders') }}" class="backlink">{{ __('Back to orders') }}</a>
        <h1>{{ $order->order_number }}</h1>
      </div>
      <span class="statusbadge {{ in_array($order->status, ['cancelled','returned']) ? 'bad' : '' }}">
        {{ __($order->status_label) }}
      </span>
    </div>

    @php
      $steps = ['pending', 'confirmed', 'processing', 'shipped', 'delivered'];
      $current = array_search($order->status, $steps);
      $dead = in_array($order->status, ['cancelled', 'returned']);
    @endphp

    <div class="cobox">
      @unless ($dead)
        <div class="steps">
          @foreach ($steps as $i => $step)
            <div class="step {{ $current !== false && $i <= $current ? 'done' : '' }}">
              <span class="dot"></span>
              <span class="slabel">{{ __(\App\Models\Order::STATUSES[$step]) }}</span>
            </div>
          @endforeach
        </div>
      @endunless

      @if ($order->courier && $order->tracking_number)
        <div class="warnbox" style="margin-bottom:18px">
          {{ __('Courier') }}: <b>{{ $order->courier }}</b> — {{ __('tracking') }} <b>{{ $order->tracking_number }}</b>
        </div>
      @endif

      <div class="colines">
        @foreach ($order->items as $item)
          <div class="coline">
            <span class="coqty">{{ $item->quantity }}×</span>
            <span class="coname">
              @if ($item->product)
                <a href="{{ route('shop.product', $item->product) }}">{{ $item->name }}</a>
              @else
                {{ $item->name }}
              @endif
              @if ($item->variant_label)<em>{{ $item->variant_label }}</em>@endif
            </span>
            <span class="cotot">৳{{ number_format($item->line_total) }}</span>
          </div>
        @endforeach
      </div>

      <div class="sumline"><span>{{ __('Subtotal') }}</span><b>৳{{ number_format($order->subtotal) }}</b></div>
      <div class="sumline"><span>{{ __('Delivery') }} — {{ $order->shipping_zone_name }}</span>
        <b>{{ $order->delivery_charge > 0 ? '৳' . number_format($order->delivery_charge) : __('Free') }}</b></div>
      @if ($order->discount > 0)
        <div class="sumline"><span>{{ __('Discount') }}</span><b>− ৳{{ number_format($order->discount) }}</b></div>
      @endif
      <div class="sumtotal">
        <span>{{ $order->payment_status === 'paid' ? __('Paid') : __('Pay on delivery') }}</span>
        <b>৳{{ number_format($order->total) }}</b>
      </div>

      <p class="sumnote" style="margin-top:18px">
        <strong>{{ __('Delivering to') }}</strong><br>
        {{ $order->customer_name }} — {{ $order->customer_phone }}<br>
        {{ $order->shipping_address }}@if ($order->shipping_area), {{ $order->shipping_area }}@endif
      </p>
    </div>
  </div>
</div>
@endsection
