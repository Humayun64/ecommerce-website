<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title', 'Admin') — AMJR Global</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@75..125,400..800&family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/admin.css') }}">
@stack('head')
<style>
.side .item .side-tag{
  margin-left:auto;background:#C5402B;color:#fff;font-size:11px;font-weight:700;
  border-radius:999px;padding:1px 8px;line-height:1.7;
}
</style>
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

      <div class="grp">Selling</div>
      <a href="{{ route('admin.orders.index') }}" class="item {{ request()->routeIs('admin.orders.*') ? 'on' : '' }}">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 2.5h12l2 5v13a1 1 0 01-1 1H5a1 1 0 01-1-1v-13z"/><path d="M4 7.5h16M9.5 11.5a2.5 2.5 0 005 0"/></svg>
        Orders
      </a>
      <a href="{{ route('admin.payments.index') }}" class="item {{ request()->routeIs('admin.payments.*') ? 'on' : '' }}">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2.5" y="6" width="19" height="12" rx="2"/><path d="M2.5 10h19"/><path d="M6 14.5h3"/></svg>
        Payments
        @if (($pendingPayments ?? 0) > 0)<span class="side-tag">{{ $pendingPayments }}</span>@endif
      </a>
      <a href="{{ route('admin.returns.index') }}" class="item {{ request()->routeIs('admin.returns.*') ? 'on' : '' }}">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3.5 9.5h13a4 4 0 010 8H9"/><path d="M7 5.5l-3.5 4 3.5 4"/></svg>
        Returns
        @if (($pendingReturns ?? 0) > 0)<span class="side-tag">{{ $pendingReturns }}</span>@endif
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

  <div class="main">
    <div class="top">
      <h1>@yield('title', 'Dashboard')</h1>
      <div class="who">
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

@stack('scripts')
</body>
</html>
