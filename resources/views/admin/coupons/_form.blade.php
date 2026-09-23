@csrf
@if ($method === 'PUT') @method('PUT') @endif

@if ($errors->any())
  <div class="alert alert-bad">
    <ul style="margin:0;padding-left:18px">
      @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
  </div>
@endif

<div class="form-grid">
<div class="form-main">

  <div class="panel">
    <div class="panel-head"><h2>The offer</h2></div>
    <div class="panel-body">

      <div class="row2">
        <div class="field">
          <label for="code">Code</label>
          <input type="text" id="code" name="code" value="{{ old('code', $coupon->code) }}" required
                 placeholder="EIDSALE" style="text-transform:uppercase;font-family:ui-monospace,monospace;letter-spacing:.04em">
          <div class="hint">What the customer types. Case does not matter.</div>
        </div>
        <div class="field">
          <label for="description">Internal note</label>
          <input type="text" id="description" name="description" value="{{ old('description', $coupon->description) }}"
                 placeholder="Eid campaign, posted 20 Sep">
          <div class="hint">Only you see this.</div>
        </div>
      </div>

      <div class="row3">
        <div class="field">
          <label for="type">Discount type</label>
          <select id="type" name="type">
            <option value="percent" @selected(old('type', $coupon->type) === 'percent')>Percentage off</option>
            <option value="fixed" @selected(old('type', $coupon->type) === 'fixed')>Fixed taka off</option>
          </select>
        </div>
        <div class="field">
          <label for="value"><span id="valueLabel">Percentage</span></label>
          <input type="number" step="0.01" min="0" id="value" name="value" value="{{ old('value', $coupon->value) }}" required>
        </div>
        <div class="field" id="capField">
          <label for="max_discount">Maximum discount (৳)</label>
          <input type="number" step="1" min="0" id="max_discount" name="max_discount" value="{{ old('max_discount', $coupon->max_discount) }}">
          <div class="hint">Caps a percentage. Leave empty for no cap.</div>
        </div>
      </div>

      <div class="field">
        <label class="check">
          <input type="checkbox" name="free_shipping" value="1" @checked(old('free_shipping', $coupon->free_shipping))>
          Also make delivery free
        </label>
      </div>

    </div>
  </div>

  <div class="panel">
    <div class="panel-head"><h2>Where it applies</h2></div>
    <div class="panel-body">
      <div class="note" style="margin-bottom:16px">
        Leave both empty and the discount comes off the whole cart. Pick one to
        run something like "20% off all Anua". A parent category covers everything under it.
      </div>

      <div class="row2">
        <div class="field">
          <label for="category_id">Category only</label>
          <select id="category_id" name="category_id">
            <option value="">Any category</option>
            @foreach ($categories as $category)
              <option value="{{ $category->id }}" @selected(old('category_id', $coupon->category_id) == $category->id)>{{ $category->full_name }}</option>
            @endforeach
          </select>
        </div>
        <div class="field">
          <label for="brand_id">Brand only</label>
          <select id="brand_id" name="brand_id">
            <option value="">Any brand</option>
            @foreach ($brands as $brand)
              <option value="{{ $brand->id }}" @selected(old('brand_id', $coupon->brand_id) == $brand->id)>{{ $brand->name }}</option>
            @endforeach
          </select>
        </div>
      </div>
    </div>
  </div>

  <div class="panel">
    <div class="panel-head"><h2>Conditions</h2></div>
    <div class="panel-body">

      <div class="row2">
        <div class="field">
          <label for="min_spend">Minimum spend (৳)</label>
          <input type="number" step="1" min="0" id="min_spend" name="min_spend" value="{{ old('min_spend', $coupon->min_spend) }}">
          <div class="hint">Checked against the cart subtotal, before delivery.</div>
        </div>
        <div class="field">
          <label class="check" style="margin-top:28px">
            <input type="checkbox" name="first_order_only" value="1" @checked(old('first_order_only', $coupon->first_order_only))>
            First-time customers only
          </label>
          <div class="hint">Matched on phone number, so it works for guest orders too.</div>
        </div>
      </div>

      <div class="row2">
        <div class="field">
          <label for="usage_limit">Total uses allowed</label>
          <input type="number" step="1" min="1" id="usage_limit" name="usage_limit" value="{{ old('usage_limit', $coupon->usage_limit) }}">
          <div class="hint">Empty means unlimited.</div>
        </div>
        <div class="field">
          <label for="usage_limit_per_customer">Uses per customer</label>
          <input type="number" step="1" min="1" id="usage_limit_per_customer" name="usage_limit_per_customer"
                 value="{{ old('usage_limit_per_customer', $coupon->usage_limit_per_customer) }}">
          <div class="hint">Usually 1. Empty means unlimited.</div>
        </div>
      </div>

      <div class="row2">
        <div class="field">
          <label for="starts_at">Starts</label>
          <input type="datetime-local" id="starts_at" name="starts_at"
                 value="{{ old('starts_at', $coupon->starts_at?->format('Y-m-d\TH:i')) }}"
                 style="width:100%;border:1.5px solid var(--line);border-radius:3px;padding:10px 12px;font:inherit;font-size:14.5px;background:#fff">
          <div class="hint">Empty means it works straight away.</div>
        </div>
        <div class="field">
          <label for="expires_at">Ends</label>
          <input type="datetime-local" id="expires_at" name="expires_at"
                 value="{{ old('expires_at', $coupon->expires_at?->format('Y-m-d\TH:i')) }}"
                 style="width:100%;border:1.5px solid var(--line);border-radius:3px;padding:10px 12px;font:inherit;font-size:14.5px;background:#fff">
          <div class="hint">Empty means it never expires.</div>
        </div>
      </div>

    </div>
  </div>

</div>

<div class="form-side">

  <div class="panel">
    <div class="panel-head"><h2>Publish</h2></div>
    <div class="panel-body">
      <div class="field">
        <label class="check">
          <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $coupon->is_active ?? true))>
          Coupon is switched on
        </label>
        <div class="hint">Untick to stop it working without deleting it.</div>
      </div>
      <button type="submit" class="btn btn-gold" style="width:100%">{{ $submit }}</button>
      <a href="{{ route('admin.coupons.index') }}" class="btn btn-line" style="width:100%;margin-top:9px">Cancel</a>
    </div>
  </div>

  @if ($coupon->exists)
    <div class="panel">
      <div class="panel-head"><h2>So far</h2></div>
      <div class="panel-body">
        <div class="glance"><span>Times used</span><b>{{ $coupon->used_count }}</b></div>
        <div class="glance"><span>Given away</span><b>৳{{ number_format($coupon->usages()->sum('discount_amount')) }}</b></div>
        <div class="glance"><span>Status</span><b>{{ ucfirst($coupon->state) }}</b></div>
      </div>
    </div>
  @endif

</div>
</div>

<script>
(function () {
  var type = document.getElementById('type');
  var label = document.getElementById('valueLabel');
  var cap = document.getElementById('capField');

  function sync() {
    var percent = type.value === 'percent';
    label.textContent = percent ? 'Percentage' : 'Amount off (৳)';
    cap.style.visibility = percent ? 'visible' : 'hidden';
  }

  type.addEventListener('change', sync);
  sync();
})();
</script>
