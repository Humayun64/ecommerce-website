@extends('admin.layouts.app')
@section('title', 'Return #' . $row->id)

@section('content')

<style>
.rd-grid{display:grid;grid-template-columns:minmax(0,1fr) 340px;gap:18px;align-items:start}
@media(max-width:1000px){.rd-grid{grid-template-columns:1fr}}
.rd-pill{display:inline-block;font-size:11.5px;font-weight:700;border-radius:999px;padding:3px 11px}
.rt-pending{background:#FDF3DC;color:#8A6410}
.rt-approved{background:#E4F3EA;color:#14663E}
.rt-rejected{background:#FBE7E4;color:#9E3423}
.rt-refunded{background:#E8EDF6;color:var(--navy)}
.rd-rows{display:grid;gap:0}
.rd-row{display:grid;grid-template-columns:150px minmax(0,1fr);gap:14px;padding:12px 0;border-bottom:1px solid var(--line)}
.rd-row:last-child{border-bottom:0}
.rd-row .k{color:var(--ink-mute);font-size:13px}
.rd-row .v{font-weight:600}
.rd-quote{background:var(--paper);border-left:3px solid var(--line);padding:12px 14px;border-radius:0 9px 9px 0;font-weight:400;white-space:pre-line}
.rd-money{font-family:var(--display);font-variation-settings:"wdth" 110;font-weight:800;font-size:22px;color:var(--navy)}
.rd-act{display:grid;gap:12px}
.rd-act label{display:block;font-size:12.5px;color:var(--ink-mute);margin-bottom:5px}
.rd-act input[type=text],.rd-act input[type=number],.rd-act textarea{
  width:100%;font:inherit;font-size:14px;padding:9px 11px;
  border:1px solid var(--line);border-radius:9px;outline:0;background:#fff;
}
.rd-act input:focus,.rd-act textarea:focus{border-color:var(--navy)}
.rd-check{display:flex;align-items:flex-start;gap:9px;font-size:13.5px;line-height:1.45}
.rd-check input{margin-top:3px;flex:0 0 auto}
.rd-btns{display:flex;gap:9px;flex-wrap:wrap}
.rd-done{background:#E4F3EA;border:1px solid #BFE0CD;color:#14663E;border-radius:11px;padding:13px 15px;font-size:13.5px;line-height:1.5}
.rd-fault{background:#FDF3DC;border:1px solid #EBD79B;color:#7A5A0E;border-radius:11px;padding:13px 15px;font-size:13.5px;line-height:1.5;margin-bottom:14px}
.rd-time{font-size:12.5px;color:var(--ink-mute);margin-top:10px;line-height:1.7}
</style>

<div class="tabs" style="margin-bottom:16px">
  <a href="{{ route('admin.returns.index') }}" class="tab">← All returns</a>
  @if ($row->order)
    <a href="{{ route('admin.orders.show', $row->order) }}" class="tab">Order #{{ $row->order->order_number }}</a>
  @endif
  <a href="{{ route('admin.customers.show', $row->customer_phone) }}" class="tab">Customer</a>
</div>

<div class="rd-grid">

  <div class="panel">
    <div class="panel-head">
      <div>
        <h2>Return #{{ $row->id }} <span class="rd-pill rt-{{ $row->status }}">{{ $row->status_label }}</span></h2>
        <div class="sub">Opened {{ $row->created_at->format('j M Y') }} at {{ $row->created_at->format('g:i a') }}</div>
      </div>
    </div>

    <div class="panel-body">
      @if ($row->is_our_fault)
        <div class="rd-fault">
          <b>This one is on us.</b>
          The customer says the item was {{ strtolower($row->reason_label) }}. Refusing it will cost
          more in goodwill than the item is worth, and the stock should not go back on the shelf
          until you have seen it.
        </div>
      @endif

      <div class="rd-rows">
        <div class="rd-row">
          <div class="k">Customer</div>
          <div class="v">
            {{ $row->customer_name }}<br>
            <span class="sub">{{ $row->customer_phone }}@if ($row->customer_email) · {{ $row->customer_email }}@endif</span>
          </div>
        </div>

        <div class="rd-row">
          <div class="k">Product</div>
          <div class="v">
            {{ $row->product_name }}
            @if ($row->variant_label)<span class="sub">— {{ $row->variant_label }}</span>@endif
            <br><span class="sub">Quantity {{ $row->quantity }}</span>
          </div>
        </div>

        <div class="rd-row">
          <div class="k">Order</div>
          <div class="v">
            @if ($row->order)
              <a href="{{ route('admin.orders.show', $row->order) }}">#{{ $row->order->order_number }}</a>
              <span class="sub">— {{ $row->order->status_label }}, ৳{{ number_format($row->order->total) }}</span>
              @if ($row->order->delivered_at)
                <br><span class="sub">Delivered {{ $row->order->delivered_at->format('j M Y') }}</span>
              @endif
            @else
              <span class="sub">The order has been deleted.</span>
            @endif
          </div>
        </div>

        <div class="rd-row">
          <div class="k">Reason</div>
          <div class="v">{{ $row->reason_label }}</div>
        </div>

        @if ($row->note)
          <div class="rd-row">
            <div class="k">What they said</div>
            <div class="v"><div class="rd-quote">{{ $row->note }}</div></div>
          </div>
        @endif

        @if ($row->photo)
          <div class="rd-row">
            <div class="k">Photo</div>
            <div class="v">
              <a href="{{ Storage::url($row->photo) }}" target="_blank" rel="noopener">
                <img src="{{ Storage::url($row->photo) }}" alt="Photo sent by the customer" style="max-width:260px;border-radius:11px;border:1px solid var(--line)">
              </a>
            </div>
          </div>
        @endif

        <div class="rd-row">
          <div class="k">Refund asked</div>
          <div class="v"><span class="rd-money">৳{{ number_format($row->refund_amount) }}</span></div>
        </div>

        @if ($row->admin_note)
          <div class="rd-row">
            <div class="k">Your note</div>
            <div class="v"><div class="rd-quote">{{ $row->admin_note }}</div></div>
          </div>
        @endif
      </div>

      <div class="rd-time">
        @if ($row->approved_at)Approved {{ $row->approved_at->format('j M Y, g:i a') }}<br>@endif
        @if ($row->rejected_at)Rejected {{ $row->rejected_at->format('j M Y, g:i a') }}<br>@endif
        @if ($row->refunded_at)Refunded {{ $row->refunded_at->format('j M Y, g:i a') }}<br>@endif
        @if ($row->restocked)Stock was put back for this return.@endif
      </div>
    </div>
  </div>

  <div class="panel">
    <div class="panel-head">
      <div>
        <h2>Decide</h2>
        <div class="sub">
          @if ($row->status === 'refunded')
            Settled.
          @else
            The customer sees whatever you pick here.
          @endif
        </div>
      </div>
    </div>

    <div class="panel-body">
      @if ($row->status === 'refunded')
        <div class="rd-done">
          <b>৳{{ number_format($row->refund_amount) }} refunded</b> on
          {{ $row->refunded_at?->format('j M Y') }}.
          {{ $row->restocked ? 'The stock went back on the shelf.' : 'The stock was not put back.' }}
          A refunded return cannot be changed — open a new one if something else comes back.
        </div>
      @else
        <form method="POST" action="{{ route('admin.returns.update', $row) }}" class="rd-act">
          @csrf
          @method('PATCH')

          <div>
            <label for="admin_note">Note {{ $row->status === 'pending' ? '(the customer will not see this)' : '' }}</label>
            <textarea name="admin_note" id="admin_note" rows="3" placeholder="Why you decided this way">{{ old('admin_note', $row->admin_note) }}</textarea>
          </div>

          <div>
            <label for="refund_amount">Refund amount (৳)</label>
            <input type="number" name="refund_amount" id="refund_amount" step="0.01" min="0"
                   value="{{ old('refund_amount', $row->refund_amount) }}">
            <div class="sub" style="margin-top:5px;font-size:12px">
              Starts at what they paid for the item. Lower it if you are keeping a fee,
              or raise it to include the delivery charge.
            </div>
          </div>

          <label class="rd-check">
            <input type="checkbox" name="restock" value="1" @checked(! $row->is_our_fault)>
            <span>Put {{ $row->quantity }} back into stock. Leave this off if the item came back damaged or used.</span>
          </label>

          <div class="rd-btns">
            @if ($row->status !== 'approved')
              <button type="submit" name="action" value="approve" class="btn btn-navy btn-sm">Approve</button>
            @endif
            <button type="submit" name="action" value="refund" class="btn btn-gold btn-sm">Mark refunded</button>
            @if ($row->status !== 'rejected')
              <button type="submit" name="action" value="reject" class="btn btn-line btn-sm">Reject</button>
            @endif
          </div>

          <div class="sub" style="font-size:12px;line-height:1.6">
            <b>Approve</b> tells the customer to send the item back.
            <b>Mark refunded</b> is the last step — it records the money and, if ticked, the stock.
            When every item on the order has been refunded the order itself is marked returned,
            so the dashboard stops counting it as a sale.
          </div>
        </form>
      @endif
    </div>
  </div>

</div>

@endsection
