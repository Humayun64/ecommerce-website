@extends('admin.layouts.app')
@section('title', 'Attributes')

@section('content')
<div class="panel">
  <div class="panel-head">
    <h2>Attributes</h2>
    <a href="{{ route('admin.attributes.create') }}" class="btn btn-gold btn-sm">Add attribute</a>
  </div>

  <div class="panel-body" style="border-bottom:1px solid var(--line)">
    <p class="sub" style="margin:0">
      An attribute is an axis a product can vary on — Size, Shade, Capacity. Define the values
      once here and reuse them across products, so you never end up with "30ml", "30 ml" and
      "30ML" as three different things.
    </p>
  </div>

  @if ($attributes->isEmpty())
    <div class="empty"><b>No attributes yet</b>Start with Size, and add the millilitre values you stock.</div>
  @else
    <table>
      <thead><tr><th>Attribute</th><th>Values</th><th class="right">Products</th><th>Filter</th><th class="right">Actions</th></tr></thead>
      <tbody>
      @foreach ($attributes as $attribute)
        <tr>
          <td><strong>{{ $attribute->name }}</strong><div class="sub">{{ $attribute->slug }}</div></td>
          <td>
            @forelse ($attribute->values as $value)
              <span class="badge b-off" style="margin:0 3px 3px 0">{{ $value->value }}</span>
            @empty
              <span class="sub">No values yet</span>
            @endforelse
          </td>
          <td class="right">{{ $attribute->products_count }}</td>
          <td><span class="badge {{ $attribute->is_filterable ? 'b-on' : 'b-off' }}">{{ $attribute->is_filterable ? 'Shown' : 'Hidden' }}</span></td>
          <td class="right">
            <a href="{{ route('admin.attributes.edit', $attribute) }}" class="btn btn-line btn-sm">Edit</a>
            <form method="POST" action="{{ route('admin.attributes.destroy', $attribute) }}" style="display:inline"
                  onsubmit="return confirm('Delete {{ $attribute->name }}?')">
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
