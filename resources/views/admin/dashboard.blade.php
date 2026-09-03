@extends('admin.layouts.app')
@section('title', 'Dashboard')

@section('content')

<div class="stats">
  <div class="stat">
    <div class="lab">Revenue this month</div>
    <div class="num">৳{{ number_format($revenue) }}</div>
    <div class="foot">{{ $orderCount }} orders, average ৳{{ number_format($avgOrder) }}</div>
  </div>
  <div class="stat">
    <div class="lab">Gross profit this month</div>
    <div class="num">৳{{ number_format($profit) }}</div>
    <div class="foot">
      @if ($revenue > 0)
        {{ (int) round($profit / $revenue * 100) }}% margin
      @else
        No sales yet this month
      @endif
    </div>
  </div>
  <div class="stat">
    <div class="lab">Stock value at cost</div>
    <div class="num">৳{{ number_format($stockValue) }}</div>
    <div class="foot">
      @if ($missingCost)
        {{ $missingCost }} product{{ $missingCost > 1 ? 's' : '' }} missing a cost
      @else
        Money sitting on the shelf
      @endif
    </div>
  </div>
  <div class="stat accent">
    <div class="lab">Needs you today</div>
    <div class="num">{{ $pending + $toShip }}</div>
    <div class="foot">{{ $pending }} to confirm, {{ $toShip }} to ship</div>
  </div>
</div>

@if ($stale)
  <div class="note" style="margin-bottom:22px">
    {{ $stale }} order{{ $stale > 1 ? 's have' : ' has' }} been marked shipped for more than three days
    with no delivery confirmation. Worth chasing the courier.
    <a href="{{ route('admin.orders.index', ['status' => 'shipped']) }}">See them</a>
  </div>
@endif

@php $peak = max(1, collect($chart)->max('total')); @endphp
<div class="panel" style="margin-bottom:18px">
  <div class="panel-head">
    <h2>Last 14 days</h2>
    <span class="sub">Revenue, excluding cancelled orders</span>
  </div>
  <div class="panel-body">
    <div class="bars">
      @foreach ($chart as $day)
        <div class="bar-col" title="{{ $day['label'] }} — ৳{{ number_format($day['total']) }}">
          <div class="bar" style="height:{{ max(2, round($day['total'] / $peak * 100)) }}%"></div>
          <span class="bar-lab">{{ $day['label'] }}</span>
        </div>
      @endforeach
    </div>
  </div>
</div>

<div class="cols">

  <div class="panel">
    <div class="panel-head">
      <h2>Recent orders</h2>
      <a href="{{ route('admin.orders.index') }}" class="btn btn-line btn-sm">All orders</a>
    </div>
    @if ($recentOrders->isEmpty())
      <div class="empty"><b>No orders yet</b>They will appear here as soon as one comes in.</div>
    @else
      <table>
        <thead><tr><th>Order</th><th>Customer</th><th class="right">Total</th><th>Status</th></tr></thead>
        <tbody>
        @foreach ($recentOrders as $order)
          <tr>
            <td>
              <a href="{{ route('admin.orders.show', $order) }}"><strong>{{ $order->order_number }}</strong></a>
              <div class="sub">{{ $order->created_at->diffForHumans() }}</div>
            </td>
            <td>{{ $order->customer_name }}<div class="sub">{{ $order->customer_phone }}</div></td>
            <td class="right">৳{{ number_format($order->total) }}</td>
            <td><span class="badge status-{{ $order->status }}">{{ $order->status_label }}</span></td>
          </tr>
        @endforeach
        </tbody>
      </table>
    @endif
  </div>

  <div class="panel">
    <div class="panel-head">
      <h2>Top earners, 30 days</h2>
      <span class="sub">By profit, not units</span>
    </div>
    @if ($topProducts->isEmpty())
      <div class="empty"><b>Nothing sold yet</b>This ranks by profit once orders come in.</div>
    @else
      <table>
        <thead><tr><th>Product</th><th class="right">Units</th><th class="right">Profit</th></tr></thead>
        <tbody>
        @foreach ($topProducts as $row)
          <tr>
            <td>{{ $row->name }}<div class="sub">৳{{ number_format($row->revenue) }} revenue</div></td>
            <td class="right">{{ $row->units }}</td>
            <td class="right"><strong>৳{{ number_format($row->profit) }}</strong></td>
          </tr>
        @endforeach
        </tbody>
      </table>
    @endif
  </div>

</div>

@if ($lowStock->isNotEmpty() || $outOfStock)
  <div class="panel" style="margin-top:18px">
    <div class="panel-head">
      <h2>Running low</h2>
      <a href="{{ route('admin.products.index', ['status' => 'low']) }}" class="btn btn-line btn-sm">View all</a>
    </div>
    @if ($lowStock->isEmpty())
      <div class="empty"><b>{{ $outOfStock }} out of stock</b>Nothing else is running low.</div>
    @else
      <table>
        <thead><tr><th>Product</th><th>SKU</th><th class="right">Stock left</th></tr></thead>
        <tbody>
        @foreach ($lowStock as $product)
          <tr>
            <td><a href="{{ route('admin.products.edit', $product) }}"><strong>{{ $product->name }}</strong></a></td>
            <td class="sub">{{ $product->sku }}</td>
            <td class="right"><span class="badge b-low">{{ $product->total_stock }} left</span></td>
          </tr>
        @endforeach
        </tbody>
      </table>
    @endif
  </div>
@endif

@endsection
