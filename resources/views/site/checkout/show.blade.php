@extends('site.layouts.app')
@section('title', __('Checkout') . ' — ' . ($settings['store_name'] ?? 'AMJR Global'))
@push('head')<meta name="robots" content="noindex">@endpush

@section('content')

<style>
.pm-list{display:grid;gap:10px}
.pm-opt{
  display:flex;align-items:center;gap:12px;padding:14px 16px;
  border:1.5px solid var(--line);border-radius:12px;cursor:pointer;background:#fff;
  transition:border-color .15s ease,background .15s ease;
}
.pm-opt:hover{border-color:#B9C3D4}
.pm-opt:has(input:checked){border-color:var(--gold);background:#FFFDF7}
/* When a panel opens underneath, the option and the panel are one shape. */
.pm-opt:has(input[data-manual="1"]:checked){
  border-bottom-left-radius:0;border-bottom-right-radius:0;border-bottom-color:transparent;
}
.pm-opt input{margin:0;flex:0 0 auto}
.pm-body{min-width:0;flex:1 1 auto}
.pm-body b{display:flex;align-items:center;gap:8px;font-size:15px;font-weight:600;line-height:1.3}
.pm-body small{display:block;color:var(--ink-mute);font-size:12.5px;margin-top:3px;line-height:1.5}
.pm-logo{height:18px;width:auto;display:block}
.pm-fee{
  margin-left:auto;flex:0 0 auto;background:var(--paper);color:var(--ink-mute);
  font-size:11.5px;font-weight:700;border-radius:999px;padding:3px 9px;
}
.pm-panel{
  border:1.5px solid var(--gold);border-top:0;border-radius:0 0 12px 12px;
  margin:-12px 0 2px;padding:18px 16px 16px;background:#FFFDF7;
}
.pm-send{
  --pm-accent:var(--navy);
  background:var(--pm-accent);color:#fff;border-radius:10px;
  padding:12px 14px;display:flex;align-items:center;justify-content:space-between;
  gap:12px;flex-wrap:wrap;margin-bottom:14px;
}
.pm-send-lab{font-size:13px;font-weight:600;opacity:.95}
.pm-copy{
  display:inline-flex;align-items:center;gap:8px;cursor:pointer;
  background:rgba(255,255,255,.16);border:1px solid rgba(255,255,255,.3);
  color:#fff;font:inherit;font-size:15px;font-weight:700;letter-spacing:.4px;
  border-radius:8px;padding:6px 11px;
}
.pm-copy:hover{background:rgba(255,255,255,.26)}
.pm-copy.done{background:rgba(255,255,255,.34)}
.pm-how{color:var(--ink-mute);font-size:13px;line-height:1.6;margin:0 0 14px;white-space:pre-line}
.pm-field{margin-bottom:14px}
.pm-field label{display:block;font-size:13px;font-weight:600;margin-bottom:6px}
.pm-field label i{color:var(--bad);font-style:normal}
.pm-field input{
  width:100%;font:inherit;font-size:14.5px;padding:11px 13px;background:#fff;
  border:1.5px solid var(--line);border-radius:10px;outline:0;
}
.pm-field input:focus{border-color:var(--navy)}
.pm-hint{color:var(--ink-mute);font-size:12px;margin-top:5px;line-height:1.5}
.pm-err{color:var(--bad);font-size:12.5px;margin-top:6px}
.pm-wait{color:var(--ink-mute);font-size:12.5px;line-height:1.55;border-top:1px solid #EFE4C8;padding-top:12px}
.pm-none{
  background:#FBE7E4;border:1px solid #F0C4BC;color:#8C3322;
  border-radius:12px;padding:14px 16px;font-size:13.5px;line-height:1.6;
}
</style>


<style>
.couponline{display:flex;justify-content:space-between;align-items:center;gap:10px;background:#FFFCF5;border:1.5px dashed var(--gold);border-radius:3px;padding:9px 12px;margin-bottom:12px}
.couponline span{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:13px;font-weight:700;letter-spacing:.04em;color:var(--navy)}
.couponline a{font-size:12.5px;color:var(--ink-mute);text-decoration:underline}
.sumline.saving b{color:var(--ok)}
</style>

<div class="crumbs"><div class="wrap">
  <a href="{{ route('cart.index') }}">{{ __('Cart') }}</a><span>/</span>
  <strong>{{ __('Checkout') }}</strong>
</div></div>

<div class="wrap cartpage">

  @if ($errors->any())
    <div class="warnbox bad">
      <ul style="margin:0;padding-left:18px">
        @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('checkout.store') }}" id="checkoutForm">
    @csrf
    <div class="cartgrid">

      <div class="cobox">
        <h2>{{ __('Delivery details') }}</h2>

        <div class="cofield">
          <label for="customer_name">{{ __('Full name') }} <span class="req">*</span></label>
          <input type="text" id="customer_name" name="customer_name" required
                 value="{{ old('customer_name', $address->name ?? auth()->user()?->name) }}">
        </div>

        <div class="corow">
          <div class="cofield">
            <label for="customer_phone">{{ __('Mobile number') }} <span class="req">*</span></label>
            <input type="tel" id="customer_phone" name="customer_phone" required placeholder="01XXXXXXXXX"
                   value="{{ old('customer_phone', $address->phone ?? auth()->user()?->phone) }}">
            <small>{{ __('We call this number before delivery.') }}</small>
          </div>
          <div class="cofield">
            <label for="customer_email">{{ __('Email') }} <small>({{ __('optional') }})</small></label>
            <input type="email" id="customer_email" name="customer_email"
                   value="{{ old('customer_email', auth()->user()?->email) }}">
          </div>
        </div>

        <div class="cofield">
          <label for="shipping_address">{{ __('Full address') }} <span class="req">*</span></label>
          <textarea id="shipping_address" name="shipping_address" required
                    placeholder="{{ __('House, road, block, area, and any landmark') }}">{{ old('shipping_address', $address->address ?? '') }}</textarea>
        </div>

        <div class="cofield">
          <label for="shipping_area">{{ __('Area or thana') }}</label>
          <input type="text" id="shipping_area" name="shipping_area"
                 value="{{ old('shipping_area', $address->area ?? '') }}" placeholder="{{ __('Uttara, Mirpur, Gulshan…') }}">
        </div>

        <div class="cofield">
          <label>{{ __('Delivery area') }} <span class="req">*</span></label>
          <div class="zonelist">
            @foreach ($zones as $zone)
              @php $quote = $quotes[$zone->id] ?? ['amount' => (float) $zone->rate, 'free' => false]; @endphp
              <label class="zone-opt">
                <input type="radio" name="shipping_zone_id" value="{{ $zone->id }}"
                       data-rate="{{ $quote['amount'] }}"
                       @checked(old('shipping_zone_id', $address->shipping_zone_id ?? null) == $zone->id)>
                <span class="zone-body">
                  <b>{{ $zone->name }}</b>
                  <small>{{ $zone->delivery_time }}</small>
                </span>
                <span class="zone-rate">{{ ($freeShip || $quote['free']) ? __('Free') : '৳' . number_format($quote['amount']) }}</span>
              </label>
            @endforeach
          </div>
        </div>

        <div class="cofield">
          <label for="customer_note">{{ __('Note for us') }} <small>({{ __('optional') }})</small></label>
          <textarea id="customer_note" name="customer_note" style="min-height:70px"
                    placeholder="{{ __('Delivery timing, gift wrapping, anything else') }}">{{ old('customer_note') }}</textarea>
        </div>

        @auth
          <label class="fcheck" style="margin-top:4px">
            <input type="checkbox" name="save_address" value="1" checked>
            <span>{{ __('Save this address for next time') }}</span>
          </label>
        @endauth

        <h2 style="margin-top:28px">{{ __('Payment') }}</h2>

        {{--
          CheckoutRequest already validates a `payment_method` field, so it is
          still posted. The real choice rides on `payment_method_code`, which
          the controller validates against the methods set up in admin.
        --}}
        <input type="hidden" name="payment_method" value="cod">

        @error('payment_method_code')<div class="pm-err">{{ $message }}</div>@enderror

        <div class="pm-list">
          @foreach ($methods as $index => $method)
            <label class="pm-opt" data-pm="{{ $method->code }}">
              <input type="radio" name="payment_method_code" value="{{ $method->code }}"
                     data-manual="{{ $method->is_manual ? 1 : 0 }}"
                     @checked(old('payment_method_code', $method->is_default ? $method->code : ($index === 0 ? $method->code : null)) === $method->code)>

              <span class="pm-body">
                <b>
                  @if ($method->logo)
                    <img src="{{ Storage::url($method->logo) }}" alt="" class="pm-logo">
                  @endif
                  {{ $method->name }}
                </b>
                @if ($method->tagline)<small>{{ $method->tagline }}</small>@endif
              </span>

              @if ($method->charge_percent > 0)
                <span class="pm-fee">+{{ rtrim(rtrim(number_format($method->charge_percent, 2), '0'), '.') }}%</span>
              @endif
            </label>

            @if ($method->is_manual)
              <div class="pm-panel" id="pm-panel-{{ $method->code }}" hidden>

                @if ($method->account_number)
                  <div class="pm-send" @if ($method->accent) style="--pm-accent:{{ $method->accent }}" @endif>
                    <span class="pm-send-lab">
                      {{ __('Send payment to :name', ['name' => $method->name]) }}
                      @if ($method->account_type) ({{ $method->account_type }}) @endif
                    </span>
                    <button type="button" class="pm-copy" data-copy="{{ $method->account_number }}">
                      <span>{{ $method->account_number }}</span>
                      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="12" height="12" rx="2"/><path d="M5 15V5a2 2 0 012-2h10"/></svg>
                    </button>
                  </div>
                @endif

                @if ($method->instructions)
                  <p class="pm-how">{{ $method->instructions }}</p>
                @endif

                @if ($method->needs_sender)
                  <div class="pm-field">
                    <label for="sender_number">{{ __('Your :name number', ['name' => $method->name]) }} <i>*</i></label>
                    <input type="text" name="sender_number" id="sender_number"
                           value="{{ old('sender_number') }}" placeholder="01XXXXXXXXX" autocomplete="off">
                    @error('sender_number')<div class="pm-err">{{ $message }}</div>@enderror
                  </div>
                @endif

                @if ($method->needs_txn)
                  <div class="pm-field">
                    <label for="transaction_id">{{ __('Transaction ID') }} <i>*</i></label>
                    <input type="text" name="transaction_id" id="transaction_id"
                           value="{{ old('transaction_id') }}" placeholder="8N7A6D5E7M" autocomplete="off">
                    <div class="pm-hint">{{ __('The ID in the confirmation message you received after sending the money.') }}</div>
                    @error('transaction_id')<div class="pm-err">{{ $message }}</div>@enderror
                  </div>
                @endif

                <div class="pm-wait">
                  {{ __('We check every payment by hand before the parcel goes out — usually within a few hours.') }}
                </div>
              </div>
            @endif
          @endforeach

          @if ($methods->isEmpty())
            <div class="pm-none">
              {{ __('No payment method is switched on. Please call us to place this order.') }}
            </div>
          @endif
        </div>
      </div>

      <aside class="cartsum">
        <div class="sumbox">
          <h2>{{ __('Your order') }}</h2>

          <div class="colines">
            @foreach ($cart->items as $item)
              <div class="coline">
                <span class="coqty">{{ $item->quantity }}×</span>
                <span class="coname">
                  {{ $item->product->name }}
                  @if ($item->label)<em>{{ $item->label }}</em>@endif
                </span>
                <span class="cotot">৳{{ number_format($item->line_total) }}</span>
              </div>
            @endforeach
          </div>

          @if ($coupon)
            <div class="couponline">
              <span>{{ $coupon->code }}</span>
              <a href="{{ route('cart.index') }}">{{ __('change') }}</a>
            </div>
          @endif

          @php
            $freeOver  = (float) ($settings['free_delivery_over'] ?? 2000);
            $qualifies = $freeShip || ($freeOver > 0 && $cart->subtotal >= $freeOver);
          @endphp

          <div class="sumline">
            <span>{{ __('Subtotal') }}</span>
            <b>৳{{ number_format($cart->subtotal) }}</b>
          </div>

          @if ($discount > 0)
            <div class="sumline saving">
              <span>{{ __('Coupon discount') }}</span>
              <b>− ৳{{ number_format($discount) }}</b>
            </div>
          @endif

          <div class="sumline">
            <span>{{ __('Delivery') }}</span>
            <b id="deliveryCell">{{ $qualifies ? __('Free') : __('Choose an area') }}</b>
          </div>

          <div class="sumtotal">
            <span>{{ __('Total to pay') }}</span>
            <b id="totalCell">৳{{ number_format(max(0, $cart->subtotal - $discount)) }}</b>
          </div>

          <p class="sumnote">
            {{ __('You pay nothing now. The courier collects the total when the parcel reaches you.') }}
          </p>

          <button type="submit" class="btn btn-gold" style="width:100%" id="placeOrder">
            {{ __('Place order') }}
          </button>

          <a href="{{ route('cart.index') }}" class="keepshopping">{{ __('Back to cart') }}</a>
        </div>
      </aside>

    </div>
  </form>
</div>

@endsection

@php
    // Built here, not inside a Blade directive: a multi-line array as a
    // directive argument confuses Blade's parser.
    $checkoutData = [
        'subtotal' => (float) $cart->subtotal,
        'discount' => (float) $discount,
        'freeShip' => (bool) ($freeShip || collect($quotes)->contains(fn ($q) => $q['free'])),
        'freeOver' => (float) ($settings['free_delivery_over'] ?? 2000),
        'free'     => __('Free'),
        'choose'   => __('Choose an area'),
    ];
@endphp

@push('scripts')
<script id="checkoutData" type="application/json">{!! json_encode($checkoutData) !!}</script>
<script src="{{ asset('js/checkout.js') }}"></script>
<script src="{{ asset('js/payment-picker.js') }}"></script>
@endpush
