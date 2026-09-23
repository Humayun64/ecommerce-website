@include('site.partials.blog-strip')

<footer><div class="wrap">
  <div class="fg">
    <div>
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
          <li><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M12 21s7-6.2 7-11a7 7 0 10-14 0c0 4.8 7 11 7 11z"/><circle cx="12" cy="10" r="2.4"/></svg>{{ $settings['store_address'] }}</li>
        @endif
        @if (!empty($settings['store_phone']))
          <li><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M5 4h4l2 5-2.5 1.5a12 12 0 005 5L15 13l5 2v4a2 2 0 01-2.2 2A17 17 0 013 6.2 2 2 0 015 4z"/></svg><a href="tel:{{ $settings['store_phone'] }}">{{ $settings['store_phone'] }}</a></li>
        @endif
        @if (!empty($settings['store_email']))
          <li><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><rect x="2.5" y="5" width="19" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg><a href="mailto:{{ $settings['store_email'] }}">{{ $settings['store_email'] }}</a></li>
        @endif
      </ul>
    </div>

    @foreach ([1, 2] as $column)
      @php $links = ($footerMenu ?? collect())->get($column, collect()); @endphp
      <div>
        <h4>{{ $settings['footer_col' . $column . '_title'] ?? ($column === 1 ? __('Shop') : __('Your order')) }}</h4>
        <ul>
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
      <ul>
        @if (!empty($settings['facebook_url']))
          <li><a href="{{ $settings['facebook_url'] }}" target="_blank" rel="noopener">{{ __('Facebook') }}</a></li>
        @endif
        @if (!empty($settings['instagram_url']))
          <li><a href="{{ $settings['instagram_url'] }}" target="_blank" rel="noopener">{{ __('Instagram') }}</a></li>
        @endif
        @if (!empty($settings['youtube_url']))
          <li><a href="{{ $settings['youtube_url'] }}" target="_blank" rel="noopener">{{ __('YouTube') }}</a></li>
        @endif
        @if (!empty($settings['store_whatsapp']))
          <li><a href="https://wa.me/{{ $settings['store_whatsapp'] }}" target="_blank" rel="noopener">{{ __('WhatsApp') }}</a></li>
        @endif
      </ul>
    </div>
  </div>

  <div class="pay">
    <div>&copy; {{ date('Y') }} {{ $settings['store_name'] ?? 'AMJR Global' }}. {{ __('All rights reserved.') }}</div>
    <div class="pl">
      <span class="chip">{{ __('Cash on delivery') }}</span>
      <span class="chip">bKash</span><span class="chip">Nagad</span><span class="chip">Rocket</span>
      <span class="chip">Visa</span><span class="chip">Mastercard</span>
    </div>
  </div>
</div></footer>
