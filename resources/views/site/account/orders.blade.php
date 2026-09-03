@extends('site.layouts.app')
@section('title', __('My orders') . ' — ' . ($settings['store_name'] ?? 'AMJR Global'))
@push('head')<meta name="robots" content="noindex">@endpush

@section('content')
<div class="wrap acctpage">
  @include('site.account._nav')

  <div class="acctmain">
    <div class="accthead">
      <h1>{{ __('My orders') }}</h1>
      @if ($unclaimed > 0)
        <form method="POST" action="{{ route('account.claim') }}">
          @csrf
          <button type="submit" class="btn btn-gold">
            {{ __('Add :n guest order(s)', ['n' => $unclaimed]) }}
          </button>
        </form>
      @endif
    </div>

    @if ($orders->isEmpty())
      <div class="noresults">
        <h2>{{ __('No orders yet') }}</h2>
        <p>{{ __('Ordered as a guest before signing up? Use the button above to link those orders to this account using your phone number.') }}</p>
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
