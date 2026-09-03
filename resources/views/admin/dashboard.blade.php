@extends('admin.layouts.app')
@section('title', 'Dashboard')

@section('content')

<div class="stats">
  <div class="stat">
    <div class="lab">Products</div>
    <div class="num">{{ $productCount }}</div>
    <div class="foot">{{ $activeCount }} visible on the storefront</div>
  </div>
  <div class="stat">
    <div class="lab">Stock value at cost</div>
    <div class="num">৳{{ number_format($stockValue) }}</div>
    <div class="foot">
      @if ($missingCost)
        {{ $missingCost }} product{{ $missingCost > 1 ? 's' : '' }} still missing a cost
      @else
        Money currently on the shelf
      @endif
    </div>
  </div>
  <div class="stat">
    <div class="lab">Average margin</div>
    <div class="num">{{ $avgMargin === null ? '—' : $avgMargin . '%' }}</div>
    <div class="foot">
      @if ($avgMargin === null)
        Add cost prices to see this
      @else
        Across products with a cost set
      @endif
    </div>
  </div>
  <div class="stat accent">
    <div class="lab">Needs attention</div>
    <div class="num">{{ $lowStock->count() + $outOfStock }}</div>
    <div class="foot">{{ $lowStock->count() }} low, {{ $outOfStock }} out of stock</div>
  </div>
</div>

@if ($missingCost)
  <div class="note" style="margin-bottom:22px">
    {{ $missingCost }} product{{ $missingCost > 1 ? 's have' : ' has' }} no cost price yet, so profit
    figures are incomplete. Add what you paid on each product's edit page.
  </div>
@endif

<div class="cols">

  <div class="panel">
    <div class="panel-head">
      <h2>Running low</h2>
      <a href="{{ route('admin.products.index', ['status' => 'low']) }}" class="btn btn-line btn-sm">View all</a>
    </div>
    @if ($lowStock->isEmpty())
      <div class="empty"><b>Nothing running low</b>Every product is above its stock threshold.</div>
    @else
      <table>
        <thead><tr><th>Product</th><th>SKU</th><th class="right">Stock left</th></tr></thead>
        <tbody>
        @foreach ($lowStock as $product)
          <tr>
            <td>
              <a href="{{ route('admin.products.edit', $product) }}"><strong>{{ $product->name }}</strong></a>
              <div class="sub">{{ $product->size_label }}</div>
            </td>
            <td class="sub">{{ $product->sku }}</td>
            <td class="right"><span class="badge b-low">{{ $product->total_stock }} left</span></td>
          </tr>
        @endforeach
        </tbody>
      </table>
    @endif
  </div>

  <div class="panel">
    <div class="panel-head"><h2>Thin margins</h2></div>
    @if ($thinMargin->isEmpty())
      <div class="empty">
        <b>No thin margins</b>
        @if ($avgMargin === null) Add cost prices to see this. @else Everything costed is above 15%. @endif
      </div>
    @else
      <table>
        <thead><tr><th>Product</th><th class="right">Profit</th><th class="right">Margin</th></tr></thead>
        <tbody>
        @foreach ($thinMargin as $product)
          <tr>
            <td>
              <a href="{{ route('admin.products.edit', $product) }}">{{ $product->name }}</a>
            </td>
            <td class="right">৳{{ number_format($product->unit_profit) }}</td>
            <td class="right"><span class="badge b-low">{{ $product->margin_percent }}%</span></td>
          </tr>
        @endforeach
        </tbody>
      </table>
    @endif
  </div>

</div>

@endsection
