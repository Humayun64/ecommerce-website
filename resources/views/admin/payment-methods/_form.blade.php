@csrf

<style>
.pmf{display:grid;grid-template-columns:minmax(0,1fr) 320px;gap:18px;align-items:start}
@media(max-width:1000px){.pmf{grid-template-columns:1fr}}
.pmf .field{margin-bottom:16px}
.pmf .field:last-child{margin-bottom:0}
.pmf label{display:block;font-size:13px;font-weight:600;margin-bottom:6px}
.pmf input[type=text],.pmf input[type=number],.pmf select,.pmf textarea,.pmf input[type=file]{
  width:100%;font:inherit;font-size:14px;padding:9px 11px;background:#fff;
  border:1px solid var(--line);border-radius:9px;outline:0;
}
.pmf input:focus,.pmf select:focus,.pmf textarea:focus{border-color:var(--navy)}
.pmf textarea{resize:vertical;min-height:90px;line-height:1.55}
.pmf .hint{font-size:12.5px;color:var(--ink-mute);margin-top:5px;line-height:1.5}
.pmf .err{color:var(--bad);font-size:12.5px;margin-top:5px}
.pmf .row2{display:grid;grid-template-columns:1fr 1fr;gap:12px}
@media(max-width:560px){.pmf .row2{grid-template-columns:1fr}}
.pmf .tick{display:flex;align-items:flex-start;gap:9px;font-size:13.5px;line-height:1.5;margin-bottom:12px}
.pmf .tick input{margin-top:3px;flex:0 0 auto}
.pmf .tick b{display:block;font-weight:600}
.pmf .tick span{color:var(--ink-mute);font-size:12.5px}
.pm-soon{
  background:#FDF3DC;border:1px solid #EBD79B;color:#7A5A0E;
  border-radius:11px;padding:13px 15px;font-size:13px;line-height:1.6;margin-top:12px;
}
.pm-colour{display:flex;gap:9px;align-items:center}
.pm-colour input[type=text]{flex:1}
.pm-swatch{width:38px;height:38px;border-radius:9px;border:1px solid var(--line);flex:0 0 auto}
</style>

