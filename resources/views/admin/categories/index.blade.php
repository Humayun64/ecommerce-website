@extends('admin.layouts.app')
@section('title', 'Categories')

@section('content')
<div class="panel">
  <div class="panel-head">
    <h2>Category tree</h2>
    <a href="{{ route('admin.categories.create') }}" class="btn btn-gold btn-sm">Add category</a>
  </div>

  @if ($categories->isEmpty())
    <div class="empty"><b>No categories yet</b>Add a parent category such as Skincare, then add sub-categories under it.</div>
  @else
    <table>
      <thead>
        <tr><th>Name</th><th>Slug</th><th class="right">Products</th><th>Status</th><th class="right">Actions</th></tr>
      </thead>
      <tbody>
      @foreach ($categories as $parent)
        <tr>
          <td><strong>{{ $parent->name }}</strong></td>
          <td class="sub">{{ $parent->slug }}</td>
          <td class="right">{{ $parent->products->count() }}</td>
          <td><span class="badge {{ $parent->is_active ? 'b-on' : 'b-off' }}">{{ $parent->is_active ? 'Active' : 'Hidden' }}</span></td>
          <td class="right">
            <a href="{{ route('admin.categories.edit', $parent) }}" class="btn btn-line btn-sm">Edit</a>
            <form method="POST" action="{{ route('admin.categories.destroy', $parent) }}" style="display:inline"
                  onsubmit="return confirm('Delete {{ $parent->name }}?')">
              @csrf @method('DELETE')
              <button class="btn btn-danger btn-sm">Delete</button>
            </form>
          </td>
        </tr>
        @foreach ($parent->children as $child)
          <tr>
            <td class="child-name">{{ $child->name }}</td>
            <td class="sub">{{ $child->slug }}</td>
            <td class="right">{{ $child->products->count() }}</td>
            <td><span class="badge {{ $child->is_active ? 'b-on' : 'b-off' }}">{{ $child->is_active ? 'Active' : 'Hidden' }}</span></td>
            <td class="right">
              <a href="{{ route('admin.categories.edit', $child) }}" class="btn btn-line btn-sm">Edit</a>
              <form method="POST" action="{{ route('admin.categories.destroy', $child) }}" style="display:inline"
                    onsubmit="return confirm('Delete {{ $child->name }}?')">
                @csrf @method('DELETE')
                <button class="btn btn-danger btn-sm">Delete</button>
              </form>
            </td>
          </tr>
        @endforeach
      @endforeach
      </tbody>
    </table>
  @endif
</div>
@endsection
