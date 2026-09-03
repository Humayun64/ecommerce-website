@extends('admin.layouts.app')
@section('title', 'Orders')

@section('content')

<div class="tabs">
  <a href="{{ route('admin.orders.index') }}" class="tab {{ ! request('status') ? 'on' : '' }}">
    All <span>{{ $counts->sum() }}</span>
  </a>
  @foreach (\App\Models\Order::STATUSES as $key => $label)
    <a href="{{ route('admin.orders.index', ['status' => $key]) }}" class="tab {{ request('status') === $key ? 'on' : '' }}">
      {{ $label }} <span>{{ $counts[$key] ?? 0 }}</span>
    </a>
  @endforeach
</div>

<div class="panel">
  <div class="panel-head">
    <h2>{{ $orders->total() }} orders</h2>
    <a href="{{ route('admin.orders.create') }}" class="btn btn-gold btn-sm">New order by hand</a>
  </div>

  <div class="panel-body" style="border-bottom:1px solid var(--line)">
    <form method="GET" class="filters">
      @if (request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
      <input type="text" name="search" value="{{ request('search') }}" placeholder="Order number, name or phone">
      <input type="date" name="from" value="{{ request('from') }}">
      <input type="date" name="to" value="{{ request('to') }}">
      <button class="btn btn-navy btn-sm">Filter</button>
      @if (request()->hasAny(['search', 'from', 'to']))
        <a href="{{ route('admin.orders.index', ['status' => request('status')]) }}" class="btn btn-line btn-sm">Clear</a>
      @endif
    </form>
  </div>

  @if ($orders->isEmpty())
    <div class="empty">
      <b>No orders here</b>
      Orders placed on the storefront land here automatically.
    </div>
  @else
    <table>
      <thead>
        <tr>
          <th>Order</th><th>Customer</th><th>Area</th>
          <th class="right">Items</th><th class="right">Total</th>
          <th>Status</th><th class="right">Placed</th>
        </tr>
      </thead>
      <tbody>
      @foreach ($orders as $order)
        <tr>
          <td>
            <a href="{{ route('admin.orders.show', $order) }}"><strong>{{ $order->order_number }}</strong></a>
            <div class="sub">{{ $order->payment_method === 'cod' ? 'Cash on delivery' : 'Online' }}</div>
          </td>
          <td>
            {{ $order->customer_name }}
            <div class="sub">{{ $order->customer_phone }}</div>
          </td>
          <td class="sub">{{ $order->shipping_zone_name ?? '—' }}</td>
          <td class="right">{{ $order->items_count }}</td>
          <td class="right"><strong>৳{{ number_format($order->total) }}</strong></td>
          <td><span class="badge status-{{ $order->status }}">{{ $order->status_label }}</span></td>
          <td class="right sub">{{ $order->created_at->format('j M, g:ia') }}</td>
        </tr>
      @endforeach
      </tbody>
    </table>

    <div class="pager">{{ $orders->links('vendor.pagination.amjr') }}</div>
  @endif
</div>
@endsection
