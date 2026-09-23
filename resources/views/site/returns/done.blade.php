@extends('site.layouts.app')

@section('title', __('Return request sent') . ' — ' . ($settings['store_name'] ?? 'AMJR Global'))

@push('head')<link rel="stylesheet" href="{{ asset('css/returns.css') }}"><meta name="robots" content="noindex">@endpush

@section('content')
<div class="rt-wrap">

  <div class="rt-head">
    <h1>{{ __('We have your request') }}</h1>
    <p>{{ __('Return #:id, opened :date.', ['id' => $row->id, 'date' => $row->created_at->format('j F Y')]) }}</p>
  </div>

  <div class="rt-card">
    <div class="rt-row" style="border:0;padding:0;box-shadow:none">
      <div>
        <div class="t">{{ $row->product_name }}@if ($row->quantity > 1) × {{ $row->quantity }}@endif</div>
        <div class="m">
          {{ $row->reason_label }}<br>
          {{ __('Order') }} #{{ $row->order?->order_number }}
        </div>
      </div>
      <div class="r">
        <span class="rt-pill rt-{{ $row->status }}">{{ $row->status_label }}</span>
        <b>৳{{ number_format($row->refund_amount) }}</b>
      </div>
    </div>
  </div>

  <div class="rt-card">
    <h2>{{ __('What happens next') }}</h2>

    <div class="rt-steps">
      <div class="rt-step">
        <div>
          <b>{{ __('We look at it') }}</b>
          <span>{{ __('Within two working days. If you sent a photo it is usually quicker than that.') }}</span>
        </div>
      </div>
      <div class="rt-step">
        <div>
          <b>{{ __('We call you') }}</b>
          <span>{{ __('On :phone, to arrange the pickup or tell you where to send it.', ['phone' => $row->customer_phone]) }}</span>
        </div>
      </div>
      <div class="rt-step">
        <div>
          <b>{{ __('The money goes back') }}</b>
          <span>{{ __('Once the item reaches us and matches what you described. Cash on delivery orders are refunded by bKash or Nagad to the number above.') }}</span>
        </div>
      </div>
    </div>
  </div>

  <div class="rt-actions">
    @auth
      <a href="{{ route('returns.mine') }}" class="btn btn-navy">{{ __('My returns') }}</a>
    @else
      <a href="{{ route('orders.track') }}" class="btn btn-navy">{{ __('Track this order') }}</a>
    @endauth
    <a href="{{ route('shop') }}" class="btn btn-line">{{ __('Keep shopping') }}</a>
  </div>

</div>
@endsection
