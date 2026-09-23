@extends('site.layouts.app')

@section('title', __('My returns') . ' — ' . ($settings['store_name'] ?? 'AMJR Global'))

@push('head')<link rel="stylesheet" href="{{ asset('css/returns.css') }}"><meta name="robots" content="noindex">@endpush

@section('content')
<div class="rt-wrap rt-wide">

  <div class="rt-crumb"><a href="{{ route('account.dashboard') }}">{{ __('My account') }}</a> · {{ __('Returns') }}</div>

  <div class="rt-head">
    <h1>{{ __('My returns') }}</h1>
    <p>{{ __('Everything you have sent back, and where each one has got to.') }}</p>
  </div>

  @if ($rows->isEmpty())

    <div class="rt-empty">
      <b>{{ __('Nothing sent back yet') }}</b>
      <p>{{ __('If something arrives damaged, wrong or not as described, open a return from the order and we will sort it out.') }}</p>
      <a href="{{ route('account.orders') }}" class="btn btn-gold">{{ __('My orders') }}</a>
    </div>

  @else

    <div class="rt-list">
      @foreach ($rows as $row)
        <div class="rt-row">
          <div>
            <div class="t">{{ $row->product_name }}@if ($row->quantity > 1) × {{ $row->quantity }}@endif</div>
            <div class="m">
              {{ $row->reason_label }}
              @if ($row->order) · {{ __('Order') }} #{{ $row->order->order_number }} @endif
              · {{ __('Opened') }} {{ $row->created_at->format('j M Y') }}

              @if ($row->status === 'pending')
                <br>{{ __('We are looking at this now.') }}
              @elseif ($row->status === 'approved')
                <br>{{ __('Approved — we will call you about sending it back.') }}
              @elseif ($row->status === 'rejected')
                <br>{{ __('Not approved.') }}
                @if ($row->admin_note) {{ $row->admin_note }} @endif
              @elseif ($row->status === 'refunded')
                <br>{{ __('Refunded on :date.', ['date' => $row->refunded_at?->format('j M Y')]) }}
              @endif
            </div>
          </div>
          <div class="r">
            <span class="rt-pill rt-{{ $row->status }}">{{ $row->status_label }}</span>
            <b>৳{{ number_format($row->refund_amount) }}</b>
          </div>
        </div>
      @endforeach
    </div>

    <div class="pagination" style="margin-top:20px">{{ $rows->onEachSide(1)->links('vendor.pagination.site') }}</div>

  @endif

</div>
@endsection