<div class="pmf">

  <div class="panel">
    <div class="panel-head"><div><h2>The method</h2><div class="sub">What the customer sees at checkout.</div></div></div>
    <div class="panel-body">

      <div class="row2">
        <div class="field">
          <label for="name">Name</label>
          <input type="text" id="name" name="name" value="{{ old('name', $method->name) }}" placeholder="bKash" required>
          @error('name')<div class="err">{{ $message }}</div>@enderror
        </div>

        <div class="field">
          <label for="code">Code</label>
          <input type="text" id="code" name="code" value="{{ old('code', $method->code) }}" placeholder="bkash" required>
          <div class="hint">Saved on every order. Do not change it once orders exist.</div>
          @error('code')<div class="err">{{ $message }}</div>@enderror
        </div>
      </div>

      <div class="field">
        <label for="tagline">One-line description</label>
        <input type="text" id="tagline" name="tagline" value="{{ old('tagline', $method->tagline) }}"
               placeholder="Send money, then enter your transaction ID">
        @error('tagline')<div class="err">{{ $message }}</div>@enderror
      </div>

      <div class="field">
        <label for="driver">How it works</label>
        <select id="driver" name="driver" required>
          @foreach ($drivers as $key => $label)
            <option value="{{ $key }}" @selected(old('driver', $method->driver) === $key)>{{ $label }}</option>
          @endforeach
        </select>
        @error('driver')<div class="err">{{ $message }}</div>@enderror
      </div>

      <div class="field" id="gatewayRow">
        <label for="gateway">Provider</label>
        <select id="gateway" name="gateway">
          <option value="">Not chosen</option>
          @foreach ($gateways as $key => $label)
            <option value="{{ $key }}" @selected(old('gateway', $method->gateway) === $key)>{{ $label }}</option>
          @endforeach
        </select>
        <div class="pm-soon">
          <b>Not connected yet.</b>
          Picking a provider here records which one you intend to use and keeps its settings, but
          no automatic payment is taken — the method stays off the checkout page until the API
          is built. Manual is what takes money today.
        </div>
        @error('gateway')<div class="err">{{ $message }}</div>@enderror
      </div>

      <div id="manualRows">
        <div class="row2">
          <div class="field">
            <label for="account_number">Your number</label>
            <input type="text" id="account_number" name="account_number"
                   value="{{ old('account_number', $method->account_number) }}" placeholder="01313164918">
            <div class="hint">Shown to the customer as the number to send money to.</div>
            @error('account_number')<div class="err">{{ $message }}</div>@enderror
          </div>

          <div class="field">
            <label for="account_type">Account type</label>
            <input type="text" id="account_type" name="account_type"
                   value="{{ old('account_type', $method->account_type) }}" placeholder="Personal">
            <div class="hint">Personal, Agent or Merchant — it changes what the customer does.</div>
          </div>
        </div>

        <div class="field">
          <label for="instructions">Instructions</label>
          <textarea id="instructions" name="instructions"
                    placeholder="Open bKash → Send Money → enter the number above → enter the exact total → then type the transaction ID here.">{{ old('instructions', $method->instructions) }}</textarea>
          <div class="hint">Line breaks are kept. Write it the way you would say it on the phone.</div>
          @error('instructions')<div class="err">{{ $message }}</div>@enderror
        </div>

        <label class="tick">
          <input type="hidden" name="needs_sender" value="0">
          <input type="checkbox" name="needs_sender" value="1" @checked(old('needs_sender', $method->needs_sender))>
          <span><b>Ask for the customer's own number</b>
          <span>So you can call them if the payment does not match.</span></span>
        </label>

        <label class="tick" style="margin-bottom:0">
          <input type="hidden" name="needs_txn" value="0">
          <input type="checkbox" name="needs_txn" value="1" @checked(old('needs_txn', $method->needs_txn))>
          <span><b>Ask for the transaction ID</b>
          <span>Required for anything you have to check by hand.</span></span>
        </label>
      </div>

    </div>
  </div>

  <div>
    <div class="panel">
      <div class="panel-head"><div><h2>Settings</h2></div></div>
      <div class="panel-body">

        <label class="tick">
          <input type="hidden" name="is_active" value="0">
          <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $method->is_active))>
          <span><b>Switched on</b><span>Off hides it from checkout at once.</span></span>
        </label>

        <label class="tick">
          <input type="hidden" name="is_default" value="0">
          <input type="checkbox" name="is_default" value="1" @checked(old('is_default', $method->is_default))>
          <span><b>Pre-selected</b><span>Only one method can be.</span></span>
        </label>

        <div class="field">
          <label for="sort_order">Position</label>
          <input type="number" id="sort_order" name="sort_order" min="0" max="999"
                 value="{{ old('sort_order', $method->sort_order ?? 0) }}">
          <div class="hint">Lower shows first.</div>
        </div>

        <div class="field">
          <label for="charge_percent">Extra charge (%)</label>
          <input type="number" id="charge_percent" name="charge_percent" step="0.01" min="0" max="100"
                 value="{{ old('charge_percent', $method->charge_percent ?? 0) }}">
          <div class="hint">Shown as a badge on the option. Leave at 0 for no charge.</div>
          @error('charge_percent')<div class="err">{{ $message }}</div>@enderror
        </div>

        <div class="field">
          <label for="min_amount">Minimum order (৳)</label>
          <input type="number" id="min_amount" name="min_amount" step="1" min="0"
                 value="{{ old('min_amount', $method->min_amount) }}" placeholder="No minimum">
          @error('min_amount')<div class="err">{{ $message }}</div>@enderror
        </div>

      </div>
    </div>

    <div class="panel" style="margin-top:18px">
      <div class="panel-head"><div><h2>Look</h2></div></div>
      <div class="panel-body">

        <div class="field">
          <label for="accent">Brand colour</label>
          <div class="pm-colour">
            <input type="text" id="accent" name="accent" value="{{ old('accent', $method->accent) }}" placeholder="#E2136E">
            <span class="pm-swatch" id="accentSwatch"
                  style="background:{{ old('accent', $method->accent) ?: 'var(--paper)' }}"></span>
          </div>
          <div class="hint">The colour of the "send payment to" bar. bKash is #E2136E, Nagad #EE7622, Rocket #8C3494.</div>
          @error('accent')<div class="err">{{ $message }}</div>@enderror
        </div>

        <div class="field">
          <label for="logo">Logo</label>
          @if ($method->logo)
            <img src="{{ Storage::url($method->logo) }}" alt="" style="height:26px;margin-bottom:9px;display:block">
          @endif
          <input type="file" id="logo" name="logo" accept="image/*">
          <div class="hint">Small and wide works best. PNG or SVG.</div>
          @error('logo')<div class="err">{{ $message }}</div>@enderror
        </div>

      </div>
    </div>
  </div>

</div>

@push('scripts')
<script>
(function () {
  var driver  = document.getElementById('driver');
  var manual  = document.getElementById('manualRows');
  var gateway = document.getElementById('gatewayRow');
  var accent  = document.getElementById('accent');
  var swatch  = document.getElementById('accentSwatch');

  function sync() {
    if (manual)  manual.style.display  = driver.value === 'manual'  ? '' : 'none';
    if (gateway) gateway.style.display = driver.value === 'gateway' ? '' : 'none';
  }

  if (driver) {
    driver.addEventListener('change', sync);
    sync();
  }

  if (accent && swatch) {
    accent.addEventListener('input', function () {
      swatch.style.background = /^#[0-9A-Fa-f]{6}$/.test(accent.value) ? accent.value : '';
    });
  }
})();
</script>
@endpush
