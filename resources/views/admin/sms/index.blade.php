@extends('admin.layouts.app')
@section('title', 'SMS')

@section('content')

<style>
.sm-grid{display:grid;grid-template-columns:minmax(0,1fr) 340px;gap:18px;align-items:start}
@media(max-width:1100px){.sm-grid{grid-template-columns:1fr}}
.sm-f{margin-bottom:15px}
.sm-f label{display:block;font-size:13px;font-weight:600;margin-bottom:6px}
.sm-f input[type=text],.sm-f select,.sm-f textarea{
  width:100%;font:inherit;font-size:14px;padding:9px 11px;background:#fff;
  border:1px solid var(--line);border-radius:9px;outline:0;
}
.sm-f input:focus,.sm-f select:focus,.sm-f textarea:focus{border-color:var(--navy)}
.sm-f textarea{resize:vertical;min-height:70px;line-height:1.55}
.sm-f .hint{font-size:12.5px;color:var(--ink-mute);margin-top:5px;line-height:1.5}
.sm-f .err{color:var(--bad);font-size:12.5px;margin-top:5px}
.sm-row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
@media(max-width:620px){.sm-row{grid-template-columns:1fr}}
.sm-tick{display:flex;align-items:flex-start;gap:9px;font-size:13.5px;line-height:1.5;margin-bottom:14px}
.sm-tick input{margin-top:3px;flex:0 0 auto}
.sm-tick b{display:block;font-weight:600}
.sm-tick span{color:var(--ink-mute);font-size:12.5px}

