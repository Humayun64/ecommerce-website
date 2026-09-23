@extends('admin.layouts.app')
@section('title', 'Customers')

@section('content')

<style>
.cu-name{font-weight:600}
.cu-name small{display:block;font-weight:400;color:var(--ink-mute);font-size:12.5px;margin-top:2px}
.cu-money{font-family:var(--display);font-variation-settings:"wdth" 110;font-weight:700;color:var(--navy)}
.cu-refuse{font-weight:700}
.cu-refuse.good{color:var(--ok)}
.cu-refuse.watch{color:var(--warn)}
.cu-refuse.bad{color:var(--bad)}
.cu-note{font-size:12.5px;color:var(--ink-mute);margin-top:4px;font-style:italic}
</style>

<div class="tabs">
  <a href="{{ route('admin.customers.index') }}" class="tab {{ ! request('type') ? 'on' : '' }}">
    Everyone <span>{{ $customers->total() }}</span>
  </a>
  <a href="{{ route('admin.customers.index', ['type' => 'registered']) }}" class="tab {{ request('type') === 'registered' ? 'on' : '' }}">
    With an account
  </a>
  <a href="{{ route('admin.customers.index', ['type' => 'guest']) }}" class="tab {{ request('type') === 'guest' ? 'on' : '' }}">
    Guests
  </a>
  @if ($blocked)
    <span class="tab" style="cursor:default">Blocked <span>{{ $blocked }}</span></span>
  @endif
</div>

<div class="panel">
  <div class="panel-head">
    <div>
      <h2>{{ $customers->total() }} customers</h2>
      <div class="sub">Anyone who has ordered, whether or not they made an account.</div>
    </div>
    <form method="GET" class="filters">
      @if (request('type'))<input type="hidden" name="type" value="{{ request('type') }}">@endif
      <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, phone or email">
      <select name="sort">
        <option value="">Most recent</option>
        <option value="spent" @selected(request('sort') === 'spent')>Biggest spenders</option>
        <option value="orders" @selected(request('sort') === 'orders')>Most orders</option>
        <option value="name" @selected(request('sort') === 'name')>Name A–Z</option>
      </select>
      <button class="btn btn-navy btn-sm">Apply</button>
      @if (request()->hasAny(['search', 'sort']))
        <a href="{{ route('admin.customers.index', ['type' => request('type')]) }}" class="btn btn-line btn-sm">Clear</a>
      @endif
    </form>
  </div>

  @if ($customers->isEmpty())
    <div class="empty">
      <b>No customers yet</b>
      Everyone who places an order appears here automatically.
    </div>
  @else
    <table>
      <thead>
        <tr>
          <th>Customer</th><th>Type</th>
          <th class="right">Orders</th><th class="right">Spent</th>
          <th class="right">Refused</th><th class="right">Last order</th>
          <th class="right"></th>
        </tr>
      </thead>
      <tbody>
      @foreach ($customers as $customer)
        @php
          $refusal = \App\Services\CustomerDirectory::refusalRate($customer->delivered_count, $customer->failed_count);
        @endphp
        <tr>
          <td class="cu-name">
            <a href="{{ route('admin.customers.show', $customer->phone) }}">{{ $customer->name ?: 'Unnamed' }}</a>
            @if ($customer->is_blocked)<span class="badge b-out" style="margin-left:6px">Blocked</span>@endif
            <small>{{ $customer->phone }}@if ($customer->email) · {{ $customer->email }}@endif</small>
            @if ($customer->note)<div class="cu-note">{{ Str::limit($customer->note, 70) }}</div>@endif
          </td>
          <td>
            <span class="badge {{ $customer->user_id ? 'b-on' : 'b-off' }}">
              {{ $customer->user_id ? 'Account' : 'Guest' }}
            </span>
          </td>
          <td class="right">{{ $customer->orders_count }}</td>
          <td class="right"><span class="cu-money">৳{{ number_format($customer->spent) }}</span></td>
          <td class="right">
            @if ($refusal === null)
              <span class="sub">—</span>
            @else
              <span class="cu-refuse {{ $refusal >= 40 ? 'bad' : ($refusal >= 20 ? 'watch' : 'good') }}">{{ $refusal }}%</span>
            @endif
          </td>
          <td class="right sub">{{ \Illuminate\Support\Carbon::parse($customer->last_order_at)->format('j M Y') }}</td>
          <td class="right">
            <a href="{{ route('admin.customers.show', $customer->phone) }}" class="btn btn-line btn-sm">Open</a>
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>

    <div class="pager">{{ $customers->links('vendor.pagination.amjr') }}</div>
  @endif
</div>

@if ($accounts->total() > 0)
  <div class="panel" style="margin-top:18px">
    <div class="panel-head">
      <div>
        <h2>{{ $accounts->total() }} accounts with no orders yet</h2>
        <div class="sub">Signed up but never bought. Worth a nudge.</div>
      </div>
    </div>
    <table>
      <thead><tr><th>Name</th><th>Contact</th><th class="right">Joined</th></tr></thead>
      <tbody>
      @foreach ($accounts as $account)
        <tr>
          <td class="cu-name">{{ $account->name }}</td>
          <td class="sub">{{ $account->phone ?: '—' }} · {{ $account->email }}</td>
          <td class="right sub">{{ $account->created_at->format('j M Y') }}</td>
        </tr>
      @endforeach
      </tbody>
    </table>
    <div class="pager">{{ $accounts->links('vendor.pagination.amjr') }}</div>
  </div>
@endif

@endsection
