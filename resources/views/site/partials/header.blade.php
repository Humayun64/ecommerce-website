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
    <a href="{{ auth()->check() ? route('account.dashboard') : route('login') }}" class="ico">
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

{{-- On a phone the menu bar becomes a hamburger; the links move into a slide-in drawer. --}}
<style>
.mn-toggle{display:none}

@media(max-width:900px){
  .nav>.wrap{display:flex;align-items:center}
  .nav>.wrap>a{display:none}
  .mn-toggle{
    display:inline-flex;align-items:center;gap:11px;
    background:transparent;border:0;padding:13px 0;margin:0;
    color:#fff;font:inherit;font-size:14.5px;font-weight:700;letter-spacing:.2px;
    cursor:pointer;-webkit-tap-highlight-color:transparent;
  }
  .mn-toggle .bars{position:relative;display:block;width:20px;height:14px;flex:0 0 auto}
  .mn-toggle .bars i{position:absolute;left:0;right:0;height:2px;border-radius:2px;background:#DFA327}
  .mn-toggle .bars i:nth-child(1){top:0}
  .mn-toggle .bars i:nth-child(2){top:6px}
  .mn-toggle .bars i:nth-child(3){top:12px}
}

.mn-drawer{position:fixed;inset:0;z-index:120;visibility:hidden;pointer-events:none}
.mn-drawer.is-open{visibility:visible;pointer-events:auto}
.mn-scrim{position:absolute;inset:0;background:rgba(12,28,54,.55);opacity:0;transition:opacity .24s ease}
.mn-drawer.is-open .mn-scrim{opacity:1}
.mn-panel{
  position:absolute;top:0;left:0;bottom:0;width:86%;max-width:340px;
  background:#fff;display:flex;flex-direction:column;
  transform:translateX(-100%);transition:transform .26s cubic-bezier(.22,.61,.36,1);
  box-shadow:0 0 44px rgba(12,28,54,.3);
}
.mn-drawer.is-open .mn-panel{transform:none}
@media(prefers-reduced-motion:reduce){.mn-panel,.mn-scrim{transition:none}}

.mn-head{
  background:#14305A;color:#fff;padding:15px 16px 15px 18px;
  display:flex;align-items:center;justify-content:space-between;gap:12px;flex:0 0 auto;
}
.mn-head b{font-size:16px;font-weight:800;letter-spacing:.2px}
.mn-head span{display:block;font-size:11.5px;font-weight:500;color:#C5CEDD;margin-top:2px}
.mn-x{background:transparent;border:0;color:#fff;cursor:pointer;padding:7px;line-height:0;border-radius:9px}
.mn-x:hover{background:rgba(255,255,255,.14)}

.mn-find{padding:14px 16px;border-bottom:1px solid #DDE0E6;flex:0 0 auto}
.mn-find form{display:flex}
.mn-find input{
  flex:1;min-width:0;font:inherit;font-size:14px;padding:10px 12px;
  border:1px solid #DDE0E6;border-right:0;border-radius:9px 0 0 9px;outline:0;
}
.mn-find input:focus{border-color:#14305A}
.mn-find button{border:0;background:#14305A;color:#fff;padding:0 14px;border-radius:0 9px 9px 0;cursor:pointer;line-height:0}

.mn-links{flex:1 1 auto;overflow-y:auto;-webkit-overflow-scrolling:touch;padding:6px 0 10px}
.mn-lab{
  padding:14px 18px 6px;font-size:11px;font-weight:800;letter-spacing:.9px;
  text-transform:uppercase;color:#5B6270;
}
.mn-links a{
  display:flex;align-items:center;gap:10px;padding:13px 18px;
  color:#15181D;text-decoration:none;font-size:15px;font-weight:600;
  border-left:3px solid transparent;
}
.mn-links a:hover{background:#F4F5F7}
.mn-links a.on{color:#14305A;background:#F4F5F7;border-left-color:#DFA327}
.mn-links a svg{flex:0 0 auto;color:#5B6270}
.mn-links a.on svg{color:#14305A}
.mn-pill{
  margin-left:auto;background:#DFA327;color:#15181D;
  font-size:11px;font-weight:800;border-radius:999px;padding:2px 8px;
}
.mn-rule{height:1px;background:#DDE0E6;margin:10px 0}
.mn-foot{flex:0 0 auto;border-top:1px solid #DDE0E6;padding:14px 18px;display:grid;gap:11px}
.mn-foot a{display:flex;align-items:center;gap:9px;color:#5B6270;text-decoration:none;font-size:14px;font-weight:600}
.mn-foot a.call{color:#14305A;font-weight:700}
body.mn-lock{overflow:hidden}
</style>

<nav class="nav" id="siteNav" aria-label="{{ __('Main menu') }}"><div class="wrap">
  <button type="button" class="mn-toggle" id="mnToggle" aria-controls="mnDrawer" aria-expanded="false">
    <span class="bars" aria-hidden="true"><i></i><i></i><i></i></span>
    {{ __('Menu') }}
  </button>

  @forelse ($headerMenu ?? [] as $item)
    <a href="{{ $item->href }}"
       class="{{ request()->url() === $item->href ? 'on' : '' }} {{ $item->is_external ? 'deal' : '' }}"
       @if ($item->new_tab) target="_blank" rel="noopener" @endif>{{ $item->label }}</a>
  @empty
    <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'on' : '' }}">{{ __('Home') }}</a>
    <a href="{{ route('shop') }}" class="{{ request()->routeIs('shop') ? 'on' : '' }}">{{ __('All products') }}</a>
  @endforelse
</div></nav>

<div class="mn-drawer" id="mnDrawer">
  <div class="mn-scrim" data-mn-close></div>

  <aside class="mn-panel" role="dialog" aria-modal="true" aria-label="{{ __('Menu') }}">
    <div class="mn-head">
      <div>
        <b>{{ $settings['store_name'] ?? 'AMJR Global' }}</b>
        <span>{{ $settings['store_tagline'] ?? __('Sourced directly from Korea') }}</span>
      </div>
      <button type="button" class="mn-x" id="mnClose" aria-label="{{ __('Close menu') }}">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1"><path d="M6 6l12 12M18 6L6 18"/></svg>
      </button>
    </div>

    <div class="mn-find">
      <form action="{{ route('shop') }}" method="GET" role="search">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ __('Search products') }}" aria-label="{{ __('Search products') }}">
        <button type="submit" aria-label="{{ __('Search') }}">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
        </button>
      </form>
    </div>

    <div class="mn-links">
      <div class="mn-lab">{{ __('Shop') }}</div>
      @forelse ($headerMenu ?? [] as $item)
        <a href="{{ $item->href }}"
           class="{{ request()->url() === $item->href ? 'on' : '' }}"
           @if ($item->new_tab) target="_blank" rel="noopener" @endif>{{ $item->label }}</a>
      @empty
        <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'on' : '' }}">{{ __('Home') }}</a>
        <a href="{{ route('shop') }}" class="{{ request()->routeIs('shop') ? 'on' : '' }}">{{ __('All products') }}</a>
      @endforelse

      <div class="mn-rule"></div>
      <div class="mn-lab">{{ __('Your account') }}</div>

      <a href="{{ route('cart.index') }}">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M6 7h13l-1.4 9.3a2 2 0 01-2 1.7H9.4a2 2 0 01-2-1.7L5.6 4H3"/><circle cx="10" cy="20.5" r="1.2" fill="currentColor" stroke="none"/><circle cx="17" cy="20.5" r="1.2" fill="currentColor" stroke="none"/></svg>
        {{ __('Cart') }}
        @if (($cartCount ?? 0) > 0)<span class="mn-pill">{{ $cartCount }}</span>@endif
      </a>
      <a href="{{ auth()->check() ? route('account.dashboard') : route('login') }}">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-7 8-7s8 2.6 8 7"/></svg>
        {{ auth()->check() ? __('My account') : __('Log in') }}
      </a>
      <a href="{{ route('orders.track') }}">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M3 7h11v9H3zM14 10h4l3 3v3h-7z"/><circle cx="7" cy="18.5" r="1.6"/><circle cx="17.5" cy="18.5" r="1.6"/></svg>
        {{ __('Track your order') }}
      </a>
    </div>

    <div class="mn-foot">
      @if (!empty($settings['store_phone']))
        <a href="tel:{{ $settings['store_phone'] }}" class="call">
          <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 5c0-.6.4-1 1-1h3l1.6 4-2 1.4a12 12 0 006 6l1.4-2 4 1.6v3c0 .6-.4 1-1 1A15.5 15.5 0 014 5z"/></svg>
          {{ $settings['store_phone'] }}
        </a>
      @endif
      <a href="{{ route('shop') }}">{{ $settings['topbar_message'] ?? __('Cash on delivery across Bangladesh') }}</a>
    </div>
  </aside>
</div>

<script>
(function () {
  var drawer = document.getElementById('mnDrawer');
  var toggle = document.getElementById('mnToggle');
  if (!drawer || !toggle) return;

  var panel = drawer.querySelector('.mn-panel');
  var closer = document.getElementById('mnClose');
  var lastFocus = null;

  function focusables() {
    return panel.querySelectorAll('a[href], button, input, [tabindex]:not([tabindex="-1"])');
  }

  function open() {
    lastFocus = document.activeElement;
    drawer.classList.add('is-open');
    document.body.classList.add('mn-lock');
    toggle.setAttribute('aria-expanded', 'true');
    if (closer) closer.focus();
  }

  function close() {
    drawer.classList.remove('is-open');
    document.body.classList.remove('mn-lock');
    toggle.setAttribute('aria-expanded', 'false');
    if (lastFocus && lastFocus.focus) lastFocus.focus();
  }

  toggle.addEventListener('click', function () {
    if (drawer.classList.contains('is-open')) { close(); } else { open(); }
  });

  drawer.addEventListener('click', function (e) {
    if (e.target.hasAttribute('data-mn-close')) close();
  });

  if (closer) closer.addEventListener('click', close);

  document.addEventListener('keydown', function (e) {
    if (!drawer.classList.contains('is-open')) return;

    if (e.key === 'Escape') { close(); return; }

    // Keep tabbing inside the drawer while it is open.
    if (e.key !== 'Tab') return;
    var list = focusables();
    if (!list.length) return;
    var first = list[0];
    var last = list[list.length - 1];
    if (e.shiftKey && document.activeElement === first) {
      e.preventDefault();
      last.focus();
    } else if (!e.shiftKey && document.activeElement === last) {
      e.preventDefault();
      first.focus();
    }
  });

  // Back on a wide screen the bar shows its own links, so nothing should stay open.
  window.addEventListener('resize', function () {
    if (window.innerWidth > 900 && drawer.classList.contains('is-open')) close();
  });
})();
</script>
