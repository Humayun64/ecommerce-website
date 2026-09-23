@extends('site.layouts.app')

@section('title', __('Request a return') . ' — ' . ($settings['store_name'] ?? 'AMJR Global'))

@push('head')<link rel="stylesheet" href="{{ asset('css/returns.css') }}"><meta name="robots" content="noindex">@endpush

@section('content')
<div class="rt-wrap">

  <div class="rt-crumb">
    <a href="{{ route('orders.track') }}">{{ __('Track an order') }}</a> ·
    {{ __('Order') }} #{{ $order->order_number }}
  </div>

  <div class="rt-head">
    <h1>{{ __('Send something back') }}</h1>
    <p>
      {{ __('Tell us which item and why. We read every one ourselves — you will hear back within two working days.') }}
    </p>
  </div>

  @if ($blocked)

    <div class="rt-stop">
      <b>{{ __('This order cannot be returned') }}</b>
      {{ $blocked }}
      <div style="margin-top:12px">
        @if (!empty($settings['store_phone']))
          {{ __('If you think that is wrong, call us on') }}
          <a href="tel:{{ $settings['store_phone'] }}" style="color:inherit;font-weight:700">{{ $settings['store_phone'] }}</a>.
        @endif
      </div>
    </div>

  @else

    @if ($closes)
      <div class="rt-note">
        {{ __('This order can be returned until :date.', ['date' => $closes->format('j F Y')]) }}
        {{ __('Keep the box and anything that came with it — an item that arrives back incomplete may only be part-refunded.') }}
      </div>
    @endif

    <form method="POST" action="{{ route('returns.store', $order) }}" enctype="multipart/form-data">
      @csrf

      <div class="rt-card">
        <h2>{{ __('Which item?') }}</h2>
        <p class="hint">{{ __('One item per request. Open another if more than one thing is going back.') }}</p>

        <div class="rt-pick">
          @foreach ($items as $index => $item)
            <label class="rt-item">
              <input type="radio" name="order_item_id" value="{{ $item->id }}"
                     @checked(old('order_item_id', $items->count() === 1 ? $item->id : null) == $item->id)>
              <span class="nm">
                {{ $item->name }}
                @if ($item->variant_label)<span class="mt">{{ $item->variant_label }}</span>@endif
              </span>
              <span class="pr">
                <b>৳{{ number_format($item->unit_price) }}</b>
                <span>{{ __('each') }}</span>
              </span>
            </label>
          @endforeach
        </div>

        @error('order_item_id')<div class="rt-err">{{ $message }}</div>@enderror
        @error('order')<div class="rt-err">{{ $message }}</div>@enderror

        <div class="rt-field" style="margin-top:18px">
          <label for="quantity">{{ __('How many?') }}</label>
          <input type="number" name="quantity" id="quantity" class="rt-qty"
                 min="1" max="99" value="{{ old('quantity', 1) }}">
          @error('quantity')<div class="rt-err">{{ $message }}</div>@enderror
        </div>
      </div>

      <div class="rt-card">
        <h2>{{ __('What went wrong?') }}</h2>
        <p class="hint">{{ __('The more you tell us, the faster this goes through.') }}</p>

        <div class="rt-field">
          <label for="reason">{{ __('Reason') }}</label>
          <select name="reason" id="reason" required>
            <option value="">{{ __('Choose one') }}</option>
            @foreach ($reasons as $key => $label)
              <option value="{{ $key }}" @selected(old('reason') === $key)>{{ $label }}</option>
            @endforeach
          </select>
          @error('reason')<div class="rt-err">{{ $message }}</div>@enderror
        </div>

        <div class="rt-field">
          <label for="note">{{ __('Anything else') }} <span style="font-weight:400;color:var(--ink-mute)">({{ __('optional') }})</span></label>
          <textarea name="note" id="note" placeholder="{{ __('The seal was already broken when the box arrived…') }}">{{ old('note') }}</textarea>
          @error('note')<div class="rt-err">{{ $message }}</div>@enderror
        </div>

        <div class="rt-field">
          <label for="photo">{{ __('Photo') }} <span style="font-weight:400;color:var(--ink-mute)">({{ __('optional') }})</span></label>
          <input type="file" name="photo" id="photo" accept="image/*">
          <div class="sub">{{ __('If something is damaged or wrong, a photo settles it in one go. Up to 4 MB.') }}</div>
          @error('photo')<div class="rt-err">{{ $message }}</div>@enderror
        </div>

        <div class="rt-actions">
          <button type="submit" class="btn btn-gold">{{ __('Send the request') }}</button>
          <a href="{{ route('orders.track') }}" class="btn btn-line">{{ __('Cancel') }}</a>
        </div>
      </div>

    </form>

  @endif

</div>
@endsection