.sm-ev{border:1px solid var(--line);border-radius:12px;padding:14px 16px;margin-bottom:12px}
.sm-ev:last-child{margin-bottom:0}
.sm-ev-top{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:10px}
.sm-ev-top b{font-size:14.5px}
.sm-cost{font-size:11.5px;font-weight:700;border-radius:999px;padding:3px 10px;white-space:nowrap}
.sm-c1{background:#E4F3EA;color:#14663E}
.sm-c2{background:#FDF3DC;color:#8A6410}
.sm-c3{background:#FBE7E4;color:#9E3423}

.sm-stat{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:10px 0;border-bottom:1px solid var(--line);font-size:13.5px}
.sm-stat:last-child{border-bottom:0}
.sm-stat b{font-weight:600}
.sm-stat span{color:var(--ink-mute)}
.sm-pill{display:inline-block;font-size:11px;font-weight:700;border-radius:999px;padding:3px 10px}
.sm-sent{background:#E4F3EA;color:#14663E}
.sm-failed{background:#FBE7E4;color:#9E3423}
.sm-queued{background:#FDF3DC;color:#8A6410}
.sm-msg{font-size:12.5px;color:var(--ink-mute);line-height:1.5;max-width:380px}
.sm-note{background:var(--paper);border-radius:10px;padding:13px 15px;font-size:12.5px;color:var(--ink-mute);line-height:1.65;margin-top:14px}
.sm-note b{display:block;color:var(--ink);font-size:13px;margin-bottom:3px}
.sm-keys{font-family:ui-monospace,'SF Mono',Menlo,Consolas,monospace;font-size:12px}
</style>

@if (! $gateway->isConfigured())
  <div class="alert alert-bad" style="margin-bottom:18px">
    <b>No SMS is going out.</b>
    Nothing is sent to customers until the gateway below is filled in and switched on.
  </div>
@endif

<div class="sm-grid">

  <div>
    {{-- ---------------- gateway ---------------- --}}
    <form method="POST" action="{{ route('admin.sms.gateway') }}">
      @csrf
      @method('PATCH')

      <div class="panel">
        <div class="panel-head">
          <div>
            <h2>Gateway</h2>
            <div class="sub">Your SMS provider's details. Any Bangladeshi gateway works — they all take the same shape of request.</div>
          </div>
        </div>

        <div class="panel-body">
          <label class="sm-tick">
            <input type="hidden" name="sms_enabled" value="0">
            <input type="checkbox" name="sms_enabled" value="1" @checked(($settings['sms_enabled'] ?? '0') === '1')>
            <span><b>Send SMS</b><span>Off stops every message at once, without losing your settings.</span></span>
          </label>

          <div class="sm-f">
            <label for="preset">Start from</label>
            <select id="preset">
              <option value="">Choose a provider to fill the form</option>
              @foreach ($presets as $key => $preset)
                <option value="{{ $key }}"
                        data-url="{{ $preset['url'] }}"
                        data-method="{{ $preset['method'] }}"
                        data-fields="{{ json_encode($preset['fields']) }}">{{ $preset['label'] }}</option>
              @endforeach
            </select>
            <div class="hint">This only fills the boxes below. Check them against your provider's documentation — they do change things.</div>
          </div>

          <div class="sm-row">
            <div class="sm-f">
              <label for="sms_url">API URL</label>
              <input type="text" id="sms_url" name="sms_url" value="{{ old('sms_url', $settings['sms_url'] ?? '') }}"
                     placeholder="https://api.example.com/sendsms">
              @error('sms_url')<div class="err">{{ $message }}</div>@enderror
            </div>
            <div class="sm-f">
              <label for="sms_method">Method</label>
              <select id="sms_method" name="sms_method">
                <option value="GET" @selected(($settings['sms_method'] ?? 'GET') === 'GET')>GET</option>
                <option value="POST" @selected(($settings['sms_method'] ?? '') === 'POST')>POST</option>
              </select>
            </div>
          </div>

          <div class="sm-row">
            <div class="sm-f">
              <label for="sms_api_key">API key</label>
              <input type="text" id="sms_api_key" name="sms_api_key" value="{{ old('sms_api_key', $settings['sms_api_key'] ?? '') }}">
              @error('sms_api_key')<div class="err">{{ $message }}</div>@enderror
            </div>
            <div class="sm-f">
              <label for="sms_sender">Sender ID</label>
              <input type="text" id="sms_sender" name="sms_sender" value="{{ old('sms_sender', $settings['sms_sender'] ?? '') }}"
                     placeholder="AMJR">
              <div class="hint">Has to be approved by your provider first.</div>
            </div>
          </div>

          <div class="sm-f">
            <label>What the provider calls each field</label>
            <div class="sm-row">
              <input type="text" name="field_api_key" class="sm-keys" value="{{ $gateway->fields()['api_key'] }}" placeholder="api_key">
              <input type="text" name="field_to" class="sm-keys" value="{{ $gateway->fields()['to'] }}" placeholder="to">
            </div>
            <div class="sm-row" style="margin-top:12px">
              <input type="text" name="field_text" class="sm-keys" value="{{ $gateway->fields()['text'] }}" placeholder="message">
              <input type="text" name="field_from" class="sm-keys" value="{{ $gateway->fields()['from'] ?? '' }}" placeholder="senderid">
            </div>
            <div class="hint">
              In order: the key, the number, the message, the sender. If your provider's docs show
              <span class="sm-keys">?api_key=...&amp;number=...&amp;message=...</span> then those are the three words to put here.
            </div>
          </div>

          <div class="sm-f">
            <label for="sms_extra">Anything else the provider needs</label>
            <textarea id="sms_extra" name="sms_extra" class="sm-keys"
                      placeholder="type=text&#10;lang=unicode">{{ old('sms_extra', $settings['sms_extra'] ?? '') }}</textarea>
            <div class="hint">One <span class="sm-keys">key=value</span> per line. Leave empty if there is nothing extra.</div>
          </div>

          <button class="btn btn-navy">Save gateway</button>
        </div>
      </div>
    </form>

    {{-- ---------------- templates ---------------- --}}
    <form method="POST" action="{{ route('admin.sms.templates') }}" style="margin-top:18px">
      @csrf
      @method('PATCH')

      <div class="panel">
        <div class="panel-head">
          <div>
            <h2>The messages</h2>
            <div class="sub">Tick the ones to send. Each costs money, so only turn on what earns its keep.</div>
          </div>
        </div>

        <div class="panel-body">
          @foreach ($events as $key => $event)
            <div class="sm-ev">
              <div class="sm-ev-top">
                <label class="sm-tick" style="margin:0">
                  <input type="hidden" name="on_{{ $key }}" value="0">
                  <input type="checkbox" name="on_{{ $key }}" value="1" @checked($event['on'])>
                  <span><b>{{ $event['label'] }}</b></span>
                </label>

                <span class="sm-cost {{ $event['parts'] <= 1 ? 'sm-c1' : ($event['parts'] === 2 ? 'sm-c2' : 'sm-c3') }}">
                  {{ $event['chars'] }} chars · {{ $event['parts'] }} SMS{{ $event['unicode'] ? ' · Bangla' : '' }}
                </span>
              </div>

              <textarea name="tpl_{{ $key }}" rows="2">{{ $notifier->template($key) }}</textarea>
              @error("tpl_{$key}")<div class="err">{{ $message }}</div>@enderror
            </div>
          @endforeach

          <div class="sm-note">
            <b>Placeholders you can use</b>
            <span class="sm-keys">{name} {order} {total} {store} {phone} {status} {tracking} {courier} {items}</span>
            <br><br>
            <b>Why Bangla costs three times more</b>
            A plain English message fits 160 characters for one SMS charge. One Bangla letter turns the
            whole message Unicode and the limit drops to 70 — so the same sentence in Bangla is usually
            billed as three messages, not one. The counter above each box shows the real cost.
          </div>

          <div style="margin-top:16px">
            <button class="btn btn-navy">Save messages</button>
          </div>
        </div>
      </div>
    </form>
  </div>

  {{-- ---------------- right ---------------- --}}
  <div>
    <div class="panel">
      <div class="panel-head"><div><h2>Send a test</h2></div></div>
      <div class="panel-body">
        <form method="POST" action="{{ route('admin.sms.test') }}">
          @csrf
          <div class="sm-f">
            <label for="phone">Your number</label>
            <input type="text" id="phone" name="phone" value="{{ $settings['store_phone'] ?? '' }}" placeholder="01XXXXXXXXX">
            @error('phone')<div class="err">{{ $message }}</div>@enderror
          </div>
          <button class="btn btn-gold btn-sm">Send test SMS</button>
        </form>

        <div class="sm-note">
          Do this before you trust it with a customer. If it fails, the reason from the gateway
          appears in the log below — usually a wrong field name or no balance.
        </div>
      </div>
    </div>

    <div class="panel" style="margin-top:18px">
      <div class="panel-head"><div><h2>This month</h2></div></div>
      <div class="panel-body">
        <div class="sm-stat"><b>Messages billed</b><span>{{ number_format($sentMonth) }}</span></div>
        <div class="sm-stat"><b>Failed, all time</b><span>{{ number_format($failed) }}</span></div>
        <div class="sm-stat">
          <b>Gateway</b>
          <span class="sm-pill {{ $gateway->isConfigured() ? 'sm-sent' : 'sm-failed' }}">
            {{ $gateway->isConfigured() ? 'Ready' : 'Not set up' }}
          </span>
        </div>
        <div class="sm-note">
          Counted in SMS parts, not messages sent, because that is what your provider bills you for.
        </div>
      </div>
    </div>
  </div>
</div>

{{-- ---------------- log ---------------- --}}
<div class="panel" style="margin-top:18px">
  <div class="panel-head">
    <div>
      <h2>Recent messages</h2>
      <div class="sub">Every send, and what the gateway said back.</div>
    </div>
    <form method="GET" class="filters">
      <select name="status">
        <option value="">All</option>
        <option value="sent" @selected(request('status') === 'sent')>Sent</option>
        <option value="failed" @selected(request('status') === 'failed')>Failed</option>
      </select>
      <button class="btn btn-navy btn-sm">Filter</button>
    </form>
  </div>

  @if ($logs->isEmpty())
    <div class="empty"><b>Nothing sent yet</b>Messages appear here as they go out.</div>
  @else
    <table>
      <thead>
        <tr><th>To</th><th>Event</th><th>Message</th><th>Status</th><th class="right">When</th></tr>
      </thead>
      <tbody>
      @foreach ($logs as $log)
        <tr>
          <td>{{ $log->phone }}</td>
          <td>
            {{ $log->event ? (\App\Services\SmsNotifier::EVENTS[$log->event] ?? ucfirst($log->event)) : '—' }}
            @if ($log->order)
              <small style="display:block;color:var(--ink-mute);font-size:12px;margin-top:2px">
                <a href="{{ route('admin.orders.show', $log->order) }}">#{{ $log->order->order_number }}</a>
              </small>
            @endif
          </td>
          <td class="sm-msg">
            {{ Str::limit($log->message, 90) }}
            @if ($log->status === 'failed' && $log->response)
              <br><span style="color:var(--bad)">{{ Str::limit($log->response, 70) }}</span>
            @endif
          </td>
          <td>
            <span class="sm-pill sm-{{ $log->status }}">{{ $log->status_label }}</span>
            @if ($log->parts > 1)<small style="display:block;color:var(--ink-mute);font-size:11.5px;margin-top:3px">{{ $log->parts }} parts</small>@endif
          </td>
          <td class="right sub">{{ $log->created_at->diffForHumans() }}</td>
        </tr>
      @endforeach
      </tbody>
    </table>
    <div class="pager">{{ $logs->links('vendor.pagination.amjr') }}</div>
  @endif
</div>

@push('scripts')
<script>
(function () {
  var preset = document.getElementById('preset');
  if (!preset) return;

  preset.addEventListener('change', function () {
    var picked = preset.options[preset.selectedIndex];
    var url = picked.getAttribute('data-url');
    if (url === null) return;

    document.getElementById('sms_url').value = url;
    document.getElementById('sms_method').value = picked.getAttribute('data-method') || 'GET';

    var fields = {};
    try { fields = JSON.parse(picked.getAttribute('data-fields') || '{}'); } catch (e) { return; }

    ['api_key', 'to', 'text', 'from'].forEach(function (key) {
      var input = document.querySelector('[name="field_' + key + '"]');
      if (input) input.value = fields[key] || '';
    });
  });
})();
</script>
@endpush

@endsection
