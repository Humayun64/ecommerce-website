<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title', 'Admin') — AMJR Global</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@75..125,400..800&family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ filemtime(public_path('css/admin.css')) }}">
@stack('head')
</head>
<body>
<div class="shell">

  <aside class="side">
    <div class="side-brand">
      <svg width="34" height="34" viewBox="0 0 44 44">
        <circle cx="22" cy="22" r="20.5" fill="none" stroke="#DFA327" stroke-width="2.4"/>
        <circle cx="22" cy="22" r="13" fill="none" stroke="#DFA327" stroke-width="1.4" opacity=".6"/>
        <ellipse cx="22" cy="22" rx="6" ry="13" fill="none" stroke="#DFA327" stroke-width="1.2" opacity=".6"/>
        <path d="M9.6 17.5h24.8M9.6 26.5h24.8" stroke="#DFA327" stroke-width="1.2" opacity=".6"/>
      </svg>
      <div><div class="bn">AMJR Global</div><div class="bt">Admin panel</div></div>
    </div>

    <nav>
      <div class="grp">Overview</div>
      <a href="{{ route('admin.dashboard') }}" class="item {{ request()->routeIs('admin.dashboard') ? 'on' : '' }}">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="8" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="11" width="7" height="10" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/></svg>
        Dashboard
      </a>

      <a href="{{ route('admin.reports.index') }}" class="item {{ request()->routeIs('admin.reports.*') ? 'on' : '' }}">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/></svg>
        Reports
      </a>
      <a href="{{ route('admin.expenses.index') }}" class="item {{ request()->routeIs('admin.expenses.*') ? 'on' : '' }}">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"><rect x="3" y="6" width="18" height="13" rx="2"/><path d="M3 10h18M7 15h4"/></svg>
        Expenses
      </a>

      <div class="grp">Selling</div>
      <a href="{{ route('admin.orders.index') }}" class="item {{ request()->routeIs('admin.orders.*') ? 'on' : '' }}">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 2.5h12l2 5v13a1 1 0 01-1 1H5a1 1 0 01-1-1v-13z"/><path d="M4 7.5h16M9.5 11.5a2.5 2.5 0 005 0"/></svg>
        Orders
        <span class="side-tag" data-alert="orders" @if (($alertCounts['orders'] ?? 0) < 1) hidden @endif>{{ $alertCounts['orders'] ?? 0 }}</span>
      </a>
      <a href="{{ route('admin.payments.index') }}" class="item {{ request()->routeIs('admin.payments.*') ? 'on' : '' }}">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2.5" y="6" width="19" height="12" rx="2"/><path d="M2.5 10h19"/><path d="M6 14.5h3"/></svg>
        Payments
        <span class="side-tag" data-alert="payments" @if (($alertCounts['payments'] ?? 0) < 1) hidden @endif>{{ $alertCounts['payments'] ?? 0 }}</span>
      </a>
      <a href="{{ route('admin.returns.index') }}" class="item {{ request()->routeIs('admin.returns.*') ? 'on' : '' }}">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3.5 9.5h13a4 4 0 010 8H9"/><path d="M7 5.5l-3.5 4 3.5 4"/></svg>
        Returns
        <span class="side-tag" data-alert="returns" @if (($alertCounts['returns'] ?? 0) < 1) hidden @endif>{{ $alertCounts['returns'] ?? 0 }}</span>
      </a>
      <a href="{{ route('admin.coupons.index') }}" class="item {{ request()->routeIs('admin.coupons.*') ? 'on' : '' }}">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 9.5V6.5a1 1 0 011-1h16a1 1 0 011 1v3a2.5 2.5 0 000 5v3a1 1 0 01-1 1H4a1 1 0 01-1-1v-3a2.5 2.5 0 000-5z"/><path d="M14 6.5v1.5M14 11v2M14 16v1.5"/></svg>
        Coupons
      </a>

      <a href="{{ route('admin.customers.index') }}" class="item {{ request()->routeIs('admin.customers.*') ? 'on' : '' }}">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="8" r="3.6"/><path d="M2.5 20c0-3.9 2.9-6.4 6.5-6.4s6.5 2.5 6.5 6.4"/><path d="M16.5 5.2a3.4 3.4 0 010 6M18 13.9c2.2.6 3.5 2.4 3.5 5"/></svg>
        Customers
      </a>
      <a href="{{ route('admin.reviews.index') }}" class="item {{ request()->routeIs('admin.reviews.*') ? 'on' : '' }}">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 2.6l2.9 5.9 6.5.9-4.7 4.6 1.1 6.5L12 17.4 6.2 20.5l1.1-6.5L2.6 9.4l6.5-.9z"/></svg>
        Reviews
        <span class="side-tag" data-alert="reviews" @if (($alertCounts['reviews'] ?? 0) < 1) hidden @endif>{{ $alertCounts['reviews'] ?? 0 }}</span>
      </a>

      <div class="grp">Catalog</div>
      <a href="{{ route('admin.products.index') }}" class="item {{ request()->routeIs('admin.products.*') ? 'on' : '' }}">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20.5 7.5v9l-8.5 4.5-8.5-4.5v-9L12 3z"/><path d="M3.5 7.5L12 12l8.5-4.5M12 12v9"/></svg>
        Products
      </a>
      <a href="{{ route('admin.categories.index') }}" class="item {{ request()->routeIs('admin.categories.*') ? 'on' : '' }}">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 6h18M7 12h14M11 18h10"/><circle cx="3.5" cy="12" r="1.2" fill="currentColor" stroke="none"/><circle cx="7.5" cy="18" r="1.2" fill="currentColor" stroke="none"/></svg>
        Categories
      </a>
      <a href="{{ route('admin.brands.index') }}" class="item {{ request()->routeIs('admin.brands.*') ? 'on' : '' }}">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 2.5l2.9 6 6.6.9-4.8 4.6 1.2 6.5L12 17.4 6.1 20.5l1.2-6.5L2.5 9.4l6.6-.9z"/></svg>
        Brands
      </a>
      <a href="{{ route('admin.attributes.index') }}" class="item {{ request()->routeIs('admin.attributes.*') ? 'on' : '' }}">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
        Attributes
      </a>

      <div class="grp">Content</div>
      <a href="{{ route('admin.posts.index') }}" class="item {{ request()->routeIs('admin.posts.*', 'admin.blog-categories.*') ? 'on' : '' }}">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 4.5h11a2 2 0 012 2V20a1.5 1.5 0 01-1.5 1.5h-10A1.5 1.5 0 014 20z"/><path d="M17 8.5h1.5A1.5 1.5 0 0120 10v9.5a2 2 0 01-2 2M7.5 9h6M7.5 13h6M7.5 17h3"/></svg>
        Blog
      </a>
      <a href="{{ route('admin.pages.index') }}" class="item {{ request()->routeIs('admin.pages.*') ? 'on' : '' }}">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 2.5H6.5a1 1 0 00-1 1v17a1 1 0 001 1h11a1 1 0 001-1V7z"/><path d="M14 2.5V7h4.5M8.5 12h7M8.5 16h7"/></svg>
        Pages
      </a>
      <a href="{{ route('admin.menus.index') }}" class="item {{ request()->routeIs('admin.menus.*') ? 'on' : '' }}">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M8 6h13M8 12h13M8 18h13"/><circle cx="3.5" cy="6" r="1.3" fill="currentColor" stroke="none"/><circle cx="3.5" cy="12" r="1.3" fill="currentColor" stroke="none"/><circle cx="3.5" cy="18" r="1.3" fill="currentColor" stroke="none"/></svg>
        Menus &amp; footer
      </a>

      <div class="grp">Store</div>
      <a href="{{ route('admin.delivery.index') }}" class="item {{ request()->routeIs('admin.delivery.*') ? 'on' : '' }}">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="1.5" y="6" width="13" height="11" rx="1.5"/><path d="M14.5 10h4l3 3.2V17h-7z"/><circle cx="6" cy="18.5" r="1.8"/><circle cx="17.5" cy="18.5" r="1.8"/></svg>
        Delivery
      </a>
      <a href="{{ route('admin.messages.index') }}" class="item {{ request()->routeIs('admin.messages.*') ? 'on' : '' }}">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2.5" y="4.5" width="19" height="15" rx="2"/><path d="M3 6.5l9 6 9-6"/></svg>
        Messages
        <span class="side-tag" data-alert="messages" @if (($alertCounts['messages'] ?? 0) < 1) hidden @endif>{{ $alertCounts['messages'] ?? 0 }}</span>
      </a>
      <a href="{{ route('admin.contact.edit') }}" class="item {{ request()->routeIs('admin.contact.*') ? 'on' : '' }}">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3.5" width="18" height="17" rx="2"/><circle cx="12" cy="10" r="2.6"/><path d="M7.5 17a4.6 4.6 0 019 0"/></svg>
        Contact page
      </a>
      <a href="{{ route('admin.email.edit') }}" class="item {{ request()->routeIs('admin.email.*') ? 'on' : '' }}">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"><rect x="2.5" y="5" width="19" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg>
        Email
      </a>
      <a href="{{ route('admin.sms.index') }}" class="item {{ request()->routeIs('admin.sms.*') ? 'on' : '' }}">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 11.5a8 8 0 01-11.6 7.1L4 20.5l1.9-5A8 8 0 1121 11.5z"/><path d="M8.5 11.5h.01M12 11.5h.01M15.5 11.5h.01"/></svg>
        SMS
      </a>
      <a href="{{ route('admin.marketing.index') }}" class="item {{ request()->routeIs('admin.marketing.*') ? 'on' : '' }}">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 10.5v3a1 1 0 001 1h2.5l5 4V5.5l-5 4H4a1 1 0 00-1 1z"/><path d="M16 9.5a4 4 0 010 5M18.8 6.8a8 8 0 010 10.4"/></svg>
        Marketing &amp; Ads
      </a>
      <a href="{{ route('admin.couriers.index') }}" class="item {{ request()->routeIs('admin.couriers.*') ? 'on' : '' }}">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"><path d="M2 7h11v9H2zM13 10h4l3 3v3h-7z"/><circle cx="6.5" cy="17.5" r="1.8"/><circle cx="16.5" cy="17.5" r="1.8"/></svg>
        Couriers
      </a>
      <a href="{{ route('admin.payment-methods.index') }}" class="item {{ request()->routeIs('admin.payment-methods.*') ? 'on' : '' }}">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="8.5" cy="12" r="2.2"/><path d="M13 10h5M13 14h5"/></svg>
        Payment methods
      </a>
      <a href="{{ route('admin.settings.edit') }}" class="item {{ request()->routeIs('admin.settings.*') ? 'on' : '' }}">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="3.2"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3M4.9 4.9l2.1 2.1M17 17l2.1 2.1M19.1 4.9L17 7M7 17l-2.1 2.1"/></svg>
        Settings
      </a>
    </nav>

    <div class="side-foot">
      <a href="{{ url('/') }}" target="_blank">View storefront</a>
    </div>
  </aside>

  <script>
  /* Keep the menu where it was. Every click loads a new page, which used
     to put the menu back at the top — so the place is remembered, and the
     current item is brought into view if it is still off-screen. */
  (function () {
    var KEY = 'amjrSideScroll';
    var boxes = [document.querySelector('.side'), document.querySelector('.side nav')].filter(Boolean);

    try {
      var saved = JSON.parse(sessionStorage.getItem(KEY) || 'null');
      if (saved) boxes.forEach(function (box, i) { var at = saved[i] || [0, 0]; box.scrollTop = at[0] || 0; box.scrollLeft = at[1] || 0; });
    } catch (e) {}

    var on = document.querySelector('.side .item.on');
    if (on) {
      var r = on.getBoundingClientRect();
      // On a phone the menu is a strip that scrolls sideways instead.
      if (r.top < 0 || r.bottom > window.innerHeight || r.left < 0 || r.right > window.innerWidth) {
        on.scrollIntoView({ block: 'nearest', inline: 'center' });
      }
    }

    function save() {
      try { sessionStorage.setItem(KEY, JSON.stringify(boxes.map(function (box) { return [box.scrollTop, box.scrollLeft]; }))); } catch (e) {}
    }
    boxes.forEach(function (box) { box.addEventListener('scroll', save, { passive: true }); });
    window.addEventListener('pagehide', save);
  })();
  </script>

  <div class="main">
    <div class="top">
      <h1>@yield('title', 'Dashboard')</h1>
      <div class="who">
        <div class="al-wrap">
          <button type="button" class="al-bell" id="alBell" aria-haspopup="true" aria-expanded="false" aria-label="Notifications">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 8-3 8h18s-3-1-3-8"/><path d="M13.7 20a2 2 0 0 1-3.4 0"/></svg>
            <span class="al-count" id="alCount" hidden>0</span>
          </button>
          <div class="al-panel" id="alPanel" hidden>
            <div class="al-head">
              <b>Waiting for you</b>
              <label class="al-mute"><input type="checkbox" id="alSound" checked> Sound</label>
            </div>
            <div class="al-list" id="alList"><p class="al-empty">Nothing is waiting. You are all caught up.</p></div>
            <div class="al-foot">
              <button type="button" class="al-link" id="alTest">Test the sound</button>
              <button type="button" class="al-link" id="alDesk" hidden>Turn on desktop alerts</button>
            </div>
          </div>
        </div>
        <div class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
        <div>{{ auth()->user()->name }}</div>
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button class="btn btn-line btn-sm" type="submit">Log out</button>
        </form>
      </div>
    </div>

    <div class="content">
      @if (session('status'))
        <div class="alert alert-ok">{{ session('status') }}</div>
      @endif
      @if (session('error'))
        <div class="alert alert-bad">{{ session('error') }}</div>
      @endif

      @yield('content')
    </div>
  </div>

