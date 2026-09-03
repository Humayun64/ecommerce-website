@extends('admin.layouts.app')
@section('title', 'Brands')

@section('content')
<div class="panel">
  <div class="panel-head">
    <h2>Brands</h2>
    <a href="{{ route('admin.brands.create') }}" class="btn btn-gold btn-sm">Add brand</a>
  </div>

  @if ($brands->isEmpty())
    <div class="empty"><b>No brands yet</b>Add the brands you import so customers can filter by them.</div>
  @else
    <table>
      <thead>
        <tr><th>Brand</th><th>Country</th><th class="right">Products</th><th>Status</th><th class="right">Actions</th></tr>
      </thead>
      <tbody>
      @foreach ($brands as $brand)
        <tr>
          <td>
            <strong>{{ $brand->name }}</strong>
            <div class="sub">{{ $brand->slug }}</div>
          </td>
          <td class="sub">{{ $brand->country ?? '—' }}</td>
          <td class="right">{{ $brand->products_count }}</td>
          <td><span class="badge {{ $brand->is_active ? 'b-on' : 'b-off' }}">{{ $brand->is_active ? 'Active' : 'Hidden' }}</span></td>
          <td class="right">
            <a href="{{ route('admin.brands.edit', $brand) }}" class="btn btn-line btn-sm">Edit</a>
            <form method="POST" action="{{ route('admin.brands.destroy', $brand) }}" style="display:inline"
                  onsubmit="return confirm('Delete {{ $brand->name }}?')">
              @csrf @method('DELETE')
              <button class="btn btn-danger btn-sm">Delete</button>
            </form>
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>
  @endif
</div>
@endsection
