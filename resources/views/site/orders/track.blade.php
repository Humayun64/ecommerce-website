@extends('site.layouts.app')
@section('title', __('Track your order') . ' — ' . ($settings['store_name'] ?? 'AMJR Global'))

@section('content')

<style>
.trk-ret{
  margin-top:18px;padding-top:18px;border-top:1px solid var(--line);
  display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;
}
.trk-ret b{display:block;font-size:14.5px}
.trk-ret span{color:var(--ink-mute);font-size:13px}
.trk-rets{margin-top:18px;padding-top:18px;border-top:1px solid var(--line)}
.trk-rets h3{font-size:13px;color:var(--ink-mute);font-weight:700;letter-spacing:.4px;text-transform:uppercase;margin:0 0 12px}
.trk-ret1{
  display:flex;align-items:flex-start;justify-content:space-between;gap:14px;
  padding:11px 0;border-bottom:1px solid var(--line);
}
.trk-ret1:last-child{border-bottom:0}
.trk-ret1 b{display:block;font-size:14px}
.trk-ret1 span{color:var(--ink-mute);font-size:12.5px}
.trk-ret1 .amt{text-align:right;white-space:nowrap}
.trk-ret1 .amt b{
  font-family:var(--display);font-variation-settings:"wdth" 110;
  font-weight:700;color:var(--navy);margin-top:6px;
}
.trk-pill{display:inline-block;font-size:11px;font-weight:700;border-radius:999px;padding:3px 10px}
.trk-pending{background:#FDF3DC;color:#8A6410}
.trk-approved{background:#E4F3EA;color:#14663E}
.trk-rejected{background:#FBE7E4;color:#9E3423}
.trk-refunded{background:#E8EDF6;color:var(--navy)}
</style>


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

      @if ($order->discount > 0)
        <div class="sumline">
          <span>{{ __('Coupon') }}@if ($order->coupon_code) {{ $order->coupon_code }}@endif</span>
          <b style="color:var(--ok)">− ৳{{ number_format($order->discount) }}</b>
        </div>
      @endif

      <div class="sumline"><span>{{ __('Delivery') }}</span><b>{{ $order->delivery_charge > 0 ? '৳' . number_format($order->delivery_charge) : __('Free') }}</b></div>
      <div class="sumtotal"><span>{{ __('Total') }}</span><b>৳{{ number_format($order->total) }}</b></div>

      <p class="sumnote" style="margin-top:16px">
        {{ __('Placed :date', ['date' => $order->created_at->format('j M Y, g:ia')]) }}
        @if (!empty($settings['store_phone']))
          — {{ __('any question, call :phone', ['phone' => $settings['store_phone']]) }}
        @endif
      </p>

      @if ($canReturn)
        <div class="trk-ret">
          <div>
            <b>{{ __('Something not right?') }}</b>
            <span>{{ __('You can send an item back from here — no account needed.') }}</span>
          </div>
          <a href="{{ route('returns.create', $order) }}" class="btn btn-line btn-sm">{{ __('Request a return') }}</a>
        </div>
      @endif

      @if ($returns->isNotEmpty())
        <div class="trk-rets">
          <h3>{{ __('Your returns on this order') }}</h3>
          @foreach ($returns as $ret)
            <div class="trk-ret1">
              <div>
                <b>{{ $ret->product_name }}@if ($ret->quantity > 1) × {{ $ret->quantity }}@endif</b>
                <span>{{ $ret->reason_label }} · {{ __('opened') }} {{ $ret->created_at->format('j M Y') }}</span>
              </div>
              <div class="amt">
                <span class="trk-pill trk-{{ $ret->status }}">{{ __($ret->status_label) }}</span>
                <b>৳{{ number_format($ret->refund_amount) }}</b>
              </div>
            </div>
          @endforeach
        </div>
      @endif
    </div>
  @endif

</div>

@endsection
