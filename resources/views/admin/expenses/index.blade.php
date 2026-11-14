@extends('admin.layouts.app')
@section('title', 'Expenses')

@section('content')

<div class="note" style="margin-bottom:18px">
  <b>What goes here.</b>
  Money the shop spends that is <em>not</em> the cost of the products — adverts, courier bills, packaging, salaries, rent.
  The Reports page takes these off the gross profit to show what the shop really kept.
</div>

<div class="form-grid">
  <div class="form-main">
    <div class="panel" style="border-radius:12px;overflow-x:auto">
      <div class="panel-head">
        <div>
          <h2>{{ $month->format('F Y') }}</h2>
          <span class="sub">{{ $rows->count() }} {{ $rows->count() === 1 ? 'entry' : 'entries' }} · ৳{{ number_format($total) }} in total</span>
        </div>
        <form method="GET" action="{{ route('admin.expenses.index') }}" class="filters">
          <input type="month" name="month" value="{{ $month->format('Y-m') }}" max="{{ now()->format('Y-m') }}" onchange="this.form.submit()">
        </form>
      </div>

      @if ($rows->isEmpty())
        <div class="empty"><b>Nothing recorded for {{ $month->format('F') }}</b>Add the first one with the form on the right.</div>
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
        </table>
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
        <div class="panel-head"><h2>By category</h2></div>
        <div class="panel-body">
          @foreach ($byCategory as $key => $sum)
            <div style="margin-bottom:12px">
              <div style="display:flex;justify-content:space-between;font-size:13.5px;margin-bottom:5px">
                <span>{{ $categories[$key] ?? $key }}</span><strong>৳{{ number_format($sum) }}</strong>
              </div>
              <div class="dash-meter" style="margin:0"><i style="width:{{ max(3, round($sum / max(1, $total) * 100)) }}%;background:linear-gradient(90deg,#C7860B,#F3C55A)"></i></div>
            </div>
          @endforeach
        </div>
      </div>
    @endif
  </div>
</div>

@endsection
