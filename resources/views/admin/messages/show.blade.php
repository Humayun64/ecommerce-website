@extends('admin.layouts.app')
@section('title', 'Message from ' . $row->name)

@section('content')

<style>
.md-grid{display:grid;grid-template-columns:minmax(0,1fr) 320px;gap:18px;align-items:start}
@media(max-width:1000px){.md-grid{grid-template-columns:1fr}}
.md-pill{display:inline-block;font-size:11.5px;font-weight:700;border-radius:999px;padding:3px 11px}
.ms-new{background:#FDF3DC;color:#8A6410}
.ms-read{background:#E8EDF6;color:var(--navy)}
.ms-replied{background:#E4F3EA;color:#14663E}
.ms-spam{background:#FBE7E4;color:#9E3423}
.md-text{
  background:var(--paper);border-left:3px solid var(--gold);border-radius:0 11px 11px 0;
  padding:16px 18px;white-space:pre-line;line-height:1.65;font-size:14.5px;
}
.md-row{display:grid;grid-template-columns:130px minmax(0,1fr);gap:14px;padding:11px 0;border-bottom:1px solid var(--line)}
.md-row:last-child{border-bottom:0}
.md-row .k{color:var(--ink-mute);font-size:13px}
.md-row .v{font-weight:600;word-break:break-word}
.md-acts{display:grid;gap:9px}
.md-acts .btn{justify-content:center}
.md-acts textarea{
  width:100%;font:inherit;font-size:13.5px;padding:9px 11px;
  border:1px solid var(--line);border-radius:9px;outline:0;resize:vertical;min-height:80px;
}
.md-acts textarea:focus{border-color:var(--navy)}
.md-sep{height:1px;background:var(--line);margin:4px 0}
.md-meta{font-size:12px;color:var(--ink-mute);line-height:1.7;margin-top:12px}
</style>

<div class="tabs" style="margin-bottom:16px">
  <a href="{{ route('admin.messages.index') }}" class="tab">← All messages</a>
  @if ($row->order_number)
    <a href="{{ route('admin.orders.index', ['search' => $row->order_number]) }}" class="tab">Find order {{ $row->order_number }}</a>
  @endif
  @if ($row->phone)
    <a href="{{ route('admin.customers.show', $row->phone) }}" class="tab">Customer record</a>
  @endif
</div>

<div class="md-grid">

  <div class="panel">
    <div class="panel-head">
      <div>
        <h2>{{ $row->topic ?: 'Message' }} <span class="md-pill ms-{{ $row->status }}">{{ $row->status_label }}</span></h2>
        <div class="sub">From {{ $row->name }}, {{ $row->created_at->format('j M Y') }} at {{ $row->created_at->format('g:i a') }}</div>
      </div>
    </div>

    <div class="panel-body">
      <div class="md-text">{{ $row->message }}</div>

      <div style="margin-top:20px">
        <div class="md-row">
          <div class="k">Name</div>
          <div class="v">{{ $row->name }} @if ($row->user_id)<span class="sub">— has an account</span>@endif</div>
        </div>
        <div class="md-row">
          <div class="k">Phone</div>
          <div class="v"><a href="tel:{{ $row->phone }}">{{ $row->phone }}</a></div>
        </div>
        @if ($row->email)
          <div class="md-row">
            <div class="k">Email</div>
            <div class="v"><a href="mailto:{{ $row->email }}">{{ $row->email }}</a></div>
          </div>
        @endif
        @if ($row->order_number)
          <div class="md-row">
            <div class="k">Order number</div>
            <div class="v">{{ $row->order_number }}</div>
          </div>
        @endif
        @if ($row->admin_note)
          <div class="md-row">
            <div class="k">Your note</div>
            <div class="v" style="font-weight:400">{{ $row->admin_note }}</div>
          </div>
        @endif
      </div>

      <div class="md-meta">
        @if ($row->read_at)Read {{ $row->read_at->format('j M, g:i a') }}@if ($row->handler) by {{ $row->handler->name }}@endif<br>@endif
        @if ($row->replied_at)Marked replied {{ $row->replied_at->format('j M, g:i a') }}<br>@endif
        Sent from {{ $row->ip ?: 'an unknown address' }}
      </div>
    </div>
  </div>

  <div class="panel">
    <div class="panel-head"><div><h2>Reply</h2><div class="sub">Answer where they are, then mark it done.</div></div></div>

    <div class="panel-body">
      <div class="md-acts">
        @if ($row->whatsapp_link)
          <a href="{{ $row->whatsapp_link }}" target="_blank" rel="noopener" class="btn btn-navy btn-sm">Reply on WhatsApp</a>
        @endif

        <a href="tel:{{ $row->phone }}" class="btn btn-line btn-sm">Call {{ $row->phone }}</a>

        @if ($row->email)
          <a href="mailto:{{ $row->email }}?subject={{ rawurlencode('Re: ' . ($row->topic ?: 'your message') . ' — ' . ($settings['store_name'] ?? 'AMJR Global')) }}"
             class="btn btn-line btn-sm">Reply by email</a>
        @endif

        <div class="md-sep"></div>

        <form method="POST" action="{{ route('admin.messages.update', $row) }}">
          @csrf
          @method('PATCH')
          <textarea name="admin_note" placeholder="Note for yourself — what you told them">{{ $row->admin_note }}</textarea>

          <div class="md-acts" style="margin-top:9px">
            @if ($row->status !== 'replied')
              <button type="submit" name="action" value="replied" class="btn btn-gold btn-sm">Mark as replied</button>
            @else
              <button type="submit" name="action" value="read" class="btn btn-line btn-sm">Move back to inbox</button>
            @endif

            @if ($row->status === 'spam')
              <button type="submit" name="action" value="unspam" class="btn btn-line btn-sm">Not spam</button>
            @else
              <button type="submit" name="action" value="spam" class="btn btn-line btn-sm">Mark as spam</button>
            @endif
          </div>
        </form>

        <div class="md-sep"></div>

        <form method="POST" action="{{ route('admin.messages.destroy', $row) }}"
              onsubmit="return confirm('Delete this message for good?')">
          @csrf
          @method('DELETE')
          <button class="btn btn-line btn-sm" style="color:var(--bad);width:100%;justify-content:center">Delete</button>
        </form>
      </div>
    </div>
  </div>

</div>

@endsection
