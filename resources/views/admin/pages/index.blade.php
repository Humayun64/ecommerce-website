@extends('admin.layouts.app')
@section('title', 'Pages')

@section('content')

<style>
.pg-quick{display:flex;gap:8px;flex-wrap:wrap}
.pg-quick a{display:inline-flex;align-items:center;gap:7px;border:1.5px solid var(--line);background:#fff;border-radius:3px;padding:9px 14px;font-size:13.5px;font-weight:600}
.pg-quick a:hover{border-color:var(--navy);color:var(--navy)}
.pg-quick a span{color:var(--gold);font-size:16px;line-height:1}
.pg-url{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:12.5px;color:var(--ink-mute)}
</style>

<div class="panel">
  <div class="panel-head">
    <div>
      <h2>{{ $pages->count() }} pages</h2>
      <div class="sub">About, policies, anything that is not a product.</div>
    </div>
    <a href="{{ route('admin.pages.create') }}" class="btn btn-gold btn-sm">Create a page</a>
  </div>

  @if ($pages->isEmpty())
    <div class="empty">
      <b>No pages yet</b>
      Use a starter below — each one comes filled in with wording written for a cash-on-delivery shop.
    </div>
  @else
    <table>
      <thead><tr><th>Page</th><th>Address</th><th>Status</th><th class="right">Actions</th></tr></thead>
      <tbody>
      @foreach ($pages as $page)
        <tr>
          <td>
            <strong>{{ $page->title }}</strong>
            @if ($page->excerpt)<div class="sub">{{ Str::limit($page->excerpt, 80) }}</div>@endif
          </td>
          <td><span class="pg-url">/{{ $page->slug }}</span></td>
          <td>
            <span class="badge {{ $page->is_published ? 'b-on' : 'b-off' }}">{{ $page->is_published ? 'Live' : 'Draft' }}</span>
            @unless ($page->is_indexable)<span class="badge b-off">No index</span>@endunless
          </td>
          <td class="right">
            <a href="{{ url('/' . $page->slug) }}" target="_blank" class="btn btn-line btn-sm">View</a>
            <a href="{{ route('admin.pages.edit', $page) }}" class="btn btn-line btn-sm">Edit</a>
            <form method="POST" action="{{ route('admin.pages.destroy', $page) }}" style="display:inline"
                  onsubmit="return confirm('Delete {{ $page->title }}?')">
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

@if ($available->isNotEmpty())
  <div class="panel" style="margin-top:18px">
    <div class="panel-head">
      <div>
        <h2>Start from a template</h2>
        <div class="sub">Each opens pre-written, using your store name, phone and return window. Edit before publishing.</div>
      </div>
    </div>
    <div class="panel-body">
      <div class="pg-quick">
        @foreach ($available as $slug => $label)
          <a href="{{ route('admin.pages.create', ['template' => $slug]) }}"><span>+</span> {{ $label }}</a>
        @endforeach
      </div>
    </div>
  </div>
@endif

@endsection
