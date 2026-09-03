@extends('admin.layouts.app')
@section('title', 'New order by hand')

@section('content')

@if ($errors->any())
  <div class="alert alert-bad">
    <ul style="margin:0;padding-left:18px">
      @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
  </div>
@endif

<div class="note" style="margin-bottom:18px">
  For orders that arrive by Facebook message or phone call. Stock comes off exactly as it
  does on the storefront, so your numbers stay honest.
</div>

<form method="POST" action="{{ route('admin.orders.store') }}">
  @csrf
  <div class="form-grid">
  <div class="form-main">

    <div class="panel">
      <div class="panel-head">
        <h2>Products</h2>
        <button type="button" class="btn btn-line btn-sm" id="addLine">Add a line</button>
      </div>
      <div class="panel-body">
        <div class="vtable-wrap">
          <table class="vtable">
            <thead>
              <tr><th style="min-width:280px">Product</th><th style="min-width:90px">Qty</th>
                  <th style="min-width:110px">Unit price</th><th></th></tr>
            </thead>
            <tbody id="lineRows"></tbody>
          </table>
        </div>
        <div class="sub" id="lineTotal" style="margin-top:12px"></div>
      </div>
    </div>

    <div class="panel">
      <div class="panel-head"><h2>Customer</h2></div>
      <div class="panel-body">
        <div class="row2">
          <div class="field">
            <label for="customer_name">Name</label>
            <input type="text" id="customer_name" name="customer_name" value="{{ old('customer_name') }}" required>
          </div>
          <div class="field">
            <label for="customer_phone">Phone</label>
            <input type="text" id="customer_phone" name="customer_phone" value="{{ old('customer_phone') }}" required placeholder="01XXXXXXXXX">
          </div>
        </div>
        <div class="field">
          <label for="customer_email">Email (optional)</label>
          <input type="text" id="customer_email" name="customer_email" value="{{ old('customer_email') }}">
        </div>
        <div class="field">
          <label for="shipping_address">Address</label>
          <textarea id="shipping_address" name="shipping_address" required>{{ old('shipping_address') }}</textarea>
        </div>
        <div class="row2">
          <div class="field">
            <label for="shipping_area">Area or thana</label>
            <input type="text" id="shipping_area" name="shipping_area" value="{{ old('shipping_area') }}">
          </div>
          <div class="field">
            <label for="shipping_zone_id">Delivery zone</label>
            <select id="shipping_zone_id" name="shipping_zone_id">
              <option value="">None</option>
              @foreach ($zones as $zone)
                <option value="{{ $zone->id }}" data-rate="{{ $zone->rate }}" @selected(old('shipping_zone_id') == $zone->id)>
                  {{ $zone->name }} — ৳{{ number_format($zone->rate) }}
                </option>
              @endforeach
            </select>
          </div>
        </div>
      </div>
    </div>

  </div>

  <div class="form-side">
    <div class="panel">
      <div class="panel-head"><h2>Totals</h2></div>
      <div class="panel-body">
        <div class="field">
          <label for="delivery_charge">Delivery charge (৳)</label>
          <input type="number" step="1" min="0" id="delivery_charge" name="delivery_charge" value="{{ old('delivery_charge') }}">
          <div class="hint">Leave empty to use the zone rate.</div>
        </div>
        <div class="field">
          <label for="discount">Discount (৳)</label>
          <input type="number" step="1" min="0" id="discount" name="discount" value="{{ old('discount', 0) }}">
        </div>
        <div class="field">
          <label for="status">Starting status</label>
          <select id="status" name="status">
            @foreach (\App\Models\Order::STATUSES as $key => $label)
              <option value="{{ $key }}" @selected(old('status', 'confirmed') === $key)>{{ $label }}</option>
            @endforeach
          </select>
          <div class="hint">Phone orders usually start as Confirmed.</div>
        </div>
        <div class="field">
          <label for="admin_note">Internal note</label>
          <textarea id="admin_note" name="admin_note" style="min-height:70px">{{ old('admin_note') }}</textarea>
        </div>
        <button type="submit" class="btn btn-gold" style="width:100%">Create order</button>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-line" style="width:100%;margin-top:9px">Cancel</a>
      </div>
    </div>
  </div>
  </div>
</form>

<script id="productOptions" type="application/json">@json($products)</script>
@endsection

@push('scripts')<script src="{{ asset('js/manual-order.js') }}"></script>@endpush
