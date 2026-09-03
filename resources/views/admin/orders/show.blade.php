@extends('admin.layouts.app')
@section('title', 'Order ' . $order->order_number)

@section('content')

<div class="form-grid">
<div class="form-main">

  <div class="panel">
    <div class="panel-head">
      <div>
        <h2>{{ $order->order_number }}</h2>
        <div class="sub">Placed {{ $order->created_at->format('j M Y, g:ia') }}</div>
      </div>
      <div style="display:flex;gap:8px">
        <a href="{{ route('admin.orders.invoice', $order) }}" target="_blank" class="btn btn-line btn-sm">Invoice</a>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-line btn-sm">All orders</a>
      </div>
    </div>

    <table>
      <thead><tr><th>Item</th><th class="right">Price</th><th class="right">Qty</th><th class="right">Total</th></tr></thead>
      <tbody>
      @foreach ($order->items as $item)
        <tr>
          <td>
            <strong>{{ $item->name }}</strong>
            <div class="sub">
              @if ($item->variant_label){{ $item->variant_label }} · @endif
              {{ $item->sku }}
              @if ($item->brand_name) · {{ $item->brand_name }}@endif
            </div>
          </td>
          <td class="right">৳{{ number_format($item->unit_price) }}</td>
          <td class="right">{{ $item->quantity }}</td>
          <td class="right"><strong>৳{{ number_format($item->line_total) }}</strong></td>
        </tr>
      @endforeach
      </tbody>
    </table>

    <div class="panel-body">
      <div class="glance"><span>Subtotal</span><b>৳{{ number_format($order->subtotal) }}</b></div>
      <div class="glance"><span>Delivery — {{ $order->shipping_zone_name ?? 'not set' }}</span>
        <b>{{ $order->delivery_charge > 0 ? '৳' . number_format($order->delivery_charge) : 'Free' }}</b></div>
      @if ($order->discount > 0)
        <div class="glance"><span>Discount</span><b>− ৳{{ number_format($order->discount) }}</b></div>
      @endif
      <div class="glance"><span><strong>Total to collect</strong></span><b style="font-size:19px">৳{{ number_format($order->total) }}</b></div>
      @if ($order->profit !== null)
        <div class="glance"><span>Your profit on this order</span><b>৳{{ number_format($order->profit) }}</b></div>
      @else
        <div class="glance"><span>Profit</span><b class="sub" style="font-size:13px">Some items have no cost set</b></div>
      @endif
    </div>
  </div>

  @if ($order->customer_note)
    <div class="panel">
      <div class="panel-head"><h2>Note from the customer</h2></div>
      <div class="panel-body"><p style="margin:0">{{ $order->customer_note }}</p></div>
    </div>
  @endif

  <div class="panel">
    <div class="panel-head"><h2>Courier and notes</h2></div>
    <div class="panel-body">
      <form method="POST" action="{{ route('admin.orders.delivery', $order) }}">
        @csrf @method('PATCH')
        <div class="row2">
          <div class="field">
            <label for="courier">Courier</label>
            <input type="text" id="courier" name="courier" value="{{ old('courier', $order->courier) }}"
                   placeholder="Steadfast, Pathao, RedX…">
          </div>
          <div class="field">
            <label for="tracking_number">Tracking number</label>
            <input type="text" id="tracking_number" name="tracking_number" value="{{ old('tracking_number', $order->tracking_number) }}">
            <div class="hint">Shown to the customer on the tracking page.</div>
          </div>
        </div>
        <div class="field">
          <label for="admin_note">Internal note</label>
          <textarea id="admin_note" name="admin_note" style="min-height:80px">{{ old('admin_note', $order->admin_note) }}</textarea>
          <div class="hint">Only you see this.</div>
        </div>
        <button type="submit" class="btn btn-navy">Save</button>
      </form>
    </div>
  </div>

</div>

<div class="form-side">

  <div class="panel">
    <div class="panel-head"><h2>Status</h2></div>
    <div class="panel-body">
      <div style="margin-bottom:14px">
        <span class="badge status-{{ $order->status }}" style="font-size:13px;padding:6px 12px">{{ $order->status_label }}</span>
      </div>

      <form method="POST" action="{{ route('admin.orders.status', $order) }}">
        @csrf @method('PATCH')
        <div class="field">
          <select name="status">
            @foreach (\App\Models\Order::STATUSES as $key => $label)
              <option value="{{ $key }}" @selected($order->status === $key)>{{ $label }}</option>
            @endforeach
          </select>
          <div class="hint">Cancelling or returning puts the stock back automatically.</div>
        </div>
        <button type="submit" class="btn btn-gold" style="width:100%">Update status</button>
      </form>

      <div style="margin-top:16px">
        @if ($order->confirmed_at)<div class="glance"><span>Confirmed</span><b style="font-size:13px">{{ $order->confirmed_at->format('j M, g:ia') }}</b></div>@endif
        @if ($order->shipped_at)<div class="glance"><span>Shipped</span><b style="font-size:13px">{{ $order->shipped_at->format('j M, g:ia') }}</b></div>@endif
        @if ($order->delivered_at)<div class="glance"><span>Delivered</span><b style="font-size:13px">{{ $order->delivered_at->format('j M, g:ia') }}</b></div>@endif
        @if ($order->cancelled_at)<div class="glance"><span>Cancelled</span><b style="font-size:13px">{{ $order->cancelled_at->format('j M, g:ia') }}</b></div>@endif
      </div>
    </div>
  </div>

  <div class="panel">
    <div class="panel-head"><h2>Customer</h2></div>
    <div class="panel-body">
      <p style="margin:0 0 12px;line-height:1.7">
        <strong>{{ $order->customer_name }}</strong><br>
        <a href="tel:{{ $order->customer_phone }}">{{ $order->customer_phone }}</a><br>
        @if ($order->customer_email)<a href="mailto:{{ $order->customer_email }}">{{ $order->customer_email }}</a><br>@endif
      </p>
      <p class="sub" style="margin:0 0 12px;line-height:1.6">
        {{ $order->shipping_address }}
        @if ($order->shipping_area)<br>{{ $order->shipping_area }}@endif
      </p>
      <div class="glance"><span>Account</span><b style="font-size:13px">{{ $order->user_id ? 'Registered' : 'Guest' }}</b></div>
      <div class="glance"><span>Payment</span><b style="font-size:13px">{{ $order->payment_status === 'paid' ? 'Collected' : 'Pending' }}</b></div>
    </div>
  </div>

</div>
</div>
@endsection
