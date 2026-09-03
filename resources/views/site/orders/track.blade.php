@extends('site.layouts.app')
@section('title', __('Track your order') . ' — ' . ($settings['store_name'] ?? 'AMJR Global'))

@section('content')

<div class="crumbs"><div class="wrap">
  <a href="{{ route('home') }}">{{ __('Home') }}</a><span>/</span>
  <strong>{{ __('Track your order') }}</strong>
</div></div>

<div class="wrap trackpage">

  <div class="trackbox">
    <h1>{{ __('Track your order') }}</h1>
    <p class="sub">{{ __('Enter the order number from your confirmation, plus the mobile number you ordered with.') }}</p>

    <form method="POST" action="{{ route('orders.track.submit') }}">
      @csrf
      <div class="corow">
        <div class="cofield">
          <label for="order_number">{{ __('Order number') }}</label>
          <input type="text" id="order_number" name="order_number" required
                 placeholder="AMJR-260903-1234" value="{{ old('order_number') }}">
        </div>
        <div class="cofield">
          <label for="phone">{{ __('Mobile number') }}</label>
          <input type="tel" id="phone" name="phone" required placeholder="01XXXXXXXXX" value="{{ old('phone') }}">
        </div>
      </div>
      <button type="submit" class="btn btn-gold">{{ __('Find my order') }}</button>
    </form>
  </div>

  @if ($searched && ! $order)
    <div class="warnbox bad" style="margin-top:18px">
      {{ __('No order matched that number and phone. Check both and try again.') }}
    </div>
  @endif

  @if ($order)
    @php
      $steps = ['pending', 'confirmed', 'processing', 'shipped', 'delivered'];
      $current = array_search($order->status, $steps);
      $cancelled = in_array($order->status, ['cancelled', 'returned']);
    @endphp

    <div class="cobox" style="margin-top:18px">
      <div class="trackhead">
        <div>
          <div class="sub">{{ __('Order') }}</div>
          <h2>{{ $order->order_number }}</h2>
        </div>
        <div class="statusbadge {{ $cancelled ? 'bad' : '' }}">{{ __($order->status_label) }}</div>
      </div>

      @unless ($cancelled)
        <div class="steps">
          @foreach ($steps as $i => $step)
            <div class="step {{ $current !== false && $i <= $current ? 'done' : '' }}">
              <span class="dot"></span>
              <span class="slabel">{{ __(\App\Models\Order::STATUSES[$step]) }}</span>
            </div>
          @endforeach
        </div>
      @endunless

      @if ($order->tracking_number)
        <div class="warnbox" style="margin-bottom:16px">
          {{ __('Courier') }}: <b>{{ $order->courier }}</b> — {{ __('tracking') }} <b>{{ $order->tracking_number }}</b>
        </div>
      @endif

      <div class="colines">
        @foreach ($order->items as $item)
          <div class="coline">
            <span class="coqty">{{ $item->quantity }}×</span>
            <span class="coname">{{ $item->name }}@if ($item->variant_label)<em>{{ $item->variant_label }}</em>@endif</span>
            <span class="cotot">৳{{ number_format($item->line_total) }}</span>
          </div>
        @endforeach
      </div>

      <div class="sumline"><span>{{ __('Subtotal') }}</span><b>৳{{ number_format($order->subtotal) }}</b></div>
      <div class="sumline"><span>{{ __('Delivery') }}</span><b>{{ $order->delivery_charge > 0 ? '৳' . number_format($order->delivery_charge) : __('Free') }}</b></div>
      <div class="sumtotal"><span>{{ __('Total') }}</span><b>৳{{ number_format($order->total) }}</b></div>

      <p class="sumnote" style="margin-top:16px">
        {{ __('Placed :date', ['date' => $order->created_at->format('j M Y, g:ia')]) }}
        @if (!empty($settings['store_phone']))
          — {{ __('any question, call :phone', ['phone' => $settings['store_phone']]) }}
        @endif
      </p>
    </div>
  @endif

</div>

@endsection
