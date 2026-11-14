@props(['heading', 'sub' => null])

<div class="authpage">
  <div class="wrap authgrid">

    <div class="authcard"
         data-show="{{ __('Show password') }}"
         data-hide="{{ __('Hide password') }}"
         data-caps="{{ __('Caps Lock is on') }}">
      <div class="aubadge" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
          <path d="M12 3 5 5.8v5.4c0 4.3 2.9 7.7 7 9.3 4.1-1.6 7-5 7-9.3V5.8z"/><path d="m9.2 12 2 2 3.6-3.9"/>
        </svg>
      </div>
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

      <p class="ausecure">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <rect x="4.5" y="10.5" width="15" height="9.5" rx="2"/><path d="M8 10.5V7.5a4 4 0 0 1 8 0v3"/>
        </svg>
        {{ __('Your password is never stored as plain text.') }}
      </p>
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

      <ul class="authlist rich">
        <li>
          <span class="auli"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3.5 8 12 3.5 20.5 8v8L12 20.5 3.5 16z"/><path d="M3.5 8 12 12.5 20.5 8M12 12.5v8"/></svg></span>
          <b>{{ __('Your orders in one place') }}</b>
          {{ __('See every order and where it has got to, without hunting for the order number.') }}
        </li>
        <li>
          <span class="auli"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M13 2.5 5 13.5h6l-1 8 8-11h-6z"/></svg></span>
          <b>{{ __('Checkout in seconds') }}</b>
          {{ __('Save your address once and skip typing it every time.') }}
        </li>
        <li>
          <span class="auli"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 14a4 4 0 0 0 5.7 0l3-3a4 4 0 0 0-5.7-5.7l-1 1"/><path d="M14 10a4 4 0 0 0-5.7 0l-3 3a4 4 0 0 0 5.7 5.7l1-1"/></svg></span>
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

<script>
/* Auth pages: field icons, show/hide password, Caps Lock hint, loading button.
   Everything here is an enhancement — with JavaScript off the forms still work. */
(function () {
  var card = document.querySelector('.authcard');
  if (!card) return;

  var ICONS = {
    email: '<path d="M3 6.5h18v11H3z"/><path d="m3.5 7 8.5 6.5L20.5 7"/>',
    password: '<rect x="4.5" y="10.5" width="15" height="9.5" rx="2"/><path d="M8 10.5V7.5a4 4 0 0 1 8 0v3"/>',
    tel: '<rect x="7" y="2.5" width="10" height="19" rx="2.2"/><path d="M11 18.5h2"/>',
    text: '<circle cx="12" cy="8" r="3.8"/><path d="M4.5 20.5c.9-3.8 4-5.8 7.5-5.8s6.6 2 7.5 5.8"/>'
  };
  var EYE = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
          + '<path d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12z"/><circle cx="12" cy="12" r="2.8"/>'
          + '<path class="slash" d="M4 4l16 16"/></svg>';
  var WARN = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
           + '<path d="M12 3 2.5 20h19z"/><path d="M12 10v4.5M12 17.3v.2"/></svg>';

  function svg(inner) {
    return '<svg class="auico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" '
         + 'stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' + inner + '</svg>';
  }

  var focused = document.activeElement;
  var inputs = card.querySelectorAll('.cofield input[type=text], .cofield input[type=email], .cofield input[type=tel], .cofield input[type=password]');

  Array.prototype.forEach.call(inputs, function (input) {
    var kind = input.type;
    var wrap = document.createElement('span');
    wrap.className = 'auwrap';
    input.parentNode.insertBefore(wrap, input);
    wrap.insertAdjacentHTML('beforeend', svg(ICONS[kind] || ICONS.text));
    wrap.appendChild(input);

    if (kind === 'email') {
      wrap.insertAdjacentHTML('beforeend', '<svg class="auok" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" '
        + 'stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12.5 4.5 4.5L19 7.5"/></svg>');
      var mark = function () { wrap.classList.toggle('isok', input.value !== '' && input.validity.valid); };
      input.addEventListener('input', mark);
      mark();
    }

    if (kind !== 'password') return;

    wrap.classList.add('haspw');
    var btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'pwtoggle';
    btn.setAttribute('aria-label', card.dataset.show);
    btn.setAttribute('aria-pressed', 'false');
    btn.innerHTML = EYE;
    wrap.appendChild(btn);

    btn.addEventListener('click', function () {
      var show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      btn.classList.toggle('on', show);
      btn.setAttribute('aria-pressed', show ? 'true' : 'false');
      btn.setAttribute('aria-label', show ? card.dataset.hide : card.dataset.show);
      input.focus();
    });

    var hint = document.createElement('div');
    hint.className = 'capshint';
    hint.setAttribute('role', 'status');
    hint.innerHTML = WARN + '<span></span>';
    hint.lastChild.textContent = card.dataset.caps;
    wrap.parentNode.insertBefore(hint, wrap.nextSibling);

    function caps(e) {
      if (e.getModifierState) hint.classList.toggle('show', e.getModifierState('CapsLock'));
    }
    input.addEventListener('keydown', caps);
    input.addEventListener('keyup', caps);
    input.addEventListener('blur', function () { hint.classList.remove('show'); });
  });

  // wrapping an input moves it in the page, which drops focus — put it back
  if (focused && focused !== document.body && card.contains(focused)) focused.focus();

  Array.prototype.forEach.call(card.querySelectorAll('form'), function (form) {
    form.addEventListener('submit', function () {
      // never submit a password field that is still switched to visible text
      Array.prototype.forEach.call(form.querySelectorAll('.haspw input'), function (i) { i.type = 'password'; });
      var b = form.querySelector(':scope > .btn-gold');
      if (b) b.classList.add('is-loading');
    });
  });

  // coming back with the browser Back button would leave the spinner on
  window.addEventListener('pageshow', function () {
    Array.prototype.forEach.call(card.querySelectorAll('.is-loading'), function (b) { b.classList.remove('is-loading'); });
  });
})();
</script>
