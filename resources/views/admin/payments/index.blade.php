@extends('admin.layouts.app')
@section('title', 'Payments')

@section('content')

<style>
.py-cards{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:18px}
@media(max-width:1000px){.py-cards{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:560px){.py-cards{grid-template-columns:1fr}}
.py-card{background:#fff;border:1px solid var(--line);border-radius:14px;padding:18px;display:flex;gap:14px}
.py-card .ic{width:40px;height:40px;border-radius:11px;flex:0 0 auto;display:flex;align-items:center;justify-content:center}
.py-card .num{font-family:var(--display);font-variation-settings:"wdth" 110;font-weight:800;font-size:26px;line-height:1.05}
.py-card .lab{font-size:12.5px;color:var(--ink-mute);margin-top:3px}
.py-card .foot{font-size:11.5px;color:var(--ink-mute);margin-top:6px}
.py-wait .ic{background:#FDF3DC;color:#8A6410}
.py-yes .ic{background:#E4F3EA;color:var(--ok)}
.py-no .ic{background:#FBE7E4;color:var(--bad)}
.py-sum .ic{background:#E8EDF6;color:var(--navy)}
.py-sum .num{color:var(--navy)}

.py-pill{display:inline-block;font-size:11.5px;font-weight:700;border-radius:999px;padding:3px 11px;white-space:nowrap}
.py-pending{background:#FDF3DC;color:#8A6410}
.py-verified{background:#E4F3EA;color:#14663E}
.py-rejected{background:#FBE7E4;color:#9E3423}

.py-txn{font-family:ui-monospace,'SF Mono',Menlo,Consolas,monospace;font-size:13px;font-weight:600;letter-spacing:.3px}
.py-meth{font-weight:600}
.py-meth small{display:block;font-weight:400;color:var(--ink-mute);font-size:12.5px;margin-top:2px}
.py-amt{font-family:var(--display);font-variation-settings:"wdth" 110;font-weight:700;color:var(--navy)}
.py-note{font-size:12.5px;color:var(--ink-mute);font-style:italic;margin-top:4px}
.py-do{display:flex;gap:7px;justify-content:flex-end;flex-wrap:wrap}
.py-do textarea{
  width:100%;font:inherit;font-size:13px;padding:8px 10px;margin-bottom:7px;
  border:1px solid var(--line);border-radius:8px;outline:0;resize:vertical;
}
.py-do textarea:focus{border-color:var(--navy)}
</style>

<div class="py-cards">
  <div class="py-card py-wait">
    <span class="ic"><svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><circle cx="12" cy="12" r="9"/><path d="M12 7.5V12l3 2"/></svg></span>
    <div>
      <div class="num">{{ $counts['pending'] }}</div>
      <div class="lab">To check</div>
      <div class="foot">Open bKash and confirm</div>
    </div>
  </div>
  <div class="py-card py-yes">
    <span class="ic"><svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1"><path d="M4.5 12.5l5 5 10-11"/></svg></span>
    <div>
      <div class="num">{{ $counts['verified'] }}</div>
      <div class="lab">Verified</div>
      <div class="foot">Money confirmed</div>
    </div>
  </div>
  <div class="py-card py-no">
    <span class="ic"><svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1"><path d="M6 6l12 12M18 6L6 18"/></svg></span>
    <div>
      <div class="num">{{ $counts['rejected'] }}</div>
      <div class="lab">Rejected</div>
      <div class="foot">Nothing arrived</div>
    </div>
  </div>
  <div class="py-card py-sum">
    <span class="ic"><svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M3 7.5h18v11H3z"/><circle cx="12" cy="13" r="2.6"/></svg></span>
    <div>
      <div class="num">৳{{ number_format($takings) }}</div>
      <div class="lab">Taken online</div>
      <div class="foot">Verified payments only</div>
    </div>
  </div>
</div>

<div class="panel">
  <div class="panel-head">
    <div>
      <h2>{{ $rows->total() }} {{ Str::plural('payment', $rows->total()) }}</h2>
      <div class="sub">Check each transaction ID in your own app before you verify it.</div>
    </div>

    <form method="GET" class="filters">
      <input type="text" name="search" value="{{ request('search') }}" placeholder="Transaction ID, number or order">
      <select name="status">
        <option value="">All</option>
        @foreach ($statuses as $key => $label)
          <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
        @endforeach
      </select>
      <button class="btn btn-navy btn-sm">Apply</button>
      @if (request()->hasAny(['search', 'status']))
        <a href="{{ route('admin.payments.index') }}" class="btn btn-line btn-sm">Clear</a>
      @endif
    </form>
  </div>

  @if ($rows->isEmpty())
    <div class="empty">
      <b>No payments yet</b>
      Anything sent by bKash, Nagad or Rocket lands here for you to check.
    </div>
  @else
    <table>
      <thead>
        <tr>
          <th>Order</th>
          <th>Method</th>
          <th>From</th>
          <th>Transaction ID</th>
          <th class="right">Amount</th>
          <th>Status</th>
          <th class="right">Action</th>
        </tr>
      </thead>
      <tbody>
      @foreach ($rows as $row)
        <tr>
          <td>
            @if ($row->order)
              <a href="{{ route('admin.orders.show', $row->order) }}">#{{ $row->order->order_number }}</a>
              <small style="display:block;color:var(--ink-mute);font-size:12.5px;margin-top:2px">{{ $row->order->customer_name }}</small>
            @else
              <span class="sub">Order removed</span>
            @endif
          </td>

          <td class="py-meth">
            {{ $row->method_name }}
            <small>{{ $row->created_at->format('j M Y, g:i a') }}</small>
          </td>

          <td class="sub">{{ $row->sender_number ?: '—' }}</td>

          <td><span class="py-txn">{{ $row->transaction_id ?: '—' }}</span></td>

          <td class="right"><span class="py-amt">৳{{ number_format($row->amount) }}</span></td>

          <td>
            <span class="py-pill py-{{ $row->status }}">{{ $row->status_label }}</span>
            @if ($row->admin_note)<div class="py-note">{{ Str::limit($row->admin_note, 60) }}</div>@endif
            @if ($row->verified_at && $row->status !== 'pending')
              <div class="py-note">{{ $row->verifier?->name }} · {{ $row->verified_at->format('j M, g:i a') }}</div>
            @endif
          </td>

          <td class="right">
            @if ($row->status === 'pending')
              <form method="POST" action="{{ route('admin.payments.update', $row) }}">
                @csrf
                @method('PATCH')
                <textarea name="admin_note" rows="1" placeholder="Note (optional)"></textarea>
                <div class="py-do">
                  <button type="submit" name="action" value="verify" class="btn btn-navy btn-sm">Verify</button>
                  <button type="submit" name="action" value="reject" class="btn btn-line btn-sm">Reject</button>
                </div>
              </form>
            @else
              <form method="POST" action="{{ route('admin.payments.update', $row) }}">
                @csrf
                @method('PATCH')
                <div class="py-do">
                  @if ($row->status === 'rejected')
                    <button type="submit" name="action" value="verify" class="btn btn-line btn-sm">Verify after all</button>
                  @else
                    <button type="submit" name="action" value="reject" class="btn btn-line btn-sm">Undo</button>
                  @endif
                </div>
              </form>
            @endif
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>

    <div class="pager">{{ $rows->links('vendor.pagination.amjr') }}</div>
  @endif
</div>

@endsection
