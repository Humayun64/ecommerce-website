@extends('site.layouts.app')
@section('title', __('Create an account') . ' — ' . ($settings['store_name'] ?? 'AMJR Global'))
@push('head')<meta name="robots" content="noindex">@endpush

@section('content')
<x-auth-shell :heading="__('Create your account')" :sub="__('Takes about thirty seconds.')">

  <form method="POST" action="{{ route('register') }}">
    @csrf

    <div class="cofield">
      <label for="name">{{ __('Full name') }}</label>
      <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus autocomplete="name">
    </div>

    <div class="cofield">
      <label for="phone">{{ __('Mobile number') }}</label>
      <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" required
             placeholder="01XXXXXXXXX" autocomplete="tel">
      <small>{{ __('We use this to link any orders you placed as a guest, and to call before delivery.') }}</small>
    </div>

    <div class="cofield">
      <label for="email">{{ __('Email') }}</label>
      <input type="email" id="email" name="email" value="{{ old('email') }}" required autocomplete="username">
      <small>{{ __('Order confirmations go here.') }}</small>
    </div>

    <div class="corow">
      <div class="cofield">
        <label for="password">{{ __('Password') }}</label>
        <input type="password" id="password" name="password" required autocomplete="new-password">
      </div>
      <div class="cofield">
        <label for="password_confirmation">{{ __('Repeat password') }}</label>
        <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password">
      </div>
    </div>

    <button type="submit" class="btn btn-gold" style="width:100%;height:50px">{{ __('Create account') }}</button>
  </form>

  <p class="authswap">
    {{ __('Already have an account?') }}
    <a href="{{ route('login') }}">{{ __('Log in') }}</a>
  </p>

</x-auth-shell>
@endsection
