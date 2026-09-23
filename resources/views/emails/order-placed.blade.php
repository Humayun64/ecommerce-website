@extends('emails._layout')

@section('body')
  <h1 style="margin:0 0 14px;font-size:22px;color:#14305A;">{{ __('Thank you, :name', ['name' => $order->customer_name]) }}</h1>
  <p style="margin:0 0 20px;font-size:15px;line-height:1.6;color:#5B6270;">
    {{ __('We have your order. We will call :phone to confirm before the parcel goes out.', ['phone' => $order->customer_phone]) }}
  </p>

  <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
         style="border:1px dashed #DFA327;border-radius:4px;background:#FFFCF5;margin-bottom:22px;">
    <tr><td style="padding:14px 18px;text-align:center;">
      <div style="font-size:11.5px;color:#5B6270;letter-spacing:0.05em;">{{ __('ORDER NUMBER') }}</div>
      <div style="font-size:20px;font-weight:800;color:#14305A;margin-top:3px;">{{ $order->order_number }}</div>
    </td></tr>
  </table>

  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:6px;">
    @foreach ($order->items as $item)
      <tr>
        <td style="padding:9px 0;border-bottom:1px solid #EEF0F3;font-size:14px;line-height:1.45;">
          <strong>{{ $item->quantity }}×</strong> {{ $item->name }}
          @if ($item->variant_label)<br><span style="font-size:12.5px;color:#5B6270;">{{ $item->variant_label }}</span>@endif
        </td>
        <td style="padding:9px 0;border-bottom:1px solid #EEF0F3;text-align:right;font-size:14px;white-space:nowrap;">
          ৳{{ number_format($item->line_total) }}
        </td>
      </tr>
    @endforeach
    <tr>
      <td style="padding:9px 0;font-size:13.5px;color:#5B6270;">{{ __('Subtotal') }}</td>
      <td style="padding:9px 0;text-align:right;font-size:13.5px;">৳{{ number_format($order->subtotal) }}</td>
    </tr>
    @if ($order->discount > 0)
      <tr>
        <td style="padding:2px 0;font-size:13.5px;color:#1B7F4C;">
          {{ __('Coupon') }}@if ($order->coupon_code) {{ $order->coupon_code }}@endif
        </td>
        <td style="padding:2px 0;text-align:right;font-size:13.5px;color:#1B7F4C;">− ৳{{ number_format($order->discount) }}</td>
      </tr>
    @endif
    <tr>
      <td style="padding:2px 0;font-size:13.5px;color:#5B6270;">{{ __('Delivery') }} — {{ $order->shipping_zone_name }}</td>
      <td style="padding:2px 0;text-align:right;font-size:13.5px;">
        {{ $order->delivery_charge > 0 ? '৳' . number_format($order->delivery_charge) : __('Free') }}
      </td>
    </tr>
    <tr>
      <td style="padding:13px 0 0;border-top:2px solid #14305A;font-size:15px;font-weight:700;">{{ __('Pay on delivery') }}</td>
      <td style="padding:13px 0 0;border-top:2px solid #14305A;text-align:right;font-size:19px;font-weight:800;color:#14305A;">
        ৳{{ number_format($order->total) }}
      </td>
    </tr>
  </table>

  <p style="margin:22px 0 8px;font-size:12px;color:#5B6270;letter-spacing:0.05em;text-transform:uppercase;">{{ __('Delivering to') }}</p>
  <p style="margin:0 0 24px;font-size:14px;line-height:1.6;color:#5B6270;">
    <strong style="color:#15181D;">{{ $order->customer_name }}</strong><br>
    {{ $order->shipping_address }}@if ($order->shipping_area)<br>{{ $order->shipping_area }}@endif<br>
    {{ $order->customer_phone }}
  </p>

  <a href="{{ route('orders.track') }}"
     style="display:inline-block;background:#DFA327;color:#0C1C36;text-decoration:none;padding:13px 28px;border-radius:3px;font-weight:700;font-size:15px;">
    {{ __('Track your order') }}
  </a>
@endsection
