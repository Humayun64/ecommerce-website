@extends('admin.layouts.app')
@section('title', 'Messages')

@section('content')

<style>
.ms-pill{display:inline-block;font-size:11.5px;font-weight:700;border-radius:999px;padding:3px 11px;white-space:nowrap}
.ms-new{background:#FDF3DC;color:#8A6410}
.ms-read{background:#E8EDF6;color:var(--navy)}
.ms-replied{background:#E4F3EA;color:#14663E}
.ms-spam{background:#FBE7E4;color:#9E3423}
.ms-who{font-weight:600}
.ms-who small{display:block;font-weight:400;color:var(--ink-mute);font-size:12.5px;margin-top:2px}
.ms-body{color:var(--ink-mute);font-size:13px;line-height:1.55;max-width:420px}
.ms-body b{display:block;color:var(--ink);font-weight:600;font-size:13.5px;margin-bottom:3px}
.ms-unread td{background:#FFFDF5}
.ms-unread .ms-who a{font-weight:700}
.ms-dot{display:inline-block;width:7px;height:7px;border-radius:50%;background:var(--gold);margin-right:7px;vertical-align:middle}
</style>

<div class="tabs" style="margin-bottom:16px">
  <a href="{{ route('admin.messages.index') }}" class="tab {{ ! request('status') ? 'on' : '' }}">
    Inbox @if ($counts['new'])<span>{{ $counts['new'] }}</span>@endif
  </a>
  <a href="{{ route('admin.messages.index', ['status' => 'new']) }}" class="tab {{ request('status') === 'new' ? 'on' : '' }}">
    Unread <span>{{ $counts['new'] }}</span>
  </a>
  <a href="{{ route('admin.messages.index', ['status' => 'replied']) }}" class="tab {{ request('status') === 'replied' ? 'on' : '' }}">
    Replied <span>{{ $counts['replied'] }}</span>
  </a>
  <a href="{{ route('admin.messages.index', ['status' => 'spam']) }}" class="tab {{ request('status') === 'spam' ? 'on' : '' }}">
    Spam <span>{{ $counts['spam'] }}</span>
  </a>
</div>

<div class="panel">
  <div class="panel-head">
    <div>
      <h2>{{ $rows->total() }} {{ Str::plural('message', $rows->total()) }}</h2>
      <div class="sub">
        @if (request('status') === 'spam')
          Junk the form caught. Nothing here was shown to you as a real message.
        @else
          Everything sent through the contact page. Spam is filtered out of this list.
        @endif
      </div>
    </div>

    <form method="GET" class="filters">
      @if (request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
      <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, phone, email or text">
      <button class="btn btn-navy btn-sm">Search</button>
      @if (request('search'))
        <a href="{{ route('admin.messages.index', ['status' => request('status')]) }}" class="btn btn-line btn-sm">Clear</a>
      @endif
    </form>
  </div>

  @if ($rows->isEmpty())
    <div class="empty">
      <b>Nothing here</b>
      @if (request()->hasAny(['search', 'status']))
        Nothing matches that.
      @else
        Messages from the contact page land here.
      @endif
    </div>
  @else
    <table>
      <thead>
        <tr>
          <th>From</th>
          <th>Message</th>
          <th>Status</th>
          <th class="right">Received</th>
          <th class="right"></th>
        </tr>
      </thead>
      <tbody>
      @foreach ($rows as $row)
        <tr class="{{ $row->is_unread ? 'ms-unread' : '' }}">
          <td class="ms-who">
            @if ($row->is_unread)<span class="ms-dot"></span>@endif
            <a href="{{ route('admin.messages.show', $row) }}">{{ $row->name }}</a>
            <small>{{ $row->phone }}@if ($row->email) · {{ $row->email }}@endif</small>
          </td>

          <td class="ms-body">
            @if ($row->topic)<b>{{ $row->topic }}</b>@endif
            {{ Str::limit($row->message, 110) }}
            @if ($row->order_number)
              <small style="display:block;margin-top:4px">Order {{ $row->order_number }}</small>
            @endif
          </td>

          <td><span class="ms-pill ms-{{ $row->status }}">{{ $row->status_label }}</span></td>

          <td class="right sub">{{ $row->created_at->diffForHumans() }}</td>

          <td class="right">
            <a href="{{ route('admin.messages.show', $row) }}" class="btn btn-line btn-sm">Open</a>
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>

    <div class="pager">{{ $rows->links('vendor.pagination.amjr') }}</div>
  @endif
</div>

@endsection
