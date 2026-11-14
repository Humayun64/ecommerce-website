@extends('site.layouts.app')
@section('title', __('My orders') . ' — ' . ($settings['store_name'] ?? 'AMJR Global'))
@push('head')<meta name="robots" content="noindex">@endpush

@section('content')
<div class="wrap acctpage">
  @include('site.account._nav')

  <div class="acctmain">
    <div class="accthead">
      <h1>{{ __('My orders') }}</h1>
    </div>

    <form method="POST" action="{{ route('account.claim') }}" class="claimbox">
      @csrf
      <label for="claim_order">{{ __('Ordered as a guest before? Enter one of your order numbers to add those orders here.') }}</label>
      <div class="claimrow">
        <input type="text" id="claim_order" name="order_number" required maxlength="32" placeholder="AMJR-000000-0000" autocomplete="off">
        <button type="submit" class="btn btn-gold">{{ __('Add my orders') }}</button>
      </div>
      @error('order_number')<small class="claimerr">{{ $message }}</small>@enderror
    </form>

    @if ($orders->isEmpty())
      <div class="noresults">
        <h2>{{ __('No orders yet') }}</h2>
        <p>{{ __('Ordered as a guest before signing up? Enter one of your order numbers above to add those orders to this account.') }}</p>
        <a href="{{ route('shop') }}" class="btn btn-gold">{{ __('Start shopping') }}</a>
      </div>
    @else
      <div class="ordlist">
        @foreach ($orders as $order)
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

      <div class="pagination">{{ $orders->links('vendor.pagination.site') }}</div>
    @endif
  </div>
</div>
@endsection
