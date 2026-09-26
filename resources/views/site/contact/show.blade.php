@extends('site.layouts.app')

@section('title', $page->heading() . ' — ' . ($settings['store_name'] ?? 'AMJR Global'))
@section('meta_description', Str::limit(strip_tags($page->intro()), 155))

@push('head')<link rel="stylesheet" href="{{ asset('css/contact.css') }}">@endpush

@section('content')

<div class="ct-hero"><div class="wrap">
  <h1>{{ $page->heading() }}</h1>
  <p>{{ $page->intro() }}</p>
</div></div>

<div class="ct-wrap">
  <div class="ct-grid">

    {{-- ---------------- the form ---------------- --}}
    <div>
      @if (session('sent'))

        <div class="ct-sent">
          <b>{{ __('Thank you — your message is with us.') }}</b>
          <span>
            {{ $page->replyTime() }}
            @if ($page->phone())
              {{ __('If it is urgent, call :phone instead.', ['phone' => $page->phone()]) }}
            @endif
          </span>
        </div>

      @elseif (! $page->formEnabled())

        <div class="ct-off">
          {{ __('The message form is switched off at the moment.') }}
          @if ($page->phone())
            {{ __('Please call :phone and we will sort it out.', ['phone' => $page->phone()]) }}
          @endif
        </div>

      @else

        <div class="ct-card">
          <h2>{{ __('Send us a message') }}</h2>
          <p class="lede">{{ $page->replyTime() }}</p>

          <form method="POST" action="{{ route('contact.store') }}">
            @csrf
            <input type="hidden" name="opened" value="{{ $opened }}">

            {{-- Hidden from people; bots fill it in and give themselves away. --}}
            <div class="ct-hp" aria-hidden="true">
              <label for="website">{{ __('Leave this empty') }}</label>
              <input type="text" name="website" id="website" tabindex="-1" autocomplete="off">
            </div>

            <div class="ct-row">
              <div class="ct-f">
                <label for="name">{{ __('Your name') }} <i>*</i></label>
                <input type="text" name="name" id="name" value="{{ old('name', auth()->user()?->name) }}" required>
                @error('name')<div class="ct-err">{{ $message }}</div>@enderror
              </div>

              <div class="ct-f">
                <label for="phone">{{ __('Mobile number') }} <i>*</i></label>
                <input type="text" name="phone" id="phone" value="{{ old('phone', auth()->user()?->phone) }}"
                       placeholder="01XXXXXXXXX" required>
                @error('phone')<div class="ct-err">{{ $message }}</div>@enderror
              </div>
            </div>

            <div class="ct-row">
              <div class="ct-f">
                <label for="email">{{ __('Email') }} <span style="font-weight:400;color:var(--ink-mute)">({{ __('optional') }})</span></label>
                <input type="email" name="email" id="email" value="{{ old('email', auth()->user()?->email) }}">
                @error('email')<div class="ct-err">{{ $message }}</div>@enderror
              </div>

              <div class="ct-f">
                <label for="order_number">{{ __('Order number') }} <span style="font-weight:400;color:var(--ink-mute)">({{ __('optional') }})</span></label>
                <input type="text" name="order_number" id="order_number" value="{{ old('order_number') }}"
                       placeholder="AMJR-260923-1234">
                @error('order_number')<div class="ct-err">{{ $message }}</div>@enderror
              </div>
            </div>

            @if ($topics)
              <div class="ct-f">
                <label for="topic">{{ __('What is it about?') }}</label>
                <select name="topic" id="topic">
                  <option value="">{{ __('Choose one') }}</option>
                  @foreach ($topics as $topic)
                    <option value="{{ $topic }}" @selected(old('topic') === $topic)>{{ $topic }}</option>
                  @endforeach
                </select>
                @error('topic')<div class="ct-err">{{ $message }}</div>@enderror
              </div>
            @endif

            <div class="ct-f">
              <label for="message">{{ __('Your message') }} <i>*</i></label>
              <textarea name="message" id="message" required
                        placeholder="{{ __('Tell us what you need. The more detail, the faster we can help.') }}">{{ old('message') }}</textarea>
              @error('message')<div class="ct-err">{{ $message }}</div>@enderror
            </div>

            <button type="submit" class="btn btn-gold">{{ __('Send message') }}</button>
          </form>
        </div>

      @endif
    </div>

    {{-- ---------------- the details ---------------- --}}
    <div>
      <div class="ct-card">
        <h2>{{ __('Other ways to reach us') }}</h2>

        <div class="ct-ways">
          @if ($page->phone())
            <a href="tel:{{ $page->phone() }}" class="ct-way">
              <span class="ic">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 5c0-.6.4-1 1-1h3l1.6 4-2 1.4a12 12 0 006 6l1.4-2 4 1.6v3c0 .6-.4 1-1 1A15.5 15.5 0 014 5z"/></svg>
              </span>
              <span><b>{{ __('Call') }}</b><span>{{ $page->phone() }}</span></span>
            </a>
          @endif

          @if ($page->whatsapp())
            <a href="{{ $page->whatsapp() }}" target="_blank" rel="noopener" class="ct-way wa">
              <span class="ic">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 00-8.6 15l-1.3 4.7 4.8-1.3A10 10 0 1012 2zm0 18a8 8 0 01-4.1-1.1l-.3-.2-2.9.8.8-2.8-.2-.3A8 8 0 1112 20zm4.4-5.8c-.2-.1-1.4-.7-1.6-.8s-.4-.1-.5.1-.6.8-.8 1-.3.2-.5 0a6.6 6.6 0 01-3.2-2.8c-.2-.4.2-.4.6-1.2a.4.4 0 000-.4l-.7-1.7c-.2-.5-.4-.4-.6-.4h-.4a.9.9 0 00-.7.3A2.8 2.8 0 006 8.9a4.8 4.8 0 001 2.5 11 11 0 004.2 3.7c1.6.6 2.2.7 3 .6a2.5 2.5 0 001.7-1.2 2 2 0 00.1-1.2z"/></svg>
              </span>
              <span><b>{{ __('WhatsApp') }}</b><span>{{ __('Message us') }}</span></span>
            </a>
          @endif

          @if ($page->email())
            <a href="mailto:{{ $page->email() }}" class="ct-way">
              <span class="ic">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2.5" y="5" width="19" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg>
              </span>
              <span><b>{{ __('Email') }}</b><span>{{ $page->email() }}</span></span>
            </a>
          @endif

          @if ($page->address())
            <div class="ct-way">
              <span class="ic">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 21s7-5.7 7-11a7 7 0 10-14 0c0 5.3 7 11 7 11z"/><circle cx="12" cy="10" r="2.6"/></svg>
              </span>
              <span><b>{{ __('Address') }}</b><span>{{ $page->address() }}</span></span>
            </div>
          @endif
        </div>

        @if ($page->hours())
          <div style="margin-top:20px">
            <h2 style="font-size:15px;margin-bottom:10px">{{ __('Opening hours') }}</h2>
            <div class="ct-hours">
              @foreach ($page->hours() as $line)
                @php($parts = array_map('trim', explode('—', str_replace(['–', ' - '], '—', $line), 2)))
                <div class="ct-hour">
                  <b>{{ $parts[0] }}</b>
                  @if (count($parts) > 1)<span>{{ $parts[1] }}</span>@endif
                </div>
              @endforeach
            </div>
          </div>
        @endif

        <div class="ct-quick">
          <b>{{ __('Checking on an order?') }}</b>
          {{ __('The quickest answer is on the') }}
          <a href="{{ route('orders.track') }}">{{ __('order tracking page') }}</a> —
          {{ __('you just need the order number and the phone number you ordered with.') }}
        </div>
      </div>

      @if ($page->mapUrl())
        <div class="ct-map">
          <iframe src="{{ $page->mapUrl() }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                  title="{{ __('Where we are') }}" allowfullscreen></iframe>
        </div>
      @endif
    </div>

  </div>
</div>

@endsection
