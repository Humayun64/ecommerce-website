@extends('site.layouts.app')
@section('title', __('Addresses') . ' — ' . ($settings['store_name'] ?? 'AMJR Global'))
@push('head')<meta name="robots" content="noindex">@endpush

@section('content')
<div class="wrap acctpage">
  @include('site.account._nav')

  <div class="acctmain">
    <div class="accthead"><h1>{{ __('Addresses') }}</h1></div>

    @if ($errors->any())
      <div class="warnbox bad">
        <ul style="margin:0;padding-left:18px">
          @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
      </div>
    @endif

    @if ($addresses->isNotEmpty())
      <div class="addrgrid">
        @foreach ($addresses as $addr)
          <div class="addrcard {{ $addr->is_default ? 'main' : '' }}">
            @if ($addr->is_default)<span class="addrtag">{{ __('Default') }}</span>@endif
            @if ($addr->label)<div class="addrlabel">{{ $addr->label }}</div>@endif
            <b>{{ $addr->name }}</b>
            <p>{{ $addr->address }}@if ($addr->area)<br>{{ $addr->area }}@endif<br>{{ $addr->phone }}</p>
            <small>{{ $addr->zone?->name }}</small>
            <form method="POST" action="{{ route('account.addresses.destroy', $addr) }}"
                  onsubmit="return confirm('{{ __('Remove this address?') }}')">
              @csrf @method('DELETE')
              <button type="submit" class="removelink">{{ __('Remove') }}</button>
            </form>
          </div>
        @endforeach
      </div>
    @endif

    <div class="cobox" style="margin-top:18px">
      <h2>{{ __('Add an address') }}</h2>
      <form method="POST" action="{{ route('account.addresses.store') }}">
        @csrf
        <div class="corow">
          <div class="cofield">
            <label for="label">{{ __('Label') }} <small>({{ __('optional') }})</small></label>
            <input type="text" id="label" name="label" value="{{ old('label') }}" placeholder="{{ __('Home, office…') }}">
          </div>
          <div class="cofield">
            <label for="name">{{ __('Full name') }}</label>
            <input type="text" id="name" name="name" value="{{ old('name', auth()->user()->name) }}" required>
          </div>
        </div>
        <div class="corow">
          <div class="cofield">
            <label for="phone">{{ __('Mobile number') }}</label>
            <input type="tel" id="phone" name="phone" value="{{ old('phone', auth()->user()->phone) }}" required placeholder="01XXXXXXXXX">
          </div>
          <div class="cofield">
            <label for="area">{{ __('Area or thana') }}</label>
            <input type="text" id="area" name="area" value="{{ old('area') }}">
          </div>
        </div>
        <div class="cofield">
          <label for="address">{{ __('Full address') }}</label>
          <textarea id="address" name="address" required>{{ old('address') }}</textarea>
        </div>
        <div class="cofield">
          <label for="shipping_zone_id">{{ __('Delivery area') }}</label>
          <select id="shipping_zone_id" name="shipping_zone_id" style="width:100%;border:1.5px solid var(--line);border-radius:3px;padding:11px 13px;font:inherit;font-size:15px;background:#fff">
            <option value="">{{ __('Choose one') }}</option>
            @foreach ($zones as $zone)
              <option value="{{ $zone->id }}" @selected(old('shipping_zone_id') == $zone->id)>{{ $zone->name }} — ৳{{ number_format($zone->rate) }}</option>
            @endforeach
          </select>
        </div>
        <label class="fcheck" style="margin-bottom:16px">
          <input type="checkbox" name="is_default" value="1" @checked($addresses->isEmpty())>
          <span>{{ __('Use this as my default address') }}</span>
        </label>
        <button type="submit" class="btn btn-gold">{{ __('Save address') }}</button>
      </form>
    </div>
  </div>
</div>
@endsection
