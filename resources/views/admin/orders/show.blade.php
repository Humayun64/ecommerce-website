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

      @if ($order->discount > 0)
        <div class="glance">
          <span>
            Discount
            @if ($order->coupon_code)
              <span class="badge b-gold" style="margin-left:6px">{{ $order->coupon_code }}</span>
            @endif
          </span>
          <b>− ৳{{ number_format($order->discount) }}</b>
        </div>
      @endif

      <div class="glance"><span>Delivery — {{ $order->shipping_zone_name ?? 'not set' }}</span>
        <b>{{ $order->delivery_charge > 0 ? '৳' . number_format($order->delivery_charge) : 'Free' }}</b></div>
      <div class="glance"><span><strong>Total to collect</strong></span><b style="font-size:19px">৳{{ number_format($order->total) }}</b></div>

      @if ($order->profit !== null)
        <div class="glance">
          <span>Your profit on this order</span>
          <b>৳{{ number_format($order->profit) }}</b>
        </div>
        @if ($order->discount > 0)
          <div class="sub" style="text-align:right;margin-top:-4px">after the ৳{{ number_format($order->discount) }} discount</div>
        @endif
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

  @php
    $courierChoices = app(\App\Services\CourierManager::class)->available();
    $courierNeeds   = collect(app(\App\Services\CourierManager::class)->all())->map(fn ($d) => $d->needsArea())->all();
  @endphp
  <div class="panel" id="courier">
    <div class="panel-head">
      <h2>Send to courier</h2>
      <a href="{{ route('admin.couriers.index') }}" class="sub">Courier settings</a>
    </div>
    <div class="panel-body">
      @if ($order->courier_code)
        <div class="row2">
          <div>
            <div class="sub">Booked with</div>
            <strong>{{ $order->courier }}</strong>
            <div class="sub" style="margin-top:8px">Tracking code</div>
            @if ($order->courier_track_url)
              <a href="{{ $order->courier_track_url }}" target="_blank" rel="noopener"><strong>{{ $order->tracking_number }}</strong> ↗</a>
            @else
              <strong>{{ $order->tracking_number }}</strong>
            @endif
          </div>
          <div>
            <div class="sub">Courier says</div>
            <span class="badge b-gold">{{ $order->courier_status ?: 'unknown' }}</span>
            <div class="sub" style="margin-top:8px">Last checked {{ $order->courier_synced_at ? $order->courier_synced_at->diffForHumans() : 'never' }}</div>
          </div>
        </div>
        <form method="POST" action="{{ route('admin.orders.courier.sync', $order) }}" style="margin-top:14px">
          @csrf
          <button type="submit" class="btn btn-navy">Check status now</button>
        </form>
      @elseif (in_array($order->status, ['cancelled', 'returned']))
        <p class="sub" style="margin:0">This order is {{ strtolower($order->status_label) }}, so it cannot be sent to a courier.</p>
      @elseif (empty($courierChoices))
        <p class="sub" style="margin:0 0 12px">No courier is connected yet. Add your Steadfast or RedX keys and switch one on.</p>
        <a href="{{ route('admin.couriers.index') }}" class="btn btn-navy">Set up a courier</a>
      @else
        <form method="POST" action="{{ route('admin.orders.courier', $order) }}" id="courierForm">
          @csrf
          <div class="row2">
            <div class="field">
              <label for="courier_code">Courier</label>
              <select id="courier_code" name="courier_code">
                @foreach ($courierChoices as $code => $label)
                  <option value="{{ $code }}" data-area="{{ ($courierNeeds[$code] ?? false) ? 1 : 0 }}" @selected(old('courier_code') === $code)>{{ $label }}</option>
                @endforeach
              </select>
            </div>
            <div class="field">
              <label for="cod">Cash to collect (৳)</label>
              <input type="number" id="cod" name="cod" min="0" step="1"
                     value="{{ old('cod', $order->payment_status === 'paid' ? 0 : (int) round($order->total)) }}">
              <div class="hint">{{ $order->payment_status === 'paid' ? 'Already paid, so nothing to collect.' : 'The order total. Change it only if part was paid in advance.' }}</div>
            </div>
          </div>

          <div id="courierArea" hidden>
            <div class="row2">
              <div class="field">
                <label for="area_search">Delivery area</label>
                <input type="text" id="area_search" placeholder="Type at least 2 letters, e.g. Uttara" autocomplete="off"
                       data-url="{{ route('admin.couriers.areas', '__CODE__') }}">
                <select id="area_pick" size="5" style="margin-top:6px;display:none"></select>
                <input type="hidden" name="area_id" id="area_id" value="{{ old('area_id') }}">
                <input type="hidden" name="area_name" id="area_name" value="{{ old('area_name') }}">
                <div class="hint" id="area_hint">The courier needs an area from its own list.</div>
              </div>
              <div class="field">
                <label for="weight">Parcel weight (grams)</label>
                <input type="number" id="weight" name="weight" min="1" max="50000" value="{{ old('weight', 500) }}">
              </div>
            </div>
          </div>

          <div class="field">
            <label for="courier_note">Note for the rider <span class="sub">(optional)</span></label>
            <input type="text" id="courier_note" name="note" maxlength="200" value="{{ old('note', $order->customer_note) }}">
          </div>

          <label class="check" style="display:flex;gap:9px;align-items:center;margin-bottom:14px;font-size:14px">
            <input type="hidden" name="mark_shipped" value="0">
            <input type="checkbox" name="mark_shipped" value="1" checked>
            Mark the order as Shipped and tell the customer
          </label>

          <button type="submit" class="btn btn-gold">Send to courier</button>
        </form>
      @endif
    </div>
  </div>

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
            <div class="hint">Shown to the customer on the tracking page. Filled in automatically when you use Send to courier.</div>
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
          <div class="hint">Cancelling or returning puts the stock back, and gives back any coupon use.</div>
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
      @if ($order->coupon_code)
        <div class="glance"><span>Coupon used</span><b style="font-size:13px">{{ $order->coupon_code }}</b></div>
      @endif

    </div>
  </div>

  @php($orderPayments = $order->relationLoaded('payments') ? $order->payments : \App\Models\Payment::where('order_id', $order->id)->latest()->get())

  @if ($orderPayments->isNotEmpty())
    <style>
      .op-row{border-bottom:1px solid var(--line);padding:13px 0}
      .op-row:last-child{border-bottom:0}
      .op-top{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
      .op-name{font-weight:600}
      .op-txn{font-family:ui-monospace,'SF Mono',Menlo,Consolas,monospace;font-size:13px;font-weight:600;letter-spacing:.3px}
      .op-meta{color:var(--ink-mute);font-size:12.5px;margin-top:5px;line-height:1.6}
      .op-pill{display:inline-block;font-size:11.5px;font-weight:700;border-radius:999px;padding:3px 11px}
      .op-pending{background:#FDF3DC;color:#8A6410}
      .op-verified{background:#E4F3EA;color:#14663E}
      .op-rejected{background:#FBE7E4;color:#9E3423}
    </style>

    <div class="panel" style="margin-top:18px">
      <div class="panel-head">
        <div>
          <h2>Payment</h2>
          <div class="sub">Check the transaction ID in your own app before verifying it.</div>
        </div>
        <a href="{{ route('admin.payments.index') }}" class="btn btn-line btn-sm">All payments</a>
      </div>

      <div class="panel-body">
        @foreach ($orderPayments as $payment)
          <div class="op-row">
            <div class="op-top">
              <div>
                <span class="op-name">{{ $payment->method_name }}</span>
                @if ($payment->transaction_id)
                  — <span class="op-txn">{{ $payment->transaction_id }}</span>
                @endif
              </div>
              <span class="op-pill op-{{ $payment->status }}">{{ $payment->status_label }}</span>
            </div>

            <div class="op-meta">
              ৳{{ number_format($payment->amount) }}
              @if ($payment->sender_number) · from {{ $payment->sender_number }} @endif
              · {{ $payment->created_at->format('j M Y, g:i a') }}
              @if ($payment->verified_at && $payment->status !== 'pending')
                <br>{{ ucfirst($payment->status) }} by {{ $payment->verifier?->name ?? 'admin' }}
                on {{ $payment->verified_at->format('j M Y, g:i a') }}
              @endif
              @if ($payment->admin_note)<br><em>{{ $payment->admin_note }}</em>@endif
            </div>

            @if ($payment->status === 'pending')
              <form method="POST" action="{{ route('admin.payments.update', $payment) }}" style="margin-top:11px;display:flex;gap:8px">
                @csrf
                @method('PATCH')
                <button type="submit" name="action" value="verify" class="btn btn-navy btn-sm">Verify</button>
                <button type="submit" name="action" value="reject" class="btn btn-line btn-sm">Reject</button>
              </form>
            @endif
          </div>
        @endforeach
      </div>
    </div>
  @endif

</div>
</div>
@endsection

@push('scripts')
<script>
/* Send to courier: show the area picker only for couriers that need one. */
(function () {
  var form = document.getElementById('courierForm');
  if (!form) return;

  var pick   = document.getElementById('courier_code');
  var box    = document.getElementById('courierArea');
  var search = document.getElementById('area_search');
  var list   = document.getElementById('area_pick');
  var idBox  = document.getElementById('area_id');
  var nmBox  = document.getElementById('area_name');
  var hint   = document.getElementById('area_hint');
  var timer  = null;

  function needsArea() {
    var o = pick.options[pick.selectedIndex];
    return !!o && o.getAttribute('data-area') === '1';
  }

  function sync() { box.hidden = !needsArea(); }
  pick.addEventListener('change', sync);
  sync();

  search.addEventListener('input', function () {
    clearTimeout(timer);
    idBox.value = ''; nmBox.value = '';
    var q = search.value.trim();
    if (q.length < 2) { list.style.display = 'none'; return; }

    timer = setTimeout(function () {
      var url = search.getAttribute('data-url').replace('__CODE__', encodeURIComponent(pick.value)) + '?q=' + encodeURIComponent(q);
      fetch(url, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          list.innerHTML = '';
          if (data.error) { hint.textContent = data.error; list.style.display = 'none'; return; }
          (data.areas || []).forEach(function (a) {
            var o = document.createElement('option');
            o.value = a.id; o.textContent = a.name; o.setAttribute('data-area', a.area || a.name);
            list.appendChild(o);
          });
          list.style.display = list.options.length ? '' : 'none';
          hint.textContent = list.options.length ? 'Click the matching area.' : 'No area matches that. Try a shorter word.';
        })
        .catch(function () { hint.textContent = 'Could not load the area list. Try again.'; });
    }, 300);
  });

  list.addEventListener('change', function () {
    var o = list.options[list.selectedIndex];
    if (!o) return;
    idBox.value = o.value;
    nmBox.value = o.getAttribute('data-area');
    search.value = o.textContent;
    list.style.display = 'none';
    hint.textContent = 'Area chosen.';
  });

  form.addEventListener('submit', function (e) {
    if (needsArea() && !idBox.value) {
      e.preventDefault();
      hint.textContent = 'Choose a delivery area from the list first.';
      search.focus();
    }
  });
})();
</script>
@endpush
