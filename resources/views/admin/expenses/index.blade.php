@extends('admin.layouts.app')
@section('title', 'Expenses')

@section('content')
@php
  $period = match (true) {
      $preset === 'all'                      => 'All time',
      $from->isSameDay($to)                  => $from->format('j M Y'),
      $from->day === 1 && $to->isSameDay($from->copy()->endOfMonth()) => $from->format('F Y'),
      $from->dayOfYear === 1 && $to->isSameDay($from->copy()->endOfYear()) => $from->format('Y'),
      default                                => $from->format('j M Y') . ' to ' . $to->format('j M Y'),
  };
  $change = $before && $before['total'] > 0 ? round(($total - $before['total']) / $before['total'] * 100, 1) : null;
@endphp

<style>
.ex-bar{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:14px}
.ex-pills{display:flex;gap:6px;flex-wrap:wrap}
.ex-pill{padding:7px 13px;border-radius:20px;font-size:13px;font-weight:600;background:#fff;border:1px solid var(--line);color:var(--ink-mute);transition:border-color .15s,color .15s}
.ex-pill:hover{border-color:var(--navy);color:var(--navy)}
.ex-pill.on{background:var(--navy);border-color:var(--navy);color:#fff}
.ex-custom{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
.ex-custom input[type=date],.ex-custom select{border:1.5px solid var(--line);border-radius:8px;padding:7px 10px;font:inherit;font-size:13.5px;background:#fff}
.ex-total{display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;padding:18px 20px;margin-bottom:18px;border-radius:12px;color:#fff;
  background:radial-gradient(420px 200px at 100% 0,rgba(223,163,39,.34),transparent 70%),linear-gradient(120deg,#0C1C36,#14305A 60%,#1E4079)}
.ex-total small{display:block;font-size:11px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:#F3D48A}
.ex-total b{display:block;font-family:var(--display);font-size:30px;line-height:1.1;margin:5px 0 4px}
.ex-total span{font-size:13px;color:#C9D5EA}
.ex-total .up{color:#FFB4A8;font-weight:600}.ex-total .down{color:#8FE3B9;font-weight:600}
.ex-month{display:flex;justify-content:space-between;gap:12px;padding:9px 0;border-bottom:1px dashed #E4E7EE;font-size:13.5px}
.ex-month:last-child{border-bottom:0}
</style>

<div class="ex-bar">
  <div class="ex-pills">
    @foreach ($presets as $key => $label)
      @if ($key !== 'custom')
        <a href="{{ route('admin.expenses.index', array_filter(['range' => $key, 'category' => $category])) }}" class="ex-pill {{ $preset === $key ? 'on' : '' }}">{{ $label }}</a>
      @endif
    @endforeach
  </div>

  <form method="GET" action="{{ route('admin.expenses.index') }}" class="ex-custom">
    <input type="hidden" name="range" value="custom">
    <input type="date" name="from" value="{{ ($from ?? now()->startOfMonth())->toDateString() }}" max="{{ now()->toDateString() }}" aria-label="From">
    <span class="sub">to</span>
    <input type="date" name="to" value="{{ ($to && $to->lte(now()) ? $to : now())->toDateString() }}" max="{{ now()->toDateString() }}" aria-label="To">
    <select name="category" aria-label="Category">
      <option value="">All categories</option>
      @foreach ($categories as $key => $label)
        <option value="{{ $key }}" @selected($category === $key)>{{ $label }}</option>
      @endforeach
    </select>
    <button type="submit" class="btn btn-navy btn-sm">Show</button>
  </form>
</div>

<div class="ex-total">
  <div>
    <small>Total spent · {{ $period }}{{ $category ? ' · ' . $categories[$category] : '' }}</small>
    <b>৳{{ number_format($total) }}</b>
    <span>
      {{ number_format($rows->total()) }} {{ $rows->total() === 1 ? 'entry' : 'entries' }}
      @if ($before)
        ·
        @if ($change !== null)
          <span class="{{ $change > 0 ? 'up' : 'down' }}">{{ $change > 0 ? '▲' : '▼' }} {{ abs($change) }}%</span>
          {{ $change > 0 ? 'more' : 'less' }} than {{ $before['label'] }} (৳{{ number_format($before['total']) }})
        @else
          nothing was recorded in {{ $before['label'] }}
        @endif
      @endif
    </span>
  </div>
  <a href="{{ route('admin.expenses.export', $query) }}" class="btn btn-gold">Download (CSV for Excel or Google Sheets)</a>
</div>

<div class="form-grid">
  <div class="form-main">
    <div class="panel" style="border-radius:12px;overflow-x:auto">
      <div class="panel-head">
        <div>
          <h2>{{ $period }}</h2>
          <span class="sub">{{ $category ? $categories[$category] . ' only' : 'Every category' }}</span>
        </div>
        @if ($category)
          <a href="{{ route('admin.expenses.index', array_filter(['range' => $preset, 'from' => $query['from'] ?? null, 'to' => $query['to'] ?? null])) }}" class="btn btn-line btn-sm">Show all categories</a>
        @endif
      </div>

      @if ($rows->isEmpty())
        <div class="empty"><b>Nothing recorded for these dates</b>Choose other dates above, or add an expense with the form on the right.</div>
      @else
        <table>
          <thead><tr><th>Date</th><th>Category</th><th>Note</th><th class="right">Amount</th><th></th></tr></thead>
          <tbody>
          @foreach ($rows as $row)
            <tr>
              <td style="white-space:nowrap">{{ $row->spent_on->format('j M Y') }}</td>
              <td><span class="badge b-gold">{{ $row->category_label }}</span></td>
              <td class="sub">{{ $row->note ?: '—' }}</td>
              <td class="right"><strong>৳{{ number_format($row->amount) }}</strong></td>
              <td class="right">
                <form method="POST" action="{{ route('admin.expenses.destroy', $row) }}"
                      onsubmit="return confirm('Remove this expense? This cannot be undone.')">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn btn-danger btn-sm">Remove</button>
                </form>
              </td>
            </tr>
          @endforeach
          </tbody>
          <tfoot>
            <tr>
              <td colspan="3" class="right"><strong>Total for {{ $period }}</strong>@if ($rows->hasPages()) <span class="sub">(all pages)</span>@endif</td>
              <td class="right"><strong>৳{{ number_format($total) }}</strong></td>
              <td></td>
            </tr>
          </tfoot>
        </table>
        @if ($rows->hasPages())
          <div class="pager">{{ $rows->links('vendor.pagination.amjr') }}</div>
        @endif
      @endif
    </div>
  </div>

  <div class="form-side">
    <div class="panel" style="border-radius:12px">
      <div class="panel-head"><h2>Add an expense</h2></div>
      <div class="panel-body">
        @if ($errors->any())
          <div class="alert alert-bad" style="margin-bottom:14px">
            <ul style="margin:0;padding-left:18px">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
          </div>
        @endif

        <form method="POST" action="{{ route('admin.expenses.store') }}">
          @csrf
          <div class="field">
            <label for="spent_on">Date</label>
            <input type="date" id="spent_on" name="spent_on" required max="{{ now()->toDateString() }}"
                   value="{{ old('spent_on', now()->toDateString()) }}" style="width:100%;border:1.5px solid var(--line);border-radius:3px;padding:10px 12px;font:inherit;font-size:14.5px">
          </div>
          <div class="field">
            <label for="category">Category</label>
            <select id="category" name="category" required>
              @foreach ($categories as $key => $label)
                <option value="{{ $key }}" @selected(old('category') === $key)>{{ $label }}</option>
              @endforeach
            </select>
          </div>
          <div class="field">
            <label for="amount">Amount (৳)</label>
            <input type="number" id="amount" name="amount" required min="0.01" step="0.01" value="{{ old('amount') }}">
          </div>
          <div class="field">
            <label for="note">Note <span class="sub">(optional)</span></label>
            <input type="text" id="note" name="note" maxlength="200" value="{{ old('note') }}" placeholder="Facebook ads, week 2">
          </div>
          <button type="submit" class="btn btn-gold" style="width:100%">Add expense</button>
        </form>
      </div>
    </div>

    @if ($byCategory)
      <div class="panel" style="border-radius:12px;margin-top:18px">
        <div class="panel-head"><div><h2>By category</h2><span class="sub">{{ $period }} · click one to filter</span></div></div>
        <div class="panel-body">
          @foreach ($byCategory as $key => $sum)
            <a href="{{ route('admin.expenses.index', array_filter(['range' => $preset, 'category' => $key, 'from' => $query['from'] ?? null, 'to' => $query['to'] ?? null])) }}" style="display:block;margin-bottom:12px">
              <div style="display:flex;justify-content:space-between;font-size:13.5px;margin-bottom:5px">
                <span @if ($category === $key) style="font-weight:700;color:var(--navy)" @endif>{{ $categories[$key] ?? $key }}</span><strong>৳{{ number_format($sum) }}</strong>
              </div>
              <div class="dash-meter" style="margin:0"><i style="width:{{ max(3, round($sum / max(1, $allTotal) * 100)) }}%;background:linear-gradient(90deg,#C7860B,#F3C55A)"></i></div>
            </a>
          @endforeach
        </div>
      </div>
    @endif

    @if (count($byMonth) > 1)
      <div class="panel" style="border-radius:12px;margin-top:18px">
        <div class="panel-head"><h2>Month by month</h2></div>
        <div class="panel-body">
          @foreach ($byMonth as $m)
            <div class="ex-month"><span>{{ $m['label'] }} <span class="sub">· {{ $m['count'] }}</span></span><strong>৳{{ number_format($m['total']) }}</strong></div>
          @endforeach
        </div>
      </div>
    @endif
  </div>
</div>

@endsection
