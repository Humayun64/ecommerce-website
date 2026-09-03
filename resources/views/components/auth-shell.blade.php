@props(['heading', 'sub' => null])

<div class="authpage">
  <div class="wrap authgrid">

    <div class="authcard">
      <h1>{{ $heading }}</h1>
      @if ($sub)<p class="authsub">{{ $sub }}</p>@endif

      @if (session('status'))
        <div class="authflash">{{ session('status') }}</div>
      @endif

      @if ($errors->any())
        <div class="warnbox bad" style="margin-bottom:18px">
          <ul style="margin:0;padding-left:18px">
            @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
          </ul>
        </div>
      @endif

      {{ $slot }}
    </div>

    <aside class="authside">
      <div class="authmark">
        <svg width="46" height="46" viewBox="0 0 44 44" aria-hidden="true">
          <circle cx="22" cy="22" r="20.5" fill="none" stroke="#DFA327" stroke-width="2.4"/>
          <circle cx="22" cy="22" r="13" fill="none" stroke="#DFA327" stroke-width="1.4" opacity=".6"/>
          <ellipse cx="22" cy="22" rx="6" ry="13" fill="none" stroke="#DFA327" stroke-width="1.2" opacity=".6"/>
          <path d="M9.6 17.5h24.8M9.6 26.5h24.8" stroke="#DFA327" stroke-width="1.2" opacity=".6"/>
        </svg>
      </div>

      <h2>{{ __('Why bother with an account?') }}</h2>

      <ul class="authlist">
        <li>
          <b>{{ __('Your orders in one place') }}</b>
          {{ __('See every order and where it has got to, without hunting for the order number.') }}
        </li>
        <li>
          <b>{{ __('Checkout in seconds') }}</b>
          {{ __('Save your address once and skip typing it every time.') }}
        </li>
        <li>
          <b>{{ __('Past orders come with you') }}</b>
          {{ __('Ordered as a guest before? Link those orders with one click using your phone number.') }}
        </li>
      </ul>

      <p class="authnote">
        {{ __('You can always order as a guest with cash on delivery — an account just makes it faster.') }}
      </p>
    </aside>

  </div>
</div>
