@extends('admin.layouts.app')
@section('title', 'Couriers')

@section('content')

<style>
.cr-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:18px;align-items:start}
.cr-card{border-radius:12px;overflow:hidden}
.cr-head{display:flex;align-items:center;gap:12px;padding:16px 18px;border-bottom:1px solid var(--line)}
.cr-logo{width:40px;height:40px;border-radius:10px;display:grid;place-items:center;color:#fff;font-weight:800;font-size:15px;flex-shrink:0}
.cr-head h2{font-size:16px}
.cr-state{margin-left:auto;font-size:11.5px;font-weight:700;padding:4px 10px;border-radius:20px;white-space:nowrap}
.cr-state.on{background:#E5F3EB;color:var(--ok)}
.cr-state.off{background:#EEF0F3;color:var(--ink-mute)}
.cr-state.warn{background:#FCEEE2;color:var(--warn)}
.cr-tick{display:flex;align-items:flex-start;gap:9px;font-size:13.5px;line-height:1.5;margin-bottom:12px}
.cr-tick input{margin-top:3px;accent-color:var(--navy)}
.cr-tick span span{display:block;color:var(--ink-mute);font-size:12.5px}
.cr-actions{display:flex;gap:9px;flex-wrap:wrap;margin-top:4px}
</style>

<div class="note" style="margin-bottom:18px">
  <b>How this works.</b>
  Enter the keys from the courier's merchant panel, switch the courier on, and press <em>Test connection</em>.
  After that every order page has a <em>Send to courier</em> box: one click books the parcel, saves the tracking
  code on the order and shows it to the customer.
</div>

<div class="cr-grid">
  @foreach ($couriers as $code => $c)
    <div class="panel cr-card">
      <div class="cr-head">
        <span class="cr-logo" style="background:{{ $code === 'redx' ? '#E4002B' : '#0B8B6B' }}">{{ mb_substr($c['label'], 0, 1) }}</span>
        <h2>{{ $c['label'] }}</h2>
        @if ($c['on'] && $c['connected'])
          <span class="cr-state on">On{{ $c['isSandbox'] ? ' · test mode' : '' }}</span>
        @elseif ($c['on'])
          <span class="cr-state warn">Keys missing</span>
        @else
          <span class="cr-state off">Off</span>
        @endif
      </div>

      <div class="panel-body">
        <form method="POST" action="{{ route('admin.couriers.update', $code) }}">
          @csrf
          @method('PUT')

          @if (isset($c['fields']['key']))
            <div class="field">
              <label for="{{ $code }}_key">{{ $c['fields']['key'] }}</label>
              <input type="text" id="{{ $code }}_key" name="key" value="{{ $c['key'] }}" autocomplete="off">
            </div>
          @endif

          <div class="field">
            <label for="{{ $code }}_secret">{{ $c['fields']['secret'] }}</label>
            <input type="password" id="{{ $code }}_secret" name="secret" autocomplete="new-password"
                   placeholder="{{ $c['hasSecret'] ? 'Saved — leave empty to keep it' : '' }}">
            <div class="hint">Stored encrypted and never shown again.</div>
          </div>

          @if ($c['sandbox'])
            <label class="cr-tick">
              <input type="hidden" name="sandbox" value="0">
              <input type="checkbox" name="sandbox" value="1" @checked($c['isSandbox'])>
              <span><b>Test mode</b><span>Uses the courier's test server. No real parcel is booked. A test token and a live token are different.</span></span>
            </label>
          @else
            <p class="sub" style="margin:0 0 12px">{{ $c['label'] }} has no test server — every booking is a real parcel.</p>
          @endif

          <label class="cr-tick">
            <input type="hidden" name="on" value="0">
            <input type="checkbox" name="on" value="1" @checked($c['on'])>
            <span><b>Switched on</b><span>Offer {{ $c['label'] }} on order pages.</span></span>
          </label>

          <div class="cr-actions">
            <button type="submit" class="btn btn-navy">Save</button>
            <button type="submit" class="btn btn-line" form="test-{{ $code }}">Test connection</button>
          </div>
        </form>

        <form method="POST" action="{{ route('admin.couriers.test', $code) }}" id="test-{{ $code }}">@csrf</form>
      </div>
    </div>
  @endforeach
</div>

<div class="panel" style="margin-top:18px;border-radius:12px">
  <div class="panel-head">
    <div>
      <h2>Parcels on the road</h2>
      <span class="sub">{{ $inTransit }} shipped order{{ $inTransit === 1 ? '' : 's' }} booked through a courier</span>
    </div>
    <form method="POST" action="{{ route('admin.couriers.sync') }}">
      @csrf
      <button type="submit" class="btn btn-gold" @disabled($inTransit === 0)>Check all now</button>
    </form>
  </div>
  <div class="panel-body">
    <p class="sub" style="margin:0">
      Asks each courier where its parcels are. A parcel reported <b>delivered</b> marks the order Delivered
      (and paid, for cash on delivery). A parcel reported <b>cancelled or returned</b> is only flagged —
      you decide whether to cancel or return the order, because that puts stock back.
      Up to 40 parcels are checked each time; press again for the rest.
    </p>
  </div>
</div>

@endsection
