@extends('admin.layouts.app')
@section('title', 'Blog posts')

@section('content')

<style>
.bp-row{display:flex;gap:13px;align-items:flex-start}
.bp-thumb{width:64px;height:48px;border-radius:3px;object-fit:cover;flex-shrink:0;border:1px solid var(--line)}
.bp-thumb-empty{width:64px;height:48px;border-radius:3px;background:var(--paper);border:1px solid var(--line);flex-shrink:0;display:grid;place-items:center;color:#B8BEC8;font-size:10px}
.badge.st-draft{background:#EEF0F3;color:var(--ink-mute)}
.badge.st-published{background:#E5F3EB;color:var(--ok)}
.badge.st-scheduled{background:#FCF6E8;color:var(--gold-deep)}
</style>

<div class="tabs">
  <a href="{{ route('admin.posts.index') }}" class="tab {{ ! request('status') ? 'on' : '' }}">All <span>{{ $counts->sum() }}</span></a>
  @foreach (\App\Models\Post::STATUSES as $key => $label)
    <a href="{{ route('admin.posts.index', ['status' => $key]) }}" class="tab {{ request('status') === $key ? 'on' : '' }}">
      {{ $label }} <span>{{ $counts[$key] ?? 0 }}</span>
    </a>
  @endforeach
  <a href="{{ route('admin.blog-categories.index') }}" class="tab">Topics</a>
</div>

<div class="panel">
  <div class="panel-head">
    <h2>{{ $posts->total() }} posts</h2>
    <a href="{{ route('admin.posts.create') }}" class="btn btn-gold btn-sm">Write a post</a>
  </div>

  <div class="panel-body" style="border-bottom:1px solid var(--line)">
    <form method="GET" class="filters">
      @if (request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
      <input type="text" name="search" value="{{ request('search') }}" placeholder="Search title or text">
      <select name="category">
        <option value="">All topics</option>
        @foreach ($categories as $category)
          <option value="{{ $category->id }}" @selected(request('category') == $category->id)>{{ $category->name }}</option>
        @endforeach
      </select>
      <button class="btn btn-navy btn-sm">Filter</button>
      @if (request()->hasAny(['search', 'category']))
        <a href="{{ route('admin.posts.index', ['status' => request('status')]) }}" class="btn btn-line btn-sm">Clear</a>
      @endif
    </form>
  </div>

  @if ($posts->isEmpty())
    <div class="empty">
      <b>No posts yet</b>
      A post about spotting counterfeit skincare would bring the right people to the shop.
    </div>
  @else
    <table>
      <thead><tr><th>Post</th><th>Topic</th><th>Status</th><th class="right">Views</th><th class="right">Actions</th></tr></thead>
      <tbody>
      @foreach ($posts as $post)
        <tr>
          <td>
            <div class="bp-row">
              @if ($post->cover_url)
                <img src="{{ $post->cover_url }}" alt="" class="bp-thumb">
              @else
                <div class="bp-thumb-empty">No<br>cover</div>
              @endif
              <div>
                <strong>{{ $post->title }}</strong>
                @if ($post->is_featured)<span class="badge b-gold" style="margin-left:6px">Featured</span>@endif
                <div class="sub">
                  /blog/{{ $post->slug }} · {{ $post->reading_minutes }} min read
                  @if ($post->author) · {{ $post->author->name }} @endif
                </div>
              </div>
            </div>
          </td>
          <td class="sub">{{ $post->category?->name ?? '—' }}</td>
          <td>
            <span class="badge st-{{ $post->status }}">{{ \App\Models\Post::STATUSES[$post->status] ?? $post->status }}</span>
            @if ($post->published_at)
              <div class="sub">{{ $post->published_at->format('j M Y') }}</div>
            @endif
          </td>
          <td class="right">{{ number_format($post->view_count) }}</td>
          <td class="right">
            <a href="{{ route('blog.show', $post->slug) }}" target="_blank" class="btn btn-line btn-sm">View</a>
            <a href="{{ route('admin.posts.edit', $post) }}" class="btn btn-line btn-sm">Edit</a>
            <form method="POST" action="{{ route('admin.posts.destroy', $post) }}" style="display:inline"
                  onsubmit="return confirm('Delete this post?')">
              @csrf @method('DELETE')
              <button class="btn btn-danger btn-sm">Delete</button>
            </form>
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>

    <div class="pager">{{ $posts->links('vendor.pagination.amjr') }}</div>
  @endif
</div>
@endsection
