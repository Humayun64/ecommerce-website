{{-- Coupon entry on the cart page. Plain form post so it works
     with JavaScript switched off. --}}
<div class="couponbox">
  @if ($coupon)
    <div class="couponon">
      <div>
        <span class="couponcode">{{ $coupon->code }}</span>
        <small>
          @if ($freeShip && $discount > 0)
            {{ __('৳:n off and free delivery', ['n' => number_format($discount)]) }}
          @elseif ($freeShip)
            {{ __('Free delivery') }}
          @else
            {{ __('৳:n off', ['n' => number_format($discount)]) }}
          @endif
        </small>
      </div>
      <form method="POST" action="{{ route('coupon.destroy') }}">
        @csrf @method('DELETE')
        <button type="submit" class="couponremove">{{ __('Remove') }}</button>
      </form>
    </div>
  @else
    <form method="POST" action="{{ route('coupon.store') }}" class="couponform">
      @csrf
      <label for="coupon_code">{{ __('Have a coupon code?') }}</label>
      <div class="couponrow">
        <input type="text" id="coupon_code" name="code" value="{{ old('code') }}"
               placeholder="{{ __('Enter code') }}" autocomplete="off">
        <button type="submit" class="btn btn-navy">{{ __('Apply') }}</button>
      </div>
    </form>
  @endif
</div>
