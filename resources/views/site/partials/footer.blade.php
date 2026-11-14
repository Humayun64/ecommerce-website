@include('site.partials.blog-strip')

@php
  $ftWhatsapp = preg_replace('/\D/', '', (string) ($settings['store_whatsapp'] ?? ''));
  $ftSocial   = array_filter([
      'facebook'  => $settings['facebook_url'] ?? null,
      'instagram' => $settings['instagram_url'] ?? null,
      'youtube'   => $settings['youtube_url'] ?? null,
      'whatsapp'  => $ftWhatsapp ? 'https://wa.me/' . $ftWhatsapp : null,
  ]);
@endphp

<footer class="ft">

  {{-- what every order comes with --}}
  <div class="ft-perks">
    <div class="wrap">
      <div class="ft-perk p1">
        <span class="ft-perk-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2.5" y="6" width="19" height="12" rx="2"/><circle cx="12" cy="12" r="2.6"/><path d="M6 9.5v5M18 9.5v5"/></svg></span>
        <span><b>{{ __('Cash on delivery') }}</b>{{ __('Pay when the parcel reaches you') }}</span>
      </div>
      <div class="ft-perk p2">
        <span class="ft-perk-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3 5 5.8v5.4c0 4.3 2.9 7.7 7 9.3 4.1-1.6 7-5 7-9.3V5.8z"/><path d="m9.2 12 2 2 3.6-3.9"/></svg></span>
        <span><b>{{ __('Sealed and genuine') }}</b>{{ __('Imported in our own name') }}</span>
      </div>
      <div class="ft-perk p3">
        <span class="ft-perk-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 7h11v9H2zM13 10h4l3 3v3h-7z"/><circle cx="6.5" cy="17.5" r="1.8"/><circle cx="16.5" cy="17.5" r="1.8"/></svg></span>
        <span><b>{{ __('Delivery across Bangladesh') }}</b>{{ __('Track your parcel any time') }}</span>
      </div>
      <div class="ft-perk p4">
        <span class="ft-perk-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 9h11a5 5 0 0 1 0 10H8"/><path d="M8 5 4 9l4 4"/></svg></span>
        <span><b>{{ __('Returns and refunds') }}</b>{{ __('Ask from your order page') }}</span>
      </div>
    </div>
  </div>

  <div class="ft-main">
    <div class="wrap">

      @if ($ftWhatsapp || !empty($settings['store_phone']))
        <div class="ft-cta">
          <div>
            <h3>{{ __('Not sure what to order?') }}</h3>
            <p>{{ __('Tell us your skin concern or what you need — we will point you to the right product.') }}</p>
          </div>
          <div class="ft-cta-act">
            @if ($ftWhatsapp)
              <a href="https://wa.me/{{ $ftWhatsapp }}" target="_blank" rel="noopener" class="ft-btn ft-btn-wa">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.5 11.8a8.5 8.5 0 0 1-12.6 7.4L3.5 20.5l1.3-4.2a8.5 8.5 0 1 1 15.7-4.5z"/></svg>
                {{ __('Chat on WhatsApp') }}
              </a>
            @endif
            @if (!empty($settings['store_phone']))
              <a href="tel:{{ $settings['store_phone'] }}" class="ft-btn ft-btn-line">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linejoin="round" aria-hidden="true"><path d="M5 4h4l2 5-2.5 1.5a12 12 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2.2 2A17 17 0 0 1 3 6.2 2 2 0 0 1 5 4z"/></svg>
                {{ $settings['store_phone'] }}
              </a>
            @endif
          </div>
        </div>
      @endif

      <div class="fg">
        <div class="ft-brand">
          <div class="fb">
            <svg class="mark" viewBox="0 0 44 44" aria-hidden="true">
              <circle cx="22" cy="22" r="20.5" fill="none" stroke="#DFA327" stroke-width="2.4"/>
              <circle cx="22" cy="22" r="13" fill="none" stroke="#DFA327" stroke-width="1.4" opacity=".6"/>
              <ellipse cx="22" cy="22" rx="6" ry="13" fill="none" stroke="#DFA327" stroke-width="1.2" opacity=".6"/>
              <path d="M9.6 17.5h24.8M9.6 26.5h24.8" stroke="#DFA327" stroke-width="1.2" opacity=".6"/>
            </svg>
            <div class="bname">{{ $settings['store_name'] ?? 'AMJR Global' }}</div>
          </div>

          @if (!empty($settings['footer_about']))
            <p class="fab">{{ $settings['footer_about'] }}</p>
          @endif

          <ul class="fc">
            @if (!empty($settings['store_address']))
              <li><span class="ft-dot"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M12 21s7-6.2 7-11a7 7 0 10-14 0c0 4.8 7 11 7 11z"/><circle cx="12" cy="10" r="2.4"/></svg></span>{{ $settings['store_address'] }}</li>
            @endif
            @if (!empty($settings['store_phone']))
              <li><span class="ft-dot"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M5 4h4l2 5-2.5 1.5a12 12 0 005 5L15 13l5 2v4a2 2 0 01-2.2 2A17 17 0 013 6.2 2 2 0 015 4z"/></svg></span><a href="tel:{{ $settings['store_phone'] }}">{{ $settings['store_phone'] }}</a></li>
            @endif
            @if (!empty($settings['store_email']))
              <li><span class="ft-dot"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><rect x="2.5" y="5" width="19" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg></span><a href="mailto:{{ $settings['store_email'] }}">{{ $settings['store_email'] }}</a></li>
            @endif
          </ul>
        </div>

        @foreach ([1, 2] as $column)
          @php $links = ($footerMenu ?? collect())->get($column, collect()); @endphp
          {{-- A column with no links is left out, rather than showing a bare heading. --}}
          @continue($links->isEmpty())
          <div>
            <h4>{{ $settings['footer_col' . $column . '_title'] ?? ($column === 1 ? __('Shop') : __('Your order')) }}</h4>
            <ul class="ft-links">
              @foreach ($links as $item)
                <li>
                  <a href="{{ $item->href }}" @if ($item->new_tab) target="_blank" rel="noopener" @endif>{{ $item->label }}</a>
                </li>
              @endforeach
            </ul>
          </div>
        @endforeach

        <div>
          <h4>{{ __('Follow us') }}</h4>
          @if ($ftSocial)
            <div class="ft-social">
              @foreach ($ftSocial as $network => $url)
                <a href="{{ $url }}" target="_blank" rel="noopener" class="ft-soc s-{{ $network }}">
                  <span class="ft-soc-ic">
                    @if ($network === 'facebook')
                      <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M13.5 21v-7.5h2.5l.4-3h-2.9V8.6c0-.9.3-1.5 1.6-1.5h1.4V4.4c-.3 0-1.1-.1-2.1-.1-2.2 0-3.7 1.3-3.7 3.8v2.4H8.2v3h2.5V21z"/></svg>
                    @elseif ($network === 'instagram')
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><rect x="3.5" y="3.5" width="17" height="17" rx="5"/><circle cx="12" cy="12" r="3.8"/><circle cx="17.2" cy="6.8" r=".9" fill="currentColor"/></svg>
                    @elseif ($network === 'youtube')
                      <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M21.6 8.2a2.5 2.5 0 0 0-1.8-1.8C18.3 6 12 6 12 6s-6.3 0-7.8.4A2.5 2.5 0 0 0 2.4 8.2C2 9.7 2 12 2 12s0 2.3.4 3.8a2.5 2.5 0 0 0 1.8 1.8C5.7 18 12 18 12 18s6.3 0 7.8-.4a2.5 2.5 0 0 0 1.8-1.8c.4-1.5.4-3.8.4-3.8s0-2.3-.4-3.8zM10 14.6V9.4l4.5 2.6z"/></svg>
                    @else
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.5 11.8a8.5 8.5 0 0 1-12.6 7.4L3.5 20.5l1.3-4.2a8.5 8.5 0 1 1 15.7-4.5z"/></svg>
                    @endif
                  </span>
                  {{ ['facebook' => 'Facebook', 'instagram' => 'Instagram', 'youtube' => 'YouTube', 'whatsapp' => 'WhatsApp'][$network] }}
                </a>
              @endforeach
            </div>
          @endif
        </div>
      </div>

      <div class="pay">
        <div>&copy; {{ date('Y') }} {{ $settings['store_name'] ?? 'AMJR Global' }}. {{ __('All rights reserved.') }}</div>
        <div class="pl">
          <span class="chip c-cod">{{ __('Cash on delivery') }}</span>
          <span class="chip c-bkash">bKash</span><span class="chip c-nagad">Nagad</span><span class="chip c-rocket">Rocket</span>
          <span class="chip c-visa">Visa</span><span class="chip c-mc">Mastercard</span>
        </div>
      </div>

    </div>
  </div>

  <a href="#" class="ft-top" id="ftTop" aria-label="{{ __('Back to top') }}">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 19V5M5.5 11.5 12 5l6.5 6.5"/></svg>
  </a>
</footer>

<script>
(function () {
  var top = document.getElementById('ftTop');
  if (!top) return;
  // Shown once the visitor has scrolled a screen or so, and tucked away
  // again at the very bottom so it never sits on top of the payment row.
  function show() {
    var fromBottom = document.documentElement.scrollHeight - (window.scrollY + window.innerHeight);
    top.classList.toggle('on', window.scrollY > 600 && fromBottom > 110);
  }
  window.addEventListener('scroll', show, { passive: true });
  window.addEventListener('resize', show, { passive: true });
  show();
  top.addEventListener('click', function (e) {
    e.preventDefault();
    var calm = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    window.scrollTo({ top: 0, behavior: calm ? 'auto' : 'smooth' });
  });
})();
</script>
