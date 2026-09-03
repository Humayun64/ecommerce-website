@extends('emails._layout')

@section('body')
  <h1 style="margin:0 0 14px;font-size:22px;color:#14305A;">{{ $headline }}</h1>
  <p style="margin:0 0 20px;font-size:15px;line-height:1.6;color:#5B6270;">{{ $body }}</p>

  <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
         style="border:1px solid #DDE0E6;border-radius:4px;background:#F4F5F7;margin-bottom:22px;">
    <tr><td style="padding:14px 18px;font-size:14px;line-height:1.7;">
      <strong>{{ $order->order_number }}</strong><br>
      <span style="color:#5B6270;">{{ __('Total') }}: ৳{{ number_format($order->total) }}</span>
      @if ($order->courier && $order->tracking_number)
        <br><span style="color:#5B6270;">{{ $order->courier }} — {{ $order->tracking_number }}</span>
      @endif
    </td></tr>
  </table>

  <a href="{{ route('orders.track') }}"
     style="display:inline-block;background:#DFA327;color:#0C1C36;text-decoration:none;padding:13px 28px;border-radius:3px;font-weight:700;font-size:15px;">
    {{ __('Track your order') }}
  </a>
@endsection
