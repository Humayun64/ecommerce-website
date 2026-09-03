<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Invoice {{ $order->order_number }}</title>
<style>
  *{box-sizing:border-box}
  body{font-family:system-ui,-apple-system,"Segoe UI",sans-serif;color:#15181D;margin:0;padding:34px;font-size:14px;line-height:1.55}
  .sheet{max-width:760px;margin:0 auto}
  .top{display:flex;justify-content:space-between;align-items:flex-start;gap:24px;padding-bottom:20px;border-bottom:2px solid #14305A;margin-bottom:22px}
  .co h1{margin:0 0 4px;font-size:23px;color:#14305A;letter-spacing:-.02em}
  .co p{margin:0;font-size:12.5px;color:#5B6270;line-height:1.5}
  .inv{text-align:right}
  .inv h2{margin:0 0 6px;font-size:15px;letter-spacing:.08em;text-transform:uppercase;color:#5B6270}
  .inv .num{font-size:19px;font-weight:700;color:#14305A}
  .inv .date{font-size:12.5px;color:#5B6270;margin-top:3px}
  .who{display:flex;gap:40px;margin-bottom:24px}
  .who h3{margin:0 0 6px;font-size:11.5px;letter-spacing:.08em;text-transform:uppercase;color:#5B6270}
  .who p{margin:0;line-height:1.6}
  table{width:100%;border-collapse:collapse;margin-bottom:20px}
  th{text-align:left;font-size:11.5px;letter-spacing:.05em;text-transform:uppercase;color:#5B6270;padding:9px 10px;border-bottom:2px solid #DDE0E6}
  td{padding:11px 10px;border-bottom:1px solid #EEF0F3;vertical-align:top}
  .r{text-align:right}
  .sub{font-size:12px;color:#5B6270}
  .totals{margin-left:auto;width:280px}
  .totals div{display:flex;justify-content:space-between;padding:6px 10px;font-size:13.5px}
  .totals .grand{border-top:2px solid #14305A;margin-top:6px;padding-top:11px;font-size:17px;font-weight:700;color:#14305A}
  .payline{margin-top:24px;padding:13px 16px;background:#FCF6E8;border-left:3px solid #DFA327;font-size:13.5px}
  .foot{margin-top:30px;padding-top:16px;border-top:1px solid #DDE0E6;font-size:12px;color:#5B6270;text-align:center}
  .noprint{margin-bottom:20px;text-align:center}
  .noprint button{background:#DFA327;border:0;padding:11px 26px;border-radius:3px;font-size:14px;font-weight:600;cursor:pointer;font-family:inherit}
  @media print{.noprint{display:none}body{padding:0}}
</style>
</head>
<body>
<div class="sheet">

  <div class="noprint"><button onclick="window.print()">Print this invoice</button></div>

  <div class="top">
    <div class="co">
      <h1>{{ $settings['store_name'] ?? 'AMJR Global' }}</h1>
      <p>
        {{ $settings['store_address'] ?? '' }}<br>
        {{ $settings['store_phone'] ?? '' }}
        @if (!empty($settings['store_email'])) · {{ $settings['store_email'] }}@endif
      </p>
    </div>
    <div class="inv">
      <h2>Invoice</h2>
      <div class="num">{{ $order->order_number }}</div>
      <div class="date">{{ $order->created_at->format('j M Y') }}</div>
    </div>
  </div>

  <div class="who">
    <div>
      <h3>Deliver to</h3>
      <p>
        <strong>{{ $order->customer_name }}</strong><br>
        {{ $order->shipping_address }}<br>
        @if ($order->shipping_area){{ $order->shipping_area }}<br>@endif
        {{ $order->customer_phone }}
      </p>
    </div>
    <div>
      <h3>Delivery</h3>
      <p>
        {{ $order->shipping_zone_name ?? '—' }}<br>
        @if ($order->courier){{ $order->courier }}@endif
        @if ($order->tracking_number)<br>{{ $order->tracking_number }}@endif
      </p>
    </div>
  </div>

  <table>
    <thead><tr><th>Item</th><th class="r">Price</th><th class="r">Qty</th><th class="r">Total</th></tr></thead>
    <tbody>
    @foreach ($order->items as $item)
      <tr>
        <td>
          {{ $item->name }}
          <div class="sub">@if ($item->variant_label){{ $item->variant_label }} · @endif{{ $item->sku }}</div>
        </td>
        <td class="r">৳{{ number_format($item->unit_price) }}</td>
        <td class="r">{{ $item->quantity }}</td>
        <td class="r">৳{{ number_format($item->line_total) }}</td>
      </tr>
    @endforeach
    </tbody>
  </table>

  <div class="totals">
    <div><span>Subtotal</span><span>৳{{ number_format($order->subtotal) }}</span></div>
    <div><span>Delivery</span><span>{{ $order->delivery_charge > 0 ? '৳' . number_format($order->delivery_charge) : 'Free' }}</span></div>
    @if ($order->discount > 0)
      <div><span>Discount</span><span>− ৳{{ number_format($order->discount) }}</span></div>
    @endif
    <div class="grand"><span>Total</span><span>৳{{ number_format($order->total) }}</span></div>
  </div>

  <div class="payline">
    @if ($order->payment_status === 'paid')
      Paid in full. Thank you.
    @else
      <strong>Cash on delivery</strong> — please collect ৳{{ number_format($order->total) }} from the customer.
    @endif
  </div>

  <div class="foot">
    Questions about this order? Call {{ $settings['store_phone'] ?? '' }} and quote {{ $order->order_number }}.
  </div>
</div>
</body>
</html>
