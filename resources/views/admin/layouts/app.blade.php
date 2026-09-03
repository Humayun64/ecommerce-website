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

      <a href="{{ route('admin.orders.index') }}" class="item {{ request()->routeIs('admin.orders.*') ? 'on' : '' }}">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 2.5h12l2 5v13a1 1 0 01-1 1H5a1 1 0 01-1-1v-13z"/><path d="M4 7.5h16M9.5 11.5a2.5 2.5 0 005 0"/></svg>
        Orders
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
      <a href="{{ route('admin.attributes.index') }}" class="item {{ request()->routeIs('admin.attributes.*') ? 'on' : '' }}">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
        Attributes
      </a>
      <a href="{{ route('admin.brands.index') }}" class="item {{ request()->routeIs('admin.brands.*') ? 'on' : '' }}">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 2.5l2.9 6 6.6.9-4.8 4.6 1.2 6.5L12 17.4 6.1 20.5l1.2-6.5L2.5 9.4l6.6-.9z"/></svg>
        Brands
      </a>

      <div class="grp">Store</div>
      <a href="{{ route('admin.settings.edit') }}" class="item {{ request()->routeIs('admin.settings.*') ? 'on' : '' }}">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="3.2"/><path d="M19.4 15a1.7 1.7 0 00.34 1.87l.06.06a2 2 0 11-2.83 2.83l-.06-.06a1.7 1.7 0 00-1.87-.34 1.7 1.7 0 00-1 1.56V21a2 2 0 11-4 0v-.1A1.7 1.7 0 008 19.3a1.7 1.7 0 00-1.87.34l-.06.06a2 2 0 11-2.83-2.83l.06-.06A1.7 1.7 0 003.64 15a1.7 1.7 0 00-1.56-1H2a2 2 0 110-4h.1A1.7 1.7 0 003.64 9a1.7 1.7 0 00-.34-1.87l-.06-.06a2 2 0 112.83-2.83l.06.06A1.7 1.7 0 008 4.64 1.7 1.7 0 009 3.08V3a2 2 0 114 0v.1A1.7 1.7 0 0015 4.64a1.7 1.7 0 001.87-.34l.06-.06a2 2 0 112.83 2.83l-.06.06A1.7 1.7 0 0019.36 9v0a1.7 1.7 0 001.56 1H21a2 2 0 110 4h-.1a1.7 1.7 0 00-1.5 1z"/></svg>
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
