@extends('admin.layouts.app')
@section('title', 'Dashboard')

@section('content')
@php
  $peak      = max(1, collect($chart)->max('total'));
  $fortnight = collect($chart)->sum('total');
  $bestDay   = collect($chart)->sortByDesc('total')->first();
  $margin    = $revenue > 0 ? (int) round($profit / $revenue * 100) : null;
  $topProfit = max(1, (float) $topProducts->max('profit'));
  $waiting   = $pending + $toShip;
@endphp

<div class="dash">

  {{-- welcome strip --}}
  <div class="dash-hero">
    <div class="dash-hero-txt">
      <span class="dash-eyebrow">{{ now()->format('F Y') }}</span>
      <h2>Welcome back, {{ explode(' ', trim(auth()->user()->name))[0] }}</h2>
      <p>
        @if ($waiting > 0)
          {{ $waiting }} order{{ $waiting > 1 ? 's are' : ' is' }} waiting for you — {{ $pending }} to confirm, {{ $toShip }} to ship.
        @else
          Nothing is waiting on you right now. Every order is moving.
        @endif
      </p>
    </div>
    <div class="dash-hero-act">
      <a href="{{ route('admin.orders.index') }}" class="dash-btn dash-btn-gold">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><path d="M3 6h18M16 10a4 4 0 0 1-8 0"/></svg>
        View orders
      </a>
      <a href="{{ route('admin.reports.index') }}" class="dash-btn dash-btn-ghost">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/></svg>
        Reports
      </a>
      <a href="{{ route('admin.products.create') }}" class="dash-btn dash-btn-ghost">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
        Add product
      </a>
    </div>
  </div>

  {{-- headline numbers --}}
  <div class="dash-stats">
    <div class="dash-stat c-blue">
      <span class="dash-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 17l6-6 4 4 8-8"/><path d="M15 7h6v6"/></svg></span>
      <div class="dash-lab">Revenue this month</div>
      <div class="dash-num">৳{{ number_format($revenue) }}</div>
      <div class="dash-foot">{{ $orderCount }} order{{ $orderCount === 1 ? '' : 's' }} · average ৳{{ number_format($avgOrder) }}</div>
    </div>

    <div class="dash-stat c-green">
      <span class="dash-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M15 9.5c-.6-.9-1.7-1.5-3-1.5-1.9 0-3 1-3 2.2 0 3 6 1.3 6 4.3 0 1.3-1.3 2.3-3 2.3-1.4 0-2.6-.6-3.2-1.6M12 6.5V8m0 8.5V18"/></svg></span>
      <div class="dash-lab">Gross profit this month</div>
      <div class="dash-num">৳{{ number_format($profit) }}</div>
      <div class="dash-foot">
        @if ($margin !== null)
          <span class="dash-pill">{{ $margin }}% margin</span>
        @else
          No sales yet this month
        @endif
      </div>
    </div>

    <div class="dash-stat c-violet">
      <span class="dash-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3.5 8 12 3.5 20.5 8v8L12 20.5 3.5 16z"/><path d="M3.5 8 12 12.5 20.5 8M12 12.5v8"/></svg></span>
      <div class="dash-lab">Stock value at cost</div>
      <div class="dash-num">৳{{ number_format($stockValue) }}</div>
      <div class="dash-foot">
        @if ($missingCost)
          {{ $missingCost }} product{{ $missingCost > 1 ? 's' : '' }} missing a cost
        @else
          Money sitting on the shelf
        @endif
      </div>
    </div>

    <a href="{{ route('admin.orders.index') }}" class="dash-stat c-gold">
      <span class="dash-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 8-3 8h18s-3-1-3-8"/><path d="M13.7 20a2 2 0 0 1-3.4 0"/></svg></span>
      <div class="dash-lab">Needs you today</div>
      <div class="dash-num">{{ $waiting }}</div>
      <div class="dash-foot">{{ $pending }} to confirm · {{ $toShip }} to ship</div>
    </a>
  </div>

  {{-- catalogue at a glance --}}
  <div class="dash-chips">
    <a href="{{ route('admin.products.index') }}" class="dash-chip"><b>{{ $productCount }}</b> products</a>
    <a href="{{ route('admin.categories.index') }}" class="dash-chip"><b>{{ $categoryCount }}</b> categories</a>
    <a href="{{ route('admin.brands.index') }}" class="dash-chip"><b>{{ $brandCount }}</b> brands</a>
    @if ($outOfStock)
      <a href="{{ route('admin.products.index', ['status' => 'low']) }}" class="dash-chip is-bad"><b>{{ $outOfStock }}</b> out of stock</a>
    @endif
    @if ($lowStock->isNotEmpty())
      <a href="{{ route('admin.products.index', ['status' => 'low']) }}" class="dash-chip is-warn"><b>{{ $lowStock->count() }}{{ $lowStock->count() >= 6 ? '+' : '' }}</b> running low</a>
    @endif
  </div>

  @if ($stale)
    <div class="note" style="margin-bottom:18px">
      {{ $stale }} order{{ $stale > 1 ? 's have' : ' has' }} been marked shipped for more than three days
      with no delivery confirmation. Worth chasing the courier.
      <a href="{{ route('admin.orders.index', ['status' => 'shipped']) }}">See them</a>
    </div>
  @endif

  {{-- revenue chart --}}
  <div class="panel dash-panel" style="margin-bottom:18px">
    <div class="panel-head">
      <div>
        <h2>Last 14 days</h2>
        <span class="sub">Revenue, excluding cancelled orders</span>
      </div>
      <div class="dash-chart-sum">
        <div><span>Total</span><b>৳{{ number_format($fortnight) }}</b></div>
        @if ($fortnight > 0)
          <div><span>Best day</span><b>{{ $bestDay['label'] }} · ৳{{ number_format($bestDay['total']) }}</b></div>
        @endif
      </div>
    </div>
    <div class="panel-body">
      <div class="dash-chart">
        <div class="dash-grid" aria-hidden="true"><i></i><i></i><i></i><i></i></div>
        <div class="dash-bars">
          @foreach ($chart as $day)
            <div class="dash-col {{ $loop->last ? 'is-today' : '' }} {{ $day['total'] > 0 ? '' : 'is-zero' }}">
              <span class="dash-tip">{{ $day['label'] }}<b>৳{{ number_format($day['total']) }}</b></span>
              <div class="dash-bar" style="height:{{ max(2, round($day['total'] / $peak * 100)) }}%"></div>
              <span class="dash-day">{{ $day['label'] }}</span>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>

  <div class="cols">

    <div class="panel dash-panel">
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
              <td>
                <div class="dash-who">
                  <span class="dash-av dash-av-{{ $order->id % 5 }}">{{ mb_strtoupper(mb_substr(trim($order->customer_name), 0, 1)) }}</span>
                  <span>{{ $order->customer_name }}<div class="sub">{{ $order->customer_phone }}</div></span>
                </div>
              </td>
              <td class="right"><strong>৳{{ number_format($order->total) }}</strong></td>
              <td><span class="badge status-{{ $order->status }}">{{ $order->status_label }}</span></td>
            </tr>
          @endforeach
          </tbody>
        </table>
      @endif
    </div>

    <div class="panel dash-panel">
      <div class="panel-head">
        <div>
          <h2>Top earners, 30 days</h2>
          <span class="sub">By profit, not units</span>
        </div>
      </div>
      @if ($topProducts->isEmpty())
        <div class="empty"><b>Nothing sold yet</b>This ranks by profit once orders come in.</div>
      @else
        <div class="dash-rank">
          @foreach ($topProducts as $row)
            <div class="dash-rank-row">
              <span class="dash-rank-n n{{ min($loop->iteration, 4) }}">{{ $loop->iteration }}</span>
              <div class="dash-rank-main">
                <div class="dash-rank-top">
                  <b>{{ $row->name }}</b>
                  <strong>৳{{ number_format($row->profit) }}</strong>
                </div>
                <div class="dash-meter"><i style="width:{{ max(3, round(max(0, (float) $row->profit) / $topProfit * 100)) }}%"></i></div>
                <div class="sub">{{ $row->units }} sold · ৳{{ number_format($row->revenue) }} revenue</div>
              </div>
            </div>
          @endforeach
        </div>
      @endif
    </div>

  </div>

  @if ($lowStock->isNotEmpty() || $outOfStock)
    <div class="panel dash-panel dash-low" style="margin-top:18px">
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

</div>
@endsection
