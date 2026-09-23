@extends('admin.layouts.app')
@section('title', 'Blog topics')

@section('content')

<div class="tabs">
  <a href="{{ route('admin.posts.index') }}" class="tab">Posts</a>
  <a href="{{ route('admin.blog-categories.index') }}" class="tab on">Topics</a>
</div>

<div class="form-grid">
<div class="form-main">
  <div class="panel">
    <div class="panel-head">
      <h2>{{ $categories->count() }} topics</h2>
    </div>

    @if ($categories->isEmpty())
      <div class="empty"><b>No topics yet</b>Add one on the right — Skincare advice, Product guides, Company news.</div>
    @else
      <table>
        <thead><tr><th>Topic</th><th>Address</th><th class="right">Posts</th><th>Status</th><th class="right">Actions</th></tr></thead>
        <tbody>
        @foreach ($categories as $category)
          <tr>
            <td>
              <form method="POST" action="{{ route('admin.blog-categories.update', $category) }}" style="display:flex;gap:6px;align-items:center;flex-wrap:wrap">
                @csrf @method('PATCH')
                <input type="text" name="name" value="{{ $category->name }}" required
                       style="max-width:170px;border:1.5px solid var(--line);border-radius:3px;padding:7px 9px;font:inherit;font-size:13.5px">
                <input type="text" name="description" value="{{ $category->description }}" placeholder="One line"
                       style="max-width:230px;border:1.5px solid var(--line);border-radius:3px;padding:7px 9px;font:inherit;font-size:13.5px">
                <input type="number" name="sort_order" value="{{ $category->sort_order }}"
                       style="width:64px;border:1.5px solid var(--line);border-radius:3px;padding:7px 9px;font:inherit;font-size:13.5px">
                <label class="check"><input type="checkbox" name="is_active" value="1" @checked($category->is_active)> On</label>
                <button class="btn btn-line btn-sm">Save</button>
              </form>
            </td>
            <td class="sub">/blog?category={{ $category->slug }}</td>
            <td class="right">{{ $category->posts_count }}</td>
            <td><span class="badge {{ $category->is_active ? 'b-on' : 'b-off' }}">{{ $category->is_active ? 'Live' : 'Hidden' }}</span></td>
            <td class="right">
              <form method="POST" action="{{ route('admin.blog-categories.destroy', $category) }}"
                    onsubmit="return confirm('Remove {{ $category->name }}?')">
                @csrf @method('DELETE')
                <button class="btn btn-danger btn-sm">Remove</button>
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
  <div class="panel">
    <div class="panel-head"><h2>Add a topic</h2></div>
    <div class="panel-body">
      <form method="POST" action="{{ route('admin.blog-categories.store') }}">
        @csrf
        <div class="field">
          <label for="name">Name</label>
          <input type="text" id="name" name="name" required placeholder="Skincare advice">
        </div>
        <div class="field">
          <label for="description">One-line description</label>
          <input type="text" id="description" name="description" placeholder="Routines, ingredients and what actually works">
        </div>
        <div class="field">
          <label for="sort_order">Order</label>
          <input type="number" id="sort_order" name="sort_order" value="0" min="0">
        </div>
        <div class="field">
          <label class="check"><input type="checkbox" name="is_active" value="1" checked> Show on the blog</label>
        </div>
        <button type="submit" class="btn btn-gold" style="width:100%">Add topic</button>
      </form>
    </div>
  </div>
</div>
</div>
@endsection
