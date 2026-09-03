<div class="util"><div class="wrap">
  <div>{{ $settings['topbar_message'] ?? __('Cash on delivery across Bangladesh') }}</div>
  <div class="util-r">
    <a href="{{ route('orders.track') }}">{{ __('Track your order') }}</a>
    @if (!empty($settings['store_phone']))
      <a href="tel:{{ $settings['store_phone'] }}">{{ $settings['store_phone'] }}</a>
    @endif
  </div>
</div></div>

<header class="head"><div class="wrap">
  <a href="{{ route('home') }}" class="brand">
    @if (!empty($settings['logo']))
      <img src="{{ Storage::url($settings['logo']) }}" alt="{{ $settings['store_name'] ?? 'AMJR Global' }}" class="logo">
    @else
      <svg class="mark" viewBox="0 0 44 44" aria-hidden="true">
        <circle cx="22" cy="22" r="20.5" fill="none" stroke="#14305A" stroke-width="2.4"/>
        <circle cx="22" cy="22" r="13" fill="none" stroke="#DFA327" stroke-width="1.6"/>
        <ellipse cx="22" cy="22" rx="6" ry="13" fill="none" stroke="#DFA327" stroke-width="1.3"/>
        <path d="M9.6 17.5h24.8M9.6 26.5h24.8" stroke="#DFA327" stroke-width="1.3"/>
        <path d="M4 30c8 7 28 7 36-6" fill="none" stroke="#14305A" stroke-width="2.6" stroke-linecap="round"/>
      </svg>
    @endif
    <div>
      <div class="bname">{{ $settings['store_name'] ?? 'AMJR Global' }}</div>
      @if (!empty($settings['store_tagline']))
        <div class="btag">{{ $settings['store_tagline'] }}</div>
      @endif
    </div>
  </a>

  <div class="search">
    <form action="{{ route('shop') }}" method="GET">
      <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ __('Search serum, sunscreen, power bank…') }}" aria-label="{{ __('Search products') }}">
      <button type="submit" aria-label="{{ __('Search') }}">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
      </button>
    </form>
  </div>

  <div class="acts">
    <a href="{{ auth()->check() ? url('/dashboard') : route('login') }}" class="ico">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-7 8-7s8 2.6 8 7"/></svg>
      <span class="lbl">{{ auth()->check() ? __('Account') : __('Log in') }}</span>
    </a>
    <a href="#" class="ico">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M20.8 5.6a5 5 0 00-7.1 0L12 7.3l-1.7-1.7a5 5 0 10-7.1 7.1L12 21.5l8.8-8.8a5 5 0 000-7.1z"/></svg>
      <span class="lbl">{{ __('Wishlist') }}</span>
    </a>
    <a href="{{ route('cart.index') }}" class="ico cartpill">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M6 7h13l-1.4 9.3a2 2 0 01-2 1.7H9.4a2 2 0 01-2-1.7L5.6 4H3"/><circle cx="10" cy="20.5" r="1.2" fill="currentColor" stroke="none"/><circle cx="17" cy="20.5" r="1.2" fill="currentColor" stroke="none"/></svg>
      <span class="cnt" id="cartCount" @if (($cartCount ?? 0) < 1) style="display:none" @endif>{{ $cartCount ?? 0 }}</span>
      <span class="lbl">{{ __('Cart') }}</span>
    </a>
  </div>
</div></header>

<nav class="nav"><div class="wrap">
  <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'on' : '' }}">{{ __('Home') }}</a>
  <a href="{{ route('shop') }}" class="{{ request()->routeIs('shop') ? 'on' : '' }}">{{ __('All products') }}</a>
  @foreach ($navCategories as $parent)
    @foreach ($parent->children as $child)
      <a href="{{ route('shop.category', $child) }}"
         class="{{ request()->routeIs('shop.category') && request()->route('category')?->id === $child->id ? 'on' : '' }}">{{ $child->name }}</a>
    @endforeach
  @endforeach
  @if (!empty($settings['store_whatsapp']))
    <a href="https://wa.me/{{ $settings['store_whatsapp'] }}" class="deal" target="_blank" rel="noopener">{{ __('Order on WhatsApp') }}</a>
  @endif
</div></nav>
