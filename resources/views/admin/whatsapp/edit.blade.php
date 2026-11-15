@extends('admin.layouts.app')
@section('title', 'WhatsApp')

@section('content')

<style>
.wa-state{display:inline-block;font-size:11.5px;font-weight:700;padding:4px 10px;border-radius:20px;white-space:nowrap}
.wa-state.on{background:#E5F3EB;color:var(--ok)}
.wa-state.off{background:#EEF0F3;color:var(--ink-mute)}
.wa-state.warn{background:#FCEEE2;color:var(--warn)}
.wa-tick{display:flex;align-items:flex-start;gap:9px;font-size:13.5px;line-height:1.5;margin-bottom:14px}
.wa-tick input{margin-top:3px;accent-color:#128C4A}
.wa-tick span span{display:block;color:var(--ink-mute);font-size:12.5px}
.wa-guide{font-size:13px;color:var(--ink-mute);line-height:1.6}
.wa-guide b{display:block;margin:14px 0 4px;color:var(--ink)}
.wa-guide b:first-child{margin-top:0}
.wa-guide ol{margin:0;padding-left:18px}
.wa-guide code{background:#F1F3F7;padding:1px 6px;border-radius:4px;font-size:12.5px;color:var(--navy);word-break:break-all}
.wa-demo{display:flex;align-items:center;gap:12px;padding:12px 14px;border-radius:12px;background:linear-gradient(135deg,#E9F9EF,#F6FCF8);border:1px solid #CFEEDB;margin-bottom:16px}
.wa-demo i{width:44px;height:44px;border-radius:50%;background:#25D366;display:grid;place-items:center;flex-shrink:0;box-shadow:0 8px 18px -8px #128C4A}
.wa-demo svg{width:24px;height:24px;fill:#fff}
.wa-demo span{font-size:13px;color:var(--ink-mute);line-height:1.5}
.wa-demo b{display:block;color:var(--ink);font-size:14px}
</style>

@if ($errors->any())
  <div class="alert alert-bad" style="margin-bottom:18px">
    <ul style="margin:0;padding-left:18px">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
  </div>
@endif

@php $provider = old('provider', $s['provider']); @endphp

<div class="form-grid">
  <div class="form-main">

    <form method="POST" action="{{ route('admin.whatsapp.update') }}">
      @csrf
      @method('PUT')

      {{-- ---------- chat button ---------- --}}
      <div class="panel" style="border-radius:12px">
        <div class="panel-head">
          <div>
            <h2>Chat button on the website</h2>
            <span class="sub">A green WhatsApp button in the corner of every shop page</span>
          </div>
          <span class="wa-state {{ $chatReady ? 'on' : 'off' }}">{{ $chatReady ? 'Showing' : 'Off' }}</span>
        </div>
        <div class="panel-body">
          <div class="wa-demo">
            <i><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3a9 9 0 0 0-7.7 13.6L3 21l4.5-1.2A9 9 0 1 0 12 3z"/><circle cx="8.4" cy="12" r="1.1" fill="#25D366"/><circle cx="12" cy="12" r="1.1" fill="#25D366"/><circle cx="15.6" cy="12" r="1.1" fill="#25D366"/></svg></i>
            <span><b>Customers tap it and WhatsApp opens</b>No account or key is needed. On a product page the message already names the product.</span>
          </div>

          <div class="row2">
            <div class="field">
              <label for="chat_number">WhatsApp number</label>
              <input type="text" id="chat_number" name="chat_number" inputmode="tel" value="{{ old('chat_number', $s['chat_number']) }}" placeholder="8801XXXXXXXXX" autocomplete="off">
              <div class="hint">With the country code. A number typed as 01… is saved as 8801…</div>
            </div>
            <div class="field">
              <label for="chat_text">Opening message</label>
              <input type="text" id="chat_text" name="chat_text" maxlength="300" value="{{ old('chat_text', $s['chat_text']) }}" placeholder="Hello, I have a question.">
              <div class="hint">Already typed for the customer. They can change it.</div>
            </div>
          </div>

          <label class="wa-tick">
            <input type="hidden" name="chat_on" value="0">
            <input type="checkbox" name="chat_on" value="1" @checked(old('chat_on', $s['chat_on']))>
            <span><b>Show the chat button</b><span>Bottom-right corner, on phones and computers.</span></span>
          </label>
        </div>
      </div>

      {{-- ---------- order alerts ---------- --}}
      <div class="panel" style="border-radius:12px;margin-top:18px">
        <div class="panel-head">
          <div>
            <h2>Order alerts on WhatsApp</h2>
            <span class="sub">A message to you each time a customer places an order</span>
          </div>
          @if ($alertReady)
            <span class="wa-state on">Sending</span>
          @elseif ($s['alert_on'])
            <span class="wa-state warn">Details missing</span>
          @else
            <span class="wa-state off">Off</span>
          @endif
        </div>
        <div class="panel-body">

          <div class="row2">
            <div class="field">
              <label for="provider">Send through</label>
              <select id="provider" name="provider">
                @foreach ($providers as $code => $label)
                  <option value="{{ $code }}" @selected($provider === $code)>{{ $label }}</option>
                @endforeach
              </select>
            </div>
            <div class="field">
              <label for="alert_number">Send alerts to (WhatsApp number)</label>
              <input type="text" id="alert_number" name="alert_number" inputmode="tel" value="{{ old('alert_number', $s['alert_number']) }}" placeholder="8801XXXXXXXXX" autocomplete="off">
            </div>
          </div>

          <div class="field">
            <label for="key" id="keyLabel">{{ $provider === 'meta' ? 'Access token' : 'API key' }}</label>
            <input type="password" id="key" name="key" autocomplete="new-password" placeholder="{{ $hasKey ? 'Saved — leave empty to keep it' : '' }}">
            <div class="hint">Stored encrypted and never shown again.</div>
          </div>

          <div id="waMeta" @if ($provider !== 'meta') hidden @endif>
            <div class="row2">
              <div class="field">
                <label for="meta_phone_id">Phone number ID</label>
                <input type="text" id="meta_phone_id" name="meta_phone_id" inputmode="numeric" value="{{ old('meta_phone_id', $s['meta_phone_id']) }}" autocomplete="off">
              </div>
              <div class="row2">
                <div class="field">
                  <label for="meta_template">Template name</label>
                  <input type="text" id="meta_template" name="meta_template" value="{{ old('meta_template', $s['meta_template']) }}" placeholder="new_order_alert" autocomplete="off">
                </div>
                <div class="field">
                  <label for="meta_lang">Language</label>
                  <input type="text" id="meta_lang" name="meta_lang" value="{{ old('meta_lang', $s['meta_lang']) }}" placeholder="en">
                </div>
              </div>
            </div>
          </div>

          <label class="wa-tick">
            <input type="hidden" name="alert_on" value="0">
            <input type="checkbox" name="alert_on" value="1" @checked(old('alert_on', $s['alert_on']))>
            <span><b>Message me when an order is placed</b><span>Order number, customer, total and a link to the order.</span></span>
          </label>

          <button type="submit" class="btn btn-gold">Save WhatsApp settings</button>
        </div>
      </div>
    </form>

  </div>

  <div class="form-side">
    <div class="panel" style="border-radius:12px">
      <div class="panel-head"><h2>Send a test</h2></div>
      <div class="panel-body">
        <form method="POST" action="{{ route('admin.whatsapp.test') }}">
          @csrf
          <button type="submit" class="btn btn-navy" style="width:100%" @disabled(! $alertReady)>Send a test alert</button>
          <p class="sub" style="margin:10px 0 0">Save the settings first. If it fails, the reason is shown here.</p>
        </form>
      </div>
    </div>

    <div class="panel" style="border-radius:12px;margin-top:18px">
      <div class="panel-head"><h2>How to get a key</h2></div>
      <div class="panel-body wa-guide">
        <b>CallMeBot — free, two minutes</b>
        <ol>
          <li>Save <code>+34 644 51 95 23</code> in your phone contacts.</li>
          <li>From the WhatsApp that should get the alerts, send it: <code>I allow callmebot to send me messages</code></li>
          <li>It replies with your API key. Paste it here.</li>
        </ol>
        The number can change — check callmebot.com if there is no reply. It only sends to your own number.

        <b>WhatsApp Cloud API — official</b>
        Needs a Meta Business account. In Meta for Developers create a WhatsApp app, then copy the permanent access token and the phone number ID. Create a template with three blanks, for example:
        <code>New order @{{1}} from @{{2}}. Total @{{3}}.</code>
        Meta must approve the template before it sends.
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
/* The extra boxes only show for the Cloud API. */
(function () {
  var provider = document.getElementById('provider');
  if (!provider) return;
  provider.addEventListener('change', function () {
    var meta = provider.value === 'meta';
    document.getElementById('waMeta').hidden = !meta;
    document.getElementById('keyLabel').textContent = meta ? 'Access token' : 'API key';
  });
})();
</script>
@endpush

@endsection
