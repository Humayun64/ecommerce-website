@extends('admin.layouts.app')
@section('title', 'Return Requests')

@section('content')

<style>
.rt-cards{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:18px}
@media(max-width:1000px){.rt-cards{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:560px){.rt-cards{grid-template-columns:1fr}}
.rt-card{
  background:#fff;border:1px solid var(--line);border-radius:14px;padding:18px 18px 16px;
  display:flex;align-items:flex-start;gap:14px;
}
.rt-card .ic{
  width:40px;height:40px;border-radius:11px;flex:0 0 auto;
  display:flex;align-items:center;justify-content:center;
}
.rt-card .num{font-family:var(--display);font-variation-settings:"wdth" 110;font-weight:800;font-size:26px;line-height:1.05;color:var(--ink)}
.rt-card .lab{font-size:12.5px;color:var(--ink-mute);margin-top:3px}
.rt-card .foot{font-size:11.5px;color:var(--ink-mute);margin-top:6px}
.rt-wait .ic{background:#FDF3DC;color:#8A6410}
.rt-yes .ic{background:#E4F3EA;color:var(--ok)}
.rt-no .ic{background:#FBE7E4;color:var(--bad)}
.rt-money .ic{background:#E8EDF6;color:var(--navy)}
.rt-money .num{color:var(--navy)}

.rt-pill{display:inline-block;font-size:11.5px;font-weight:700;border-radius:999px;padding:3px 11px;white-space:nowrap}
.rt-pending{background:#FDF3DC;color:#8A6410}
.rt-approved{background:#E4F3EA;color:#14663E}
.rt-rejected{background:#FBE7E4;color:#9E3423}
.rt-refunded{background:#E8EDF6;color:var(--navy)}

.rt-who{font-weight:600}
.rt-who small{display:block;font-weight:400;color:var(--ink-mute);font-size:12.5px;margin-top:2px}
.rt-prod{font-weight:600;max-width:240px}
.rt-prod small{display:block;font-weight:400;color:var(--ink-mute);font-size:12.5px;margin-top:2px}
.rt-amount{font-family:var(--display);font-variation-settings:"wdth" 110;font-weight:700;color:var(--bad)}
.rt-fault{color:var(--warn);font-size:11.5px;font-weight:700;display:block;margin-top:3px}
.rt-num{color:var(--ink-mute);font-size:12.5px}
</style>

<div class="rt-cards">
  <div class="rt-card rt-wait">
    <span class="ic">
      <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><circle cx="12" cy="12" r="9"/><path d="M12 7.5V12l3 2"/></svg>
    </span>
    <div>
      <div class="num">{{ $counts['pending'] }}</div>
      <div class="lab">Pending</div>
      <div class="foot">Waiting on you</div>
    </div>
  </div>

  <div class="rt-card rt-yes">
    <span class="ic">
      <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1"><path d="M4.5 12.5l5 5 10-11"/></svg>
    </span>
    <div>
      <div class="num">{{ $counts['approved'] }}</div>
      <div class="lab">Approved</div>
      <div class="foot">Item on its way back</div>
    </div>
  </div>

  <div class="rt-card rt-no">
    <span class="ic">
      <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1"><path d="M6 6l12 12M18 6L6 18"/></svg>
    </span>
    <div>
      <div class="num">{{ $counts['rejected'] }}</div>
      <div class="lab">Rejected</div>
      <div class="foot">Turned down</div>
    </div>
  </div>

  <div class="rt-card rt-money">
    <span class="ic">
      <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M3 7.5h18v11H3z"/><circle cx="12" cy="13" r="2.6"/><path d="M6.5 13h.01M17.5 13h.01"/></svg>
    </span>
    <div>
      <div class="num">{{ $counts['refunded'] }}</div>
      <div class="lab">Refunded</div>
      <div class="foot">৳{{ number_format($refunded) }} paid back</div>
    </div>
  </div>
</div>

<div class="panel">
  <div class="panel-head">
    <div>
      <h2>{{ $rows->total() }} {{ Str::plural('request', $rows->total()) }}</h2>
      <div class="sub">Manage customer return &amp; refund requests.</div>
    </div>

    <form method="GET" class="filters">
      <input type="text" name="search" value="{{ request('search') }}" placeholder="Customer, phone, product or order no.">
      <select name="status">
        <option value="">All status</option>
        @foreach ($statuses as $key => $label)
          <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
        @endforeach
      </select>
      <button class="btn btn-navy btn-sm">Apply</button>
      @if (request()->hasAny(['search', 'status']))
        <a href="{{ route('admin.returns.index') }}" class="btn btn-line btn-sm">Clear</a>
      @endif
    </form>
  </div>

  @if ($rows->isEmpty())
    <div class="empty">
      <b>No return requests</b>
      @if (request()->hasAny(['search', 'status']))
        Nothing matches that filter.
      @else
        Customers open these from a delivered order, and they land here for you to decide on.
      @endif
    </div>
  @else
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Customer</th>
          <th>Product</th>
          <th>Reason</th>
          <th class="right">Refund</th>
          <th>Status</th>
          <th class="right">Date</th>
          <th class="right"></th>
        </tr>
      </thead>
      <tbody>
      @foreach ($rows as $row)
        <tr>
          <td class="rt-num">#{{ $row->id }}</td>

          <td class="rt-who">
            {{ $row->customer_name }}
            <small>{{ $row->customer_email ?: $row->customer_phone }}</small>
          </td>

          <td class="rt-prod">
            {{ Str::limit($row->product_name, 32) }}
            @if ($row->quantity > 1)<span class="sub">× {{ $row->quantity }}</span>@endif
            <small>
              @if ($row->order)
                Order #{{ $row->order->order_number }}
              @else
                Order removed
              @endif
              @if ($row->variant_label) · {{ $row->variant_label }} @endif
            </small>
          </td>

          <td>
            {{ $row->reason_label }}
            @if ($row->is_our_fault)<span class="rt-fault">Our fault</span>@endif
          </td>

          <td class="right"><span class="rt-amount">৳{{ number_format($row->refund_amount) }}</span></td>

          <td><span class="rt-pill rt-{{ $row->status }}">{{ $row->status_label }}</span></td>

          <td class="right sub">{{ $row->created_at->format('j M Y') }}</td>

          <td class="right">
            <a href="{{ route('admin.returns.show', $row) }}" class="btn btn-line btn-sm">Open</a>
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>

    <div class="pager">{{ $rows->links('vendor.pagination.amjr') }}</div>
  @endif
</div>

@endsection
