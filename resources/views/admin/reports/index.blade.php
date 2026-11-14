@extends('admin.layouts.app')
@section('title', 'Reports')

@section('content')
@php
  $t       = $report['totals'];
  $bars    = $report['trend']['bars'];
  $peak    = max(1, collect($bars)->max('total'));
  $step    = max(1, (int) ceil(count($bars) / 10));
  $money   = fn ($v) => ($v < 0 ? '−' : '') . '৳' . number_format(abs($v));
  $arrow   = function ($pct) use ($report) {
      if ($pct === null) return '<span class="rp-chg flat">no earlier sales to compare</span>';
      $cls = $pct >= 0 ? 'up' : 'down';
      return '<span class="rp-chg ' . $cls . '">' . ($pct >= 0 ? '▲' : '▼') . ' ' . abs($pct) . '% vs the ' . (int) $report['days'] . ' days before</span>';
  };
  $statusNames = \App\Models\Order::STATUSES;
@endphp

<style>
.rp-bar{display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;margin-bottom:16px}
.rp-pills{display:flex;gap:6px;flex-wrap:wrap}
.rp-pill{padding:7px 13px;border-radius:20px;font-size:13px;font-weight:600;background:#fff;border:1px solid var(--line);color:var(--ink-mute);transition:border-color .15s,color .15s}
.rp-pill:hover{border-color:var(--navy);color:var(--navy)}
.rp-pill.on{background:var(--navy);border-color:var(--navy);color:#fff}
.rp-custom{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
.rp-custom input[type=date]{border:1.5px solid var(--line);border-radius:8px;padding:7px 10px;font:inherit;font-size:13.5px;background:#fff}
.rp-range{font-size:13px;color:var(--ink-mute);margin:-6px 0 16px}
.rp-chg{display:block;font-size:11.5px;font-weight:600;margin-top:5px}
.rp-chg.up{color:#12875A}.rp-chg.down{color:var(--bad)}.rp-chg.flat{color:var(--ink-mute);font-weight:400}
.rp-flow{display:grid;gap:0}
.rp-line{display:flex;justify-content:space-between;gap:14px;padding:9px 0;font-size:14px;border-bottom:1px dashed #E4E7EE}
.rp-line span small{display:block;font-size:12px;color:var(--ink-mute)}
.rp-line.minus b{color:var(--bad)}
.rp-line.sum{border-bottom:0;border-top:2px solid var(--navy);margin-top:2px;padding-top:11px;font-weight:700}
.rp-line.sum b{font-family:var(--display);font-size:17px;color:var(--navy)}
.rp-line.final{background:linear-gradient(90deg,#E9F7F0,#fff);border:1px solid #BFE0CD;border-radius:10px;padding:13px 14px;margin-top:10px}
.rp-line.final b{font-family:var(--display);font-size:20px;color:#12875A}
.rp-line.final.loss{background:linear-gradient(90deg,#FBE9E8,#fff);border-color:#EFC9C6}
.rp-line.final.loss b{color:var(--bad)}
.rp-side{background:#F8FAFD;border:1px solid var(--line);border-radius:10px;padding:13px 14px;font-size:13px;color:var(--ink-mute);line-height:1.55}
.rp-side b{color:var(--ink)}
.rp-two{display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-top:18px}
@media(max-width:980px){.rp-two{grid-template-columns:1fr}}
.rp-share{height:6px;border-radius:6px;background:#EEF1F5;overflow:hidden;margin-top:6px}
.rp-share i{display:block;height:100%;border-radius:6px;background:linear-gradient(90deg,#1E5BD8,#5B93F0)}
.rp-status{display:flex;gap:8px;flex-wrap:wrap}
.rp-neg{color:var(--bad)}
.rp-month-now td{background:#FFFBEF}
.rp-export{display:flex;gap:8px;flex-wrap:wrap}
</style>

<div class="dash">

  <div class="rp-bar">
    <div class="rp-pills">
      @foreach ($presets as $key => $label)
        @if ($key !== 'custom')
          <a href="{{ route('admin.reports.index', ['range' => $key]) }}" class="rp-pill {{ $preset === $key ? 'on' : '' }}">{{ $label }}</a>
        @endif
      @endforeach
    </div>
    <form method="GET" action="{{ route('admin.reports.index') }}" class="rp-custom">
      <input type="hidden" name="range" value="custom">
      <input type="date" name="from" value="{{ $from->toDateString() }}" max="{{ now()->toDateString() }}" aria-label="From">
      <span class="sub">to</span>
      <input type="date" name="to" value="{{ $to->toDateString() }}" max="{{ now()->toDateString() }}" aria-label="To">
      <button type="submit" class="btn btn-navy btn-sm">Show</button>
    </form>
  </div>

  <p class="rp-range">
    Showing <b>{{ $from->format('j M Y') }}</b> to <b>{{ $to->format('j M Y') }}</b> ({{ $report['days'] }} day{{ $report['days'] === 1 ? '' : 's' }}),
    by the date each order was placed.
  </p>

  {{-- headline numbers --}}
  <div class="dash-stats">
    <div class="dash-stat c-blue">
      <span class="dash-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 17l6-6 4 4 8-8"/><path d="M15 7h6v6"/></svg></span>
      <div class="dash-lab">Net sales</div>
      <div class="dash-num">{{ $money($t['net_sales']) }}</div>
      <div class="dash-foot">After ৳{{ number_format($t['discounts']) }} of discounts {!! $arrow($report['change']['net_sales']) !!}</div>
    </div>
    <div class="dash-stat c-violet">
      <span class="dash-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><path d="M3 6h18M16 10a4 4 0 0 1-8 0"/></svg></span>
      <div class="dash-lab">Orders</div>
      <div class="dash-num">{{ number_format($t['orders']) }}</div>
      <div class="dash-foot">Average ৳{{ number_format($t['average']) }} each {!! $arrow($report['change']['orders']) !!}</div>
    </div>
    <div class="dash-stat c-green">
      <span class="dash-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M15 9.5c-.6-.9-1.7-1.5-3-1.5-1.9 0-3 1-3 2.2 0 3 6 1.3 6 4.3 0 1.3-1.3 2.3-3 2.3-1.4 0-2.6-.6-3.2-1.6M12 6.5V8m0 8.5V18"/></svg></span>
      <div class="dash-lab">Gross profit</div>
      <div class="dash-num">{{ $money($t['gross_profit']) }}</div>
      <div class="dash-foot">
        @if ($t['margin'] !== null)<span class="dash-pill">{{ $t['margin'] }}% margin</span>@else No costed sales yet @endif
        {!! $arrow($report['change']['gross_profit']) !!}
      </div>
    </div>
    <div class="dash-stat c-gold">
      <span class="dash-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="6" width="18" height="13" rx="2"/><path d="M3 10h18M7 15h4"/></svg></span>
      <div class="dash-lab">Net profit</div>
      <div class="dash-num {{ $report['net'] < 0 ? 'rp-neg' : '' }}">{{ $money($report['net']) }}</div>
      <div class="dash-foot">After refunds and expenses</div>
    </div>
  </div>

  @if ($t['uncosted'] > 0)
    <div class="note" style="margin-bottom:18px">
      <b>{{ $t['uncosted'] }} order{{ $t['uncosted'] === 1 ? '' : 's' }} in this period {{ $t['uncosted'] === 1 ? 'has' : 'have' }} products with no cost price.</b>
      Their sales are counted, but they are left out of the profit figures because profit cannot be worked out without a cost.
      Add cost prices on the product pages to make future profit complete.
    </div>
  @endif

  <div class="cols">
    {{-- trend --}}
    <div class="panel dash-panel">
      <div class="panel-head">
        <div>
          <h2>Net sales {{ $report['trend']['daily'] ? 'by day' : 'by month' }}</h2>
          <span class="sub">Cancelled and returned orders left out</span>
        </div>
      </div>
      <div class="panel-body">
        <div class="dash-chart">
          <div class="dash-grid" aria-hidden="true"><i></i><i></i><i></i><i></i></div>
          <div class="dash-bars" style="gap:{{ count($bars) > 40 ? 2 : 6 }}px">
            @foreach ($bars as $bar)
              <div class="dash-col {{ $bar['total'] > 0 ? '' : 'is-zero' }}">
                <span class="dash-tip">{{ $bar['label'] }}<b>৳{{ number_format($bar['total']) }}</b></span>
                <div class="dash-bar" style="height:{{ max(2, round($bar['total'] / $peak * 100)) }}%"></div>
                <span class="dash-day" style="display:{{ $loop->index % $step === 0 ? 'block' : 'none' }}">{{ $bar['label'] }}</span>
              </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>

    {{-- how the money adds up --}}
    <div class="panel dash-panel">
      <div class="panel-head"><h2>How the money adds up</h2></div>
      <div class="panel-body">
        <div class="rp-flow">
          <div class="rp-line"><span>Product sales<small>{{ number_format($t['orders']) }} orders</small></span><b>{{ $money($t['sales']) }}</b></div>
          <div class="rp-line minus"><span>Discounts and coupons</span><b>− ৳{{ number_format($t['discounts']) }}</b></div>
          <div class="rp-line sum"><span>Net sales</span><b>{{ $money($t['net_sales']) }}</b></div>
          <div class="rp-line minus"><span>Cost of the goods sold<small>{{ $t['uncosted'] ? 'Costed orders only' : 'What you paid for them' }}</small></span><b>− ৳{{ number_format($t['cost']) }}</b></div>
          <div class="rp-line sum"><span>Gross profit</span><b>{{ $money($t['gross_profit']) }}</b></div>
          <div class="rp-line minus"><span>Refunds paid</span><b>− ৳{{ number_format($report['refunds']) }}</b></div>
          <div class="rp-line minus"><span>Expenses<small><a href="{{ route('admin.expenses.index') }}" style="color:var(--navy);text-decoration:underline">Add or view expenses</a></small></span><b>− ৳{{ number_format($report['expenses']) }}</b></div>
          <div class="rp-line final {{ $report['net'] < 0 ? 'loss' : '' }}"><span>Net profit<small>What the shop kept</small></span><b>{{ $money($report['net']) }}</b></div>
        </div>
        <div class="rp-side" style="margin-top:14px">
          <b>৳{{ number_format($t['delivery']) }}</b> of delivery charges were also collected. That money is shown apart
          because it goes on to the courier; record the courier's bill under Expenses to see the difference.
        </div>
      </div>
    </div>
  </div>

  {{-- twelve months --}}
  <div class="panel dash-panel" style="margin-top:18px;overflow-x:auto">
    <div class="panel-head">
      <div>
        <h2>Month by month</h2>
        <span class="sub">The last twelve months, whatever dates are chosen above</span>
      </div>
      <div class="rp-export">
        <a href="{{ route('admin.reports.export', ['type' => 'months']) }}" class="btn btn-line btn-sm">Download months (CSV)</a>
        <a href="{{ route('admin.reports.export', $query) }}" class="btn btn-line btn-sm">Download these orders (CSV)</a>
      </div>
    </div>
    <table>
      <thead>
        <tr>
          <th>Month</th><th class="right">Orders</th><th class="right">Net sales</th><th class="right">Cost of goods</th>
          <th class="right">Gross profit</th><th class="right">Margin</th><th class="right">Refunds</th><th class="right">Expenses</th><th class="right">Net profit</th>
        </tr>
      </thead>
      <tbody>
        @foreach (array_reverse($months) as $m)
          <tr class="{{ $m['key'] === now()->format('Y-m') ? 'rp-month-now' : '' }}">
            <td><strong>{{ $m['label'] }}</strong>@if ($m['key'] === now()->format('Y-m')) <span class="sub">so far</span>@endif</td>
            <td class="right">{{ number_format($m['totals']['orders']) }}@if ($m['totals']['cancelled'])<div class="sub">{{ $m['totals']['cancelled'] }} cancelled</div>@endif</td>
            <td class="right">৳{{ number_format($m['totals']['net_sales']) }}</td>
            <td class="right">৳{{ number_format($m['totals']['cost']) }}</td>
            <td class="right {{ $m['totals']['gross_profit'] < 0 ? 'rp-neg' : '' }}">{{ $money($m['totals']['gross_profit']) }}</td>
            <td class="right">{{ $m['totals']['margin'] !== null ? $m['totals']['margin'] . '%' : '—' }}</td>
            <td class="right">{{ $m['refunds'] > 0 ? '৳' . number_format($m['refunds']) : '—' }}</td>
            <td class="right">{{ $m['expenses'] > 0 ? '৳' . number_format($m['expenses']) : '—' }}</td>
            <td class="right {{ $m['net'] < 0 ? 'rp-neg' : '' }}"><strong>{{ $money($m['net']) }}</strong></td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <div class="rp-two">
    <div class="panel dash-panel">
      <div class="panel-head"><div><h2>Best-selling products</h2><span class="sub">By sales, in the chosen dates</span></div></div>
      @if ($report['products']->isEmpty())
        <div class="empty"><b>Nothing sold in these dates</b>Pick a wider range above.</div>
      @else
        <table>
          <thead><tr><th>Product</th><th class="right">Units</th><th class="right">Sales</th><th class="right">Profit</th></tr></thead>
          <tbody>
            @foreach ($report['products'] as $row)
              <tr>
                <td>{{ $row->name }}</td>
                <td class="right">{{ number_format($row->units) }}</td>
                <td class="right">৳{{ number_format($row->revenue) }}</td>
                <td class="right">@if ($row->uncosted > 0)<span class="sub">no cost set</span>@else<strong style="color:#12875A">৳{{ number_format($row->profit) }}</strong>@endif</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      @endif
    </div>

    <div class="panel dash-panel">
      <div class="panel-head"><div><h2>Best customers</h2><span class="sub">By what they spent, in the chosen dates</span></div></div>
      @if (empty($report['customers']))
        <div class="empty"><b>No customers in these dates</b>Pick a wider range above.</div>
      @else
        <table>
          <thead><tr><th>Customer</th><th class="right">Orders</th><th class="right">Spent</th></tr></thead>
          <tbody>
            @foreach ($report['customers'] as $row)
              <tr>
                <td>{{ $row['name'] }}<div class="sub">{{ $row['phone'] }}</div></td>
                <td class="right">{{ $row['orders'] }}</td>
                <td class="right"><strong>৳{{ number_format($row['spent']) }}</strong></td>
              </tr>
            @endforeach
          </tbody>
        </table>
      @endif
    </div>
  </div>

  <div class="rp-two">
    @foreach (['byPayment' => 'How customers paid', 'byZone' => 'Where orders went'] as $key => $heading)
      <div class="panel dash-panel">
        <div class="panel-head"><h2>{{ $heading }}</h2></div>
        <div class="panel-body">
          @forelse ($report[$key] as $row)
            <div style="margin-bottom:13px">
              <div style="display:flex;justify-content:space-between;gap:12px;font-size:13.5px">
                <span>{{ $row['label'] }} <span class="sub">· {{ $row['orders'] }} order{{ $row['orders'] === 1 ? '' : 's' }}</span></span>
                <strong>৳{{ number_format($row['sales']) }}</strong>
              </div>
              <div class="rp-share"><i style="width:{{ max(2, round($row['sales'] / max(1, $t['net_sales']) * 100)) }}%"></i></div>
            </div>
          @empty
            <p class="sub" style="margin:0">No orders in these dates.</p>
          @endforelse
        </div>
      </div>
    @endforeach
  </div>

  <div class="panel dash-panel" style="margin-top:18px">
    <div class="panel-head">
      <div><h2>Every order placed in these dates</h2><span class="sub">{{ number_format($t['placed']) }} placed · {{ $t['cancel_rate'] }}% cancelled</span></div>
    </div>
    <div class="panel-body">
      @if (empty($report['byStatus']))
        <p class="sub" style="margin:0">No orders were placed in these dates.</p>
      @else
        <div class="rp-status">
          @foreach ($report['byStatus'] as $status => $count)
            <a href="{{ route('admin.orders.index', ['status' => $status]) }}" class="badge status-{{ $status }}" style="font-size:13px;padding:7px 12px">
              {{ $statusNames[$status] ?? $status }} · {{ $count }}
            </a>
          @endforeach
        </div>
      @endif
    </div>
  </div>

</div>
@endsection
