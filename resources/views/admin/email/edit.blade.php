@extends('admin.layouts.app')
@section('title', 'Email')

@section('content')

<style>
.em-state{display:inline-block;font-size:11.5px;font-weight:700;padding:4px 10px;border-radius:20px;white-space:nowrap}
.em-state.on{background:#E5F3EB;color:var(--ok)}
.em-state.off{background:#EEF0F3;color:var(--ink-mute)}
.em-state.warn{background:#FCEEE2;color:var(--warn)}
.em-tick{display:flex;align-items:flex-start;gap:9px;font-size:13.5px;line-height:1.5;margin-bottom:14px}
.em-tick input{margin-top:3px;accent-color:var(--navy)}
.em-tick span span{display:block;color:var(--ink-mute);font-size:12.5px}
.em-modes{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.em-mode{display:flex;gap:10px;align-items:flex-start;padding:13px 14px;border:1.5px solid var(--line);border-radius:12px;cursor:pointer;transition:border-color .15s,background .15s}
.em-mode:has(input:checked){border-color:var(--navy);background:#F3F6FC}
.em-mode input{margin-top:3px;accent-color:var(--navy)}
.em-mode b{display:block;font-size:14px}
.em-mode span span{display:block;font-size:12.5px;color:var(--ink-mute);line-height:1.5;margin-top:2px}
.em-mode em{font-style:normal;font-size:11px;font-weight:700;color:var(--ok);background:#E5F3EB;border-radius:20px;padding:2px 8px;margin-left:6px}
@media(max-width:700px){.em-modes{grid-template-columns:1fr}}
.em-guide b{display:block;margin:14px 0 4px;color:var(--ink)}
.em-guide b:first-child{margin-top:0}
.em-guide{font-size:13px;color:var(--ink-mute);line-height:1.6}
.em-guide code{background:#F1F3F7;padding:1px 6px;border-radius:4px;font-size:12.5px;color:var(--navy)}
</style>

@if ($errors->any())
  <div class="alert alert-bad" style="margin-bottom:18px">
    <ul style="margin:0;padding-left:18px">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
  </div>
@endif

@if (! $ready)
  <div class="note" style="margin-bottom:18px">
    <b>Email is not being sent from these settings yet.</b>
    @if ($envMailer === 'log')
      Right now the site only writes emails to a log file, so customers receive nothing — no order confirmations and no password-reset links.
    @endif
    Choose how to send below, tick <em>Send email</em>, save, then send yourself a test.
  </div>
@endif

<div class="form-grid">
  <div class="form-main">

    <form method="POST" action="{{ route('admin.email.update') }}">
      @csrf
      @method('PUT')

      @php $mode = old('mode', $s['mode']); @endphp

      <div class="panel" style="border-radius:12px">
        <div class="panel-head">
          <div>
            <h2>How the shop sends email</h2>
            <span class="sub">Order emails, status updates and password-reset links</span>
          </div>
          @if ($ready)
            <span class="em-state on">Sending</span>
          @elseif ($s['on'])
            <span class="em-state warn">Details missing</span>
          @else
            <span class="em-state off">Off</span>
          @endif
        </div>
        <div class="panel-body">

          <div class="em-modes" style="margin-bottom:16px">
            <label class="em-mode">
              <input type="radio" name="mode" value="server" @checked($mode === 'server') @disabled(! $canServer)>
              <span><b>Simple — no SMTP <em>No setup</em></b>
                <span>The website's own server sends the email. No mailbox or password needed.
                @unless ($canServer) This server has it switched off, so it cannot be used here. @endunless</span></span>
            </label>
            <label class="em-mode">
              <input type="radio" name="mode" value="smtp" @checked($mode === 'smtp')>
              <span><b>SMTP mailbox</b>
                <span>Sends through a real mailbox. Most reliable, and the least likely to land in spam.</span></span>
            </label>
          </div>

          <div id="emSmtp" @if ($mode === 'server') hidden @endif>
          <div class="row2">
            <div class="field">
              <label for="host">Mail server</label>
              <input type="text" id="host" name="host" value="{{ old('host', $s['host']) }}" placeholder="smtp.hostinger.com" autocomplete="off">
            </div>
            <div class="row2">
              <div class="field">
                <label for="security">Security</label>
                <select id="security" name="security">
                  <option value="ssl" @selected(old('security', $s['security']) === 'ssl')>SSL</option>
                  <option value="tls" @selected(old('security', $s['security']) === 'tls')>STARTTLS</option>
                </select>
              </div>
              <div class="field">
                <label for="port">Port</label>
                <input type="number" id="port" name="port" min="1" max="65535" value="{{ old('port', $s['port']) }}">
              </div>
            </div>
          </div>

          <div class="row2">
            <div class="field">
              <label for="username">Username</label>
              <input type="text" id="username" name="username" value="{{ old('username', $s['username']) }}" placeholder="orders@yourdomain.com" autocomplete="off">
              <div class="hint">Usually the full email address.</div>
            </div>
            <div class="field">
              <label for="password">Password</label>
              <input type="password" id="password" name="password" autocomplete="new-password"
                     placeholder="{{ $hasPassword ? 'Saved — leave empty to keep it' : '' }}">
              <div class="hint">Stored encrypted and never shown again.</div>
            </div>
          </div>
          </div>

          <div class="row2">
            <div class="field">
              <label for="from_address">Send from (address)</label>
              <input type="email" id="from_address" name="from_address" value="{{ old('from_address', $s['from_address']) }}" placeholder="{{ $defaultFrom ?: 'orders@yourdomain.com' }}">
              <div class="hint" id="emFromSmtp" @if ($mode === 'server') hidden @endif>Use the same mailbox as the username, or the email may land in spam.</div>
              <div class="hint" id="emFromServer" @if ($mode !== 'server') hidden @endif>Must be on the shop's own domain{{ $defaultFrom ? ' — empty uses ' . $defaultFrom : '' }}. A Gmail address here will be rejected as fake.</div>
            </div>
            <div class="field">
              <label for="from_name">Send from (name)</label>
              <input type="text" id="from_name" name="from_name" value="{{ old('from_name', $s['from_name']) }}" placeholder="{{ $settings['store_name'] ?? 'AMJR Global' }}">
              <div class="hint">What customers see as the sender. Empty uses the shop name.</div>
            </div>
          </div>

          <label class="em-tick">
            <input type="hidden" name="on" value="0">
            <input type="checkbox" name="on" value="1" @checked(old('on', $s['on']))>
            <span><b>Send email</b><span>Order confirmations, status updates and password-reset links go out this way.</span></span>
          </label>
        </div>
      </div>

      <div class="panel" style="border-radius:12px;margin-top:18px">
        <div class="panel-head"><div><h2>Tell me about new orders</h2><span class="sub">A copy of each order confirmation, sent to you</span></div></div>
        <div class="panel-body">
          <label class="em-tick">
            <input type="hidden" name="admin_copy" value="0">
            <input type="checkbox" name="admin_copy" value="1" @checked(old('admin_copy', $s['admin_copy']))>
            <span><b>Email me when an order is placed</b><span>Sent whether or not the customer gave an email address.</span></span>
          </label>
          <div class="field" style="max-width:420px">
            <label for="admin_to">Send the copy to</label>
            <input type="email" id="admin_to" name="admin_to" value="{{ old('admin_to', $s['admin_to'] ?: ($settings['store_email'] ?? '')) }}">
          </div>
          <button type="submit" class="btn btn-gold">Save email settings</button>
        </div>
      </div>
    </form>

  </div>

  <div class="form-side">
    <div class="panel" style="border-radius:12px">
      <div class="panel-head"><h2>Send a test</h2></div>
      <div class="panel-body">
        <form method="POST" action="{{ route('admin.email.test') }}">
          @csrf
          <div class="field">
            <label for="to">Send a test email to</label>
            <input type="email" id="to" name="to" required value="{{ old('to', auth()->user()->email) }}">
          </div>
          <button type="submit" class="btn btn-navy" style="width:100%" @disabled(! $ready)>Send test email</button>
          <p class="sub" style="margin:10px 0 0">Save the settings first. If it fails, the reason is shown here.</p>
        </form>
      </div>
    </div>

    <div class="panel" style="border-radius:12px;margin-top:18px">
      <div class="panel-head"><h2>Common settings</h2></div>
      <div class="panel-body em-guide">
        <b>Simple — no SMTP</b>
        Nothing to fill in except where order emails should go. Some inboxes put these in spam at first: open one and press <em>Not spam</em> once.

        <b>Hostinger email</b>
        Server <code>smtp.hostinger.com</code>, SSL, port <code>465</code>. Username is the full address; password is that mailbox's password. Create the mailbox first in hPanel → Emails.

        <b>Gmail</b>
        Server <code>smtp.gmail.com</code>, STARTTLS, port <code>587</code>. Google will not accept your normal password: turn on 2-step verification, then create an <em>App password</em> and use that.

        <b>Port and security go together</b>
        SSL uses 465. STARTTLS uses 587.
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
/* Changing the security type moves the port to its usual partner. */
(function () {
  /* The SMTP boxes only show when SMTP is chosen. */
  var radios = document.querySelectorAll('input[name="mode"]');
  function sync() {
    var picked = document.querySelector('input[name="mode"]:checked');
    var server = picked && picked.value === 'server';
    document.getElementById('emSmtp').hidden = server;
    document.getElementById('emFromSmtp').hidden = server;
    document.getElementById('emFromServer').hidden = !server;
  }
  radios.forEach(function (r) { r.addEventListener('change', sync); });

  var security = document.getElementById('security');
  var port = document.getElementById('port');
  if (!security || !port) return;
  security.addEventListener('change', function () {
    if (port.value === '465' || port.value === '587' || port.value === '') {
      port.value = security.value === 'ssl' ? '465' : '587';
    }
  });
})();
</script>
@endpush

@endsection
