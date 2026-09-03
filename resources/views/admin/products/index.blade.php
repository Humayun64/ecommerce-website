@extends('admin.layouts.app')
@section('title', 'Products')

@section('content')
<div class="panel">
  <div class="panel-head">
    <h2>{{ $products->total() }} products</h2>
    <a href="{{ route('admin.products.create') }}" class="btn btn-gold btn-sm">Add product</a>
  </div>

  <div class="panel-body" style="border-bottom:1px solid var(--line)">
    <form method="GET" class="filters">
      <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name or SKU">
      <select name="brand">
        <option value="">All brands</option>
        @foreach ($brands as $brand)
          <option value="{{ $brand->id }}" @selected(request('brand') == $brand->id)>{{ $brand->name }}</option>
        @endforeach
      </select>
      <select name="category">
        <option value="">All categories</option>
        @foreach ($categories as $category)
          <option value="{{ $category->id }}" @selected(request('category') == $category->id)>{{ $category->full_name }}</option>
        @endforeach
      </select>
      <select name="status">
        <option value="">Any status</option>
        <option value="low" @selected(request('status') === 'low')>Low stock</option>
        <option value="inactive" @selected(request('status') === 'inactive')>Hidden</option>
      </select>
      <button class="btn btn-navy btn-sm">Filter</button>
      @if (request()->hasAny(['search', 'brand', 'category', 'status']))
        <a href="{{ route('admin.products.index') }}" class="btn btn-line btn-sm">Clear</a>
      @endif
    </form>
  </div>

  @if ($products->isEmpty())
    <div class="empty">
      <b>No products match</b>
      Try clearing the filters, or add a new product.
    </div>
  @else
    <table>
      <thead>
        <tr>
          <th>Product</th><th>Brand</th><th>Category</th>
          <th class="right">Price</th><th class="right">Margin</th>
          <th class="right">Stock</th><th>Status</th><th class="right">Actions</th>
        </tr>
      </thead>
      <tbody>
      @foreach ($products as $product)
        <tr>
          <td>
            <div class="namecell">
              @if ($product->primaryImage)
                <img src="{{ $product->primaryImage->url }}" alt="" class="thumb">
              @else
                <div class="thumb-empty">No<br>photo</div>
              @endif
              <div>
                <strong>{{ $product->name }}</strong>
                <div class="sub">
                  {{ $product->sku }}
                  @if ($product->has_variants) · {{ $product->variants->count() }} sizes @endif
                </div>
              </div>
            </div>
          </td>
          <td class="sub">{{ $product->brand?->name ?? '—' }}</td>
          <td class="sub">{{ $product->category?->name ?? '—' }}</td>
          <td class="right">
            @if ($product->has_variants)
              <span class="sub">from</span> ৳{{ number_format($product->display_price) }}
            @else
              ৳{{ number_format($product->price) }}
              @if ($product->compare_price)
                <div class="sub" style="text-decoration:line-through">৳{{ number_format($product->compare_price) }}</div>
              @endif
            @endif
          </td>
          <td class="right">
            @if ($product->margin_percent === null)
              <span class="sub">Set cost</span>
            @else
              <span class="badge {{ $product->margin_percent < 15 ? 'b-low' : 'b-on' }}">{{ $product->margin_percent }}%</span>
            @endif
          </td>
          <td class="right">{{ $product->total_stock }}</td>
          <td>
            @if ($product->is_out_of_stock)
              <span class="badge b-out">Out of stock</span>
            @elseif ($product->is_low_stock)
              <span class="badge b-low">Low</span>
            @else
              <span class="badge b-on">In stock</span>
            @endif
            @unless ($product->is_active)<span class="badge b-off">Hidden</span>@endunless
            @if ($product->is_featured)<span class="badge b-gold">Featured</span>@endif
          </td>
          <td class="right">
            <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-line btn-sm">Edit</a>
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>

    <div class="pager">{{ $products->links('vendor.pagination.amjr') }}</div>
  @endif
</div>
@endsection