</div>


<div class="al-toasts" id="alToasts" aria-live="polite"></div>

<script>
/* Admin notifications: live badges, the bell, a toast and a sound when
   something new arrives. The page asks the server every 20 seconds. */
(function () {
  var ENDPOINT = @json(route('admin.alerts'));
  var KEY = 'amjrAlerts';
  var KINDS = ['orders', 'payments', 'returns', 'messages', 'reviews'];
  var TITLE = document.title;

  var bell = document.getElementById('alBell');
  var panel = document.getElementById('alPanel');
  var count = document.getElementById('alCount');
  var list = document.getElementById('alList');
  var toasts = document.getElementById('alToasts');
  var soundBox = document.getElementById('alSound');
  var deskBtn = document.getElementById('alDesk');
  if (!bell || !panel) return;

  var state = { seen: {}, muted: false };
  try { state = Object.assign(state, JSON.parse(localStorage.getItem(KEY) || '{}')); } catch (e) {}
  if (!state.seen) state.seen = {};
  function save() { try { localStorage.setItem(KEY, JSON.stringify(state)); } catch (e) {} }

  soundBox.checked = !state.muted;
  soundBox.addEventListener('change', function () { state.muted = !soundBox.checked; save(); });

  /* ---- sound: made in the browser, so there is no audio file to load ---- */
  var ctx = null;
  function unlock() {
    try {
      ctx = ctx || new (window.AudioContext || window.webkitAudioContext)();
      if (ctx.state === 'suspended') ctx.resume();
    } catch (e) {}
  }
  ['click', 'keydown', 'touchstart'].forEach(function (ev) { document.addEventListener(ev, unlock, { passive: true }); });
  unlock();

  function note(freq, at, length, volume) {
    var osc = ctx.createOscillator(), gain = ctx.createGain();
    osc.type = 'sine';
    osc.frequency.value = freq;
    gain.gain.setValueAtTime(0.0001, ctx.currentTime + at);
    gain.gain.exponentialRampToValueAtTime(volume, ctx.currentTime + at + 0.02);
    gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + at + length);
    osc.connect(gain); gain.connect(ctx.destination);
    osc.start(ctx.currentTime + at);
    osc.stop(ctx.currentTime + at + length + 0.05);
  }

  /* A rising three-note chime for an order; one soft note for anything else. */
  function chime(isOrder, force) {
    if ((state.muted && !force) || !ctx || ctx.state !== 'running') return false;
    try {
      if (isOrder) { note(784, 0, 0.35, 0.28); note(988, 0.16, 0.35, 0.28); note(1319, 0.32, 0.6, 0.3); }
      else { note(880, 0, 0.4, 0.2); }
      return true;
    } catch (e) { return false; }
  }

  document.getElementById('alTest').addEventListener('click', function () {
    unlock();
    setTimeout(function () {
      if (!chime(true, true)) toast('Sound is blocked', 'Allow sound for this site in your browser, then try again.', null, 'orders');
    }, 120);
  });

  /* ---- desktop alerts (optional, asks the browser for permission) ---- */
  if ('Notification' in window && Notification.permission === 'default') {
    deskBtn.hidden = false;
    deskBtn.addEventListener('click', function () {
      Notification.requestPermission().then(function () { deskBtn.hidden = true; });
    });
  }
  function desktop(title, body, url) {
    try {
      if (!('Notification' in window) || Notification.permission !== 'granted' || !document.hidden) return;
      var n = new Notification(title, { body: body, tag: 'amjr-admin' });
      n.onclick = function () { window.focus(); if (url) location.href = url; n.close(); };
    } catch (e) {}
  }

  /* ---- toast ---- */
  function toast(title, body, url, kind) {
    var el = document.createElement(url ? 'a' : 'div');
    if (url) el.href = url;
    el.className = 'al-toast k-' + kind;
    var b = document.createElement('b'); b.textContent = title;
    var s = document.createElement('span'); s.textContent = body;
    el.appendChild(b); el.appendChild(s);
    toasts.appendChild(el);
    setTimeout(function () { el.classList.add('out'); setTimeout(function () { el.remove(); }, 400); }, 9000);
  }

  /* ---- bell ---- */
  function open(yes) {
    panel.hidden = !yes;
    bell.setAttribute('aria-expanded', yes ? 'true' : 'false');
  }
  bell.addEventListener('click', function (e) { e.stopPropagation(); open(panel.hidden); });
  panel.addEventListener('click', function (e) { e.stopPropagation(); });
  document.addEventListener('click', function () { open(false); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') open(false); });

  function render(items) {
    list.textContent = '';
    if (!items.length) {
      var p = document.createElement('p'); p.className = 'al-empty';
      p.textContent = 'Nothing is waiting. You are all caught up.';
      list.appendChild(p);
      return;
    }
    items.forEach(function (it) {
      var a = document.createElement('a');
      a.href = it.url; a.className = 'al-item k-' + it.kind;
      var dot = document.createElement('i');
      var box = document.createElement('span');
      var b = document.createElement('b'); b.textContent = it.title;
      var s = document.createElement('small'); s.textContent = it.sub + (it.ago ? ' · ' + it.ago : '');
      box.appendChild(b); box.appendChild(s);
      a.appendChild(dot); a.appendChild(box);
      list.appendChild(a);
    });
  }

  /* ---- apply one answer from the server ---- */
  function apply(data) {
    var total = 0;
    KINDS.forEach(function (kind) {
      var n = (data.counts && data.counts[kind]) || 0;
      total += n;
      var tag = document.querySelector('.side-tag[data-alert="' + kind + '"]');
      if (tag) { tag.textContent = n > 99 ? '99+' : n; tag.hidden = n < 1; }
    });

    count.textContent = total > 99 ? '99+' : total;
    count.hidden = total < 1;
    document.title = (total > 0 ? '(' + total + ') ' : '') + TITLE;
    render(data.items || []);

    var fresh = [];
    KINDS.forEach(function (kind) {
      var latest = (data.latest && data.latest[kind]) || 0;
      // The first time this browser looks, nothing counts as new.
      if (state.seen[kind] === undefined) { state.seen[kind] = latest; return; }
      if (latest > state.seen[kind]) { fresh.push(kind); state.seen[kind] = latest; }
    });
    save();
    if (!fresh.length) return;

    var newest = function (kind) {
      return (data.items || []).filter(function (i) { return i.kind === kind; })[0];
    };

    fresh.forEach(function (kind) {
      var it = newest(kind);
      var title = it ? it.title : 'Something new in ' + kind;
      var body = it ? it.sub : 'Open the ' + kind + ' page to see it.';
      toast(title, body, it ? it.url : null, kind);
      desktop(title, body, it ? it.url : null);
    });

    chime(fresh.indexOf('orders') !== -1, false);
    bell.classList.remove('ring'); void bell.offsetWidth; bell.classList.add('ring');
  }

  function poll() {
    fetch(ENDPOINT, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin', cache: 'no-store' })
      .then(function (r) { return r.ok ? r.json() : null; })
      .then(function (data) { if (data && data.counts) apply(data); })
      .catch(function () {});
  }

  poll();
  setInterval(poll, 20000);
  document.addEventListener('visibilitychange', function () { if (!document.hidden) poll(); });
})();
</script>

@stack('scripts')
</body>
</html>
