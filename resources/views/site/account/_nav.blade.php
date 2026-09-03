<aside class="acctnav">
  <div class="acctwho">
    <div class="acctav">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
    <div>
      <b>{{ auth()->user()->name }}</b>
      <small>{{ auth()->user()->phone ?: auth()->user()->email }}</small>
    </div>
  </div>

  <nav>
    <a href="{{ route('account.dashboard') }}" class="{{ request()->routeIs('account.dashboard') ? 'on' : '' }}">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="8" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="11" width="7" height="10" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/></svg>
      {{ __('Overview') }}
    </a>
    <a href="{{ route('account.orders') }}" class="{{ request()->routeIs('account.orders', 'account.order') ? 'on' : '' }}">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 2.5h12l2 5v13a1 1 0 01-1 1H5a1 1 0 01-1-1v-13z"/><path d="M4 7.5h16M9.5 11.5a2.5 2.5 0 005 0"/></svg>
      {{ __('My orders') }}
    </a>
    <a href="{{ route('account.addresses') }}" class="{{ request()->routeIs('account.addresses') ? 'on' : '' }}">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 21s7-6.2 7-11a7 7 0 10-14 0c0 4.8 7 11 7 11z"/><circle cx="12" cy="10" r="2.4"/></svg>
      {{ __('Addresses') }}
    </a>
    <a href="{{ route('orders.track') }}">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="1.5" y="6" width="13" height="11" rx="1.5"/><path d="M14.5 10h4l3 3.2V17h-7z"/><circle cx="6" cy="18.5" r="1.8"/><circle cx="17.5" cy="18.5" r="1.8"/></svg>
      {{ __('Track a parcel') }}
    </a>
    <a href="{{ route('profile.edit') }}" class="{{ request()->routeIs('profile.edit') ? 'on' : '' }}">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-7 8-7s8 2.6 8 7"/></svg>
      {{ __('Profile and password') }}
    </a>
    @if (auth()->user()->is_admin)
      <a href="{{ route('admin.dashboard') }}">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="3.2"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3M4.9 4.9l2.1 2.1M17 17l2.1 2.1M19.1 4.9L17 7M7 17l-2.1 2.1"/></svg>
        {{ __('Admin panel') }}
      </a>
    @endif
  </nav>

  <form method="POST" action="{{ route('logout') }}">
    @csrf
    <button type="submit" class="logoutbtn">{{ __('Log out') }}</button>
  </form>
</aside>
