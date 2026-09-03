@extends('site.layouts.app')
@section('title', __('Your cart') . ' — ' . ($settings['store_name'] ?? 'AMJR Global'))

@section('content')

<div class="crumbs"><div class="wrap">
  <a href="{{ route('home') }}">{{ __('Home') }}</a><span>/</span>
  <strong>{{ __('Your cart') }}</strong>
</div></div>

<div class="wrap cartpage">

  @if ($cart->items->isEmpty())
    <div class="noresults">
      <h2>{{ __('Your cart is empty') }}</h2>
      <p>{{ __('Nothing here yet. Have a look at what just landed.') }}</p>
      <a href="{{ route('shop') }}" class="btn btn-gold">{{ __('Browse products') }}</a>
    </div>
  @else

    @php $problems = $cart->problemLines(); @endphp
    @if ($problems->isNotEmpty())
      <div class="warnbox">
        {{ __('Some items dropped in stock since you added them. Quantities have been capped below.') }}
      </div>
    @endif

    <div class="cartgrid">
      <div class="cartlines">
        <div class="clhead">
          <span>{{ __('Product') }}</span>
          <span>{{ __('Quantity') }}</span>
          <span class="right">{{ __('Total') }}</span>
        </div>

        @foreach ($cart->items as $item)
          <div class="cline">
            <div class="cprod">
              <a href="{{ route('shop.product', $item->product) }}" class="cthumb">
                @if ($item->product->primaryImage)
                  <img src="{{ $item->product->primaryImage->url }}" alt="{{ $item->product->name }}">
                @else
                  <svg width="30" height="50" viewBox="0 0 88 150" fill="none" aria-hidden="true"><rect x="14" y="24" width="60" height="122" rx="8" fill="#14305A" opacity=".12"/><circle cx="44" cy="100" r="14" fill="#DFA327" opacity=".26"/></svg>
                @endif
              </a>
              <div>
                @if ($item->product->brand)<div class="cbrand">{{ $item->product->brand->name }}</div>@endif
                <a href="{{ route('shop.product', $item->product) }}" class="cname">{{ $item->product->name }}</a>
                @if ($item->label)<div class="cvar">{{ $item->label }}</div>@endif
                <div class="cunit">৳{{ number_format($item->unit_price) }} {{ __('each') }}</div>
                @if ($item->quantity >= $item->available)
                  <div class="cwarn">{{ __('Only :n in stock', ['n' => $item->available]) }}</div>
                @endif
              </div>
            </div>

            <div class="cqty">
              <form method="POST" action="{{ route('cart.update', $item) }}" class="qtyform">
                @csrf @method('PATCH')
                <div class="qty">
                  <button type="submit" name="quantity" value="{{ $item->quantity - 1 }}" aria-label="{{ __('Decrease') }}">−</button>
                  <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" max="{{ $item->available }}" aria-label="{{ __('Quantity') }}">
                  <button type="submit" name="quantity" value="{{ $item->quantity + 1 }}" aria-label="{{ __('Increase') }}"
                          @disabled($item->quantity >= $item->available)>+</button>
                </div>
              </form>

              <form method="POST" action="{{ route('cart.destroy', $item) }}">
                @csrf @method('DELETE')
                <button type="submit" class="removelink">{{ __('Remove') }}</button>
              </form>
            </div>

            <div class="cltotal">৳{{ number_format($item->line_total) }}</div>
          </div>
        @endforeach
      </div>

      <aside class="cartsum">
        <div class="sumbox">
          <h2>{{ __('Order summary') }}</h2>

          @php
            $freeOver = (float) ($settings['free_delivery_over'] ?? 2000);
            $delivery = (float) ($settings['delivery_dhaka'] ?? 60);
            $qualifies = $cart->subtotal >= $freeOver;
            $shortfall = max(0, $freeOver - $cart->subtotal);
          @endphp

          <div class="sumline">
            <span>{{ __('Subtotal') }} ({{ $cart->count }} {{ trans_choice('item|items', $cart->count) }})</span>
            <b>৳{{ number_format($cart->subtotal) }}</b>
          </div>

          <div class="sumline">
            <span>{{ __('Delivery, inside Dhaka') }}</span>
            <b>{{ $qualifies ? __('Free') : '৳' . number_format($delivery) }}</b>
          </div>

          @if (! $qualifies && $shortfall > 0)
            <div class="nudge">
              {{ __('Add ৳:n more for free delivery.', ['n' => number_format($shortfall)]) }}
            </div>
          @endif

          <div class="sumtotal">
            <span>{{ __('Estimated total') }}</span>
            <b>৳{{ number_format($cart->subtotal + ($qualifies ? 0 : $delivery)) }}</b>
          </div>

          <p class="sumnote">
            {{ __('Delivery is calculated properly at checkout once you enter an address. Cash on delivery available everywhere.') }}
          </p>

          <a href="{{ route('checkout.show') }}" class="btn btn-gold" style="width:100%">
            {{ __('Proceed to checkout') }}
          </a>

          @if (!empty($settings['store_whatsapp']))
            <a href="https://wa.me/{{ $settings['store_whatsapp'] }}" target="_blank" rel="noopener"
               class="btn btn-line" style="width:100%;margin-top:9px">{{ __('Order on WhatsApp instead') }}</a>
          @endif

          <a href="{{ route('shop') }}" class="keepshopping">{{ __('Keep shopping') }}</a>
        </div>
      </aside>
    </div>
  @endif

</div>

@endsection
