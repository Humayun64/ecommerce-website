@extends('admin.layouts.app')
@section('title', 'Reviews')

@section('content')

<style>
.rv-stars{color:var(--gold);letter-spacing:1px;font-size:14px}
.rv-body{max-width:520px;font-size:13.5px;line-height:1.55;color:var(--ink-mute)}
.rv-body strong{display:block;color:var(--ink);font-size:14px;margin-bottom:3px}
.badge.rv-pending{background:#FCF6E8;color:var(--gold-deep)}
.badge.rv-approved{background:#E5F3EB;color:var(--ok)}
.badge.rv-rejected{background:#EEF0F3;color:var(--ink-mute)}
.rv-actions{display:flex;gap:6px;flex-wrap:wrap;justify-content:flex-end}
.rv-reply{margin-top:10px}
.rv-reply textarea{width:100%;border:1.5px solid var(--line);border-radius:3px;padding:8px 10px;font:inherit;font-size:13px;min-height:54px}
.rv-reply textarea:focus{border-color:var(--navy);outline:none}
</style>

<div class="tabs">
  <a href="{{ route('admin.reviews.index') }}" class="tab {{ ! request('status') ? 'on' : '' }}">
    All <span>{{ $counts->sum() }}</span>
  </a>
  @foreach (\App\Models\Review::STATUSES as $key => $label)
    <a href="{{ route('admin.reviews.index', ['status' => $key]) }}" class="tab {{ request('status') === $key ? 'on' : '' }}">
      {{ $label }} <span>{{ $counts[$key] ?? 0 }}</span>
    </a>
  @endforeach
</div>

<div class="panel">
  <div class="panel-head">
    <h2>{{ $reviews->total() }} reviews</h2>
    <form method="GET" class="filters">
      @if (request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
      <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or words in the review">
      <button class="btn btn-navy btn-sm">Search</button>
      @if (request('search'))
        <a href="{{ route('admin.reviews.index', ['status' => request('status')]) }}" class="btn btn-line btn-sm">Clear</a>
      @endif
    </form>
  </div>

  @if ($reviews->isEmpty())
    <div class="empty">
      <b>Nothing here</b>
      Reviews customers write appear here for you to approve before they go public.
    </div>
  @else
    <table>
      <thead>
        <tr><th>Review</th><th>Product</th><th>Status</th><th class="right">Actions</th></tr>
      </thead>
      <tbody>
      @foreach ($reviews as $review)
        <tr>
          <td>
            <div class="rv-stars">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</div>
            <div class="rv-body">
              @if ($review->title)<strong>{{ $review->title }}</strong>@endif
              {{ $review->body }}
            </div>
            <div class="sub" style="margin-top:6px">
              {{ $review->reviewer_name }}
              @if ($review->reviewer_phone) · {{ $review->reviewer_phone }}@endif
              · {{ $review->created_at->format('j M Y') }}
              @if ($review->is_verified)
                <span class="badge b-on" style="margin-left:6px">Verified purchase</span>
              @endif
            </div>
          </td>
          <td class="sub">
            @if ($review->product)
              <a href="{{ route('shop.product', $review->product) }}" target="_blank">{{ $review->product->name }}</a>
            @else
              —
            @endif
          </td>
          <td><span class="badge rv-{{ $review->status }}">{{ \App\Models\Review::STATUSES[$review->status] ?? $review->status }}</span></td>
          <td class="right">
            <form method="POST" action="{{ route('admin.reviews.update', $review) }}">
              @csrf @method('PATCH')
              <div class="rv-actions">
                @if ($review->status !== 'approved')
                  <button class="btn btn-gold btn-sm" name="status" value="approved">Publish</button>
                @endif
                @if ($review->status !== 'rejected')
                  <button class="btn btn-line btn-sm" name="status" value="rejected">Reject</button>
                @endif
              </div>
              <div class="rv-reply">
                <textarea name="admin_reply" placeholder="Reply publicly (optional)">{{ $review->admin_reply }}</textarea>
                <button class="btn btn-line btn-sm" name="status" value="{{ $review->status }}" style="margin-top:6px">Save reply</button>
              </div>
            </form>

            <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}"
                  onsubmit="return confirm('Delete this review for good?')" style="margin-top:6px">
              @csrf @method('DELETE')
              <button class="btn btn-danger btn-sm">Delete</button>
            </form>
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>

    <div class="pager">{{ $reviews->links('vendor.pagination.amjr') }}</div>
  @endif
</div>
@endsection
