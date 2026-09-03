@extends('site.layouts.app')
@section('title', __('My account') . ' — ' . ($settings['store_name'] ?? 'AMJR Global'))
@push('head')<meta name="robots" content="noindex">@endpush

@section('content')
<div class="wrap acctpage">
  @include('site.account._nav')

  <div class="acctmain">
    <div class="accthead">
      <div>
        <h1>{{ __('Hello, :name', ['name' => explode(' ', auth()->user()->name)[0]]) }}</h1>
        <p class="sub" style="margin:5px 0 0">{{ __('Here is where your orders stand.') }}</p>
      </div>
      <a href="{{ route('shop') }}" class="btn btn-gold">{{ __('Shop now') }}</a>
    </div>

    @if ($unclaimed > 0)
      <div class="warnbox" style="display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap">
        <span>{{ __('We found :n order(s) placed as a guest on your number.', ['n' => $unclaimed]) }}</span>
        <form method="POST" action="{{ route('account.claim') }}">
          @csrf
          <button type="submit" class="btn btn-navy btn-sm">{{ __('Add them to my account') }}</button>
        </form>
      </div>
    @endif

    <div class="acctstats">
      <div class="astat">
        <div class="alab">{{ __('Orders placed') }}</div>
        <div class="anum">{{ $orderCount }}</div>
      </div>
      <div class="astat">
        <div class="alab">{{ __('Total spent') }}</div>
        <div class="anum">৳{{ number_format($spent) }}</div>
      </div>
      <div class="astat">
        <div class="alab">{{ __('On the way') }}</div>
        <div class="anum">{{ $inProgress }}</div>
      </div>
    </div>

    <div class="acctcols">
      <div class="cobox">
        <h2>{{ __('Recent orders') }}</h2>

        @if ($recent->isEmpty())
          <p class="sub" style="margin:0 0 16px">{{ __('Nothing yet. Your first order will show up here.') }}</p>
          <a href="{{ route('shop') }}" class="btn btn-line">{{ __('Browse products') }}</a>
        @else
          <div class="ordlist">
            @foreach ($recent as $order)
              <a href="{{ route('account.order', $order) }}" class="ordcard">
                <div class="ordtop">
                  <div>
                    <b>{{ $order->order_number }}</b>
                    <small>{{ $order->created_at->format('j M Y') }} · {{ $order->items_count }} {{ trans_choice('item|items', $order->items_count) }}</small>
                  </div>
                  <span class="statusbadge {{ in_array($order->status, ['cancelled','returned']) ? 'bad' : '' }}">
                    {{ __($order->status_label) }}
                  </span>
                </div>
                <div class="ordbot">
                  <span>{{ $order->shipping_zone_name }}</span>
                  <b>৳{{ number_format($order->total) }}</b>
                </div>
              </a>
            @endforeach
          </div>
          <a href="{{ route('account.orders') }}" class="keepshopping">{{ __('See all orders') }}</a>
        @endif
      </div>

      <div class="cobox">
        <h2>{{ __('Default address') }}</h2>

        @if ($address)
          <p class="sub" style="line-height:1.7;margin:0 0 16px">
            <strong style="color:var(--ink)">{{ $address->name }}</strong><br>
            {{ $address->address }}@if ($address->area)<br>{{ $address->area }}@endif<br>
            {{ $address->phone }}
            @if ($address->zone)<br><em>{{ $address->zone->name }}</em>@endif
          </p>
          <a href="{{ route('account.addresses') }}" class="btn btn-line">{{ __('Manage addresses') }}</a>
        @else
          <p class="sub" style="margin:0 0 16px">
            {{ __('Save an address once and checkout fills itself in next time.') }}
          </p>
          <a href="{{ route('account.addresses') }}" class="btn btn-gold">{{ __('Add an address') }}</a>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection
