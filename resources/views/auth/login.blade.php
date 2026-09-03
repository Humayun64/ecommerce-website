@extends('site.layouts.app')
@section('title', __('Log in') . ' — ' . ($settings['store_name'] ?? 'AMJR Global'))
@push('head')<meta name="robots" content="noindex">@endpush

@section('content')
<x-auth-shell :heading="__('Welcome back')" :sub="__('Log in to see your orders and check out faster.')">

  <form method="POST" action="{{ route('login') }}">
    @csrf

    <div class="cofield">
      <label for="email">{{ __('Email') }}</label>
      <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
    </div>

    <div class="cofield">
      <label for="password">{{ __('Password') }}</label>
      <input type="password" id="password" name="password" required autocomplete="current-password">
    </div>

    <div class="authrow">
      <label class="fcheck">
        <input type="checkbox" name="remember">
        <span>{{ __('Keep me logged in') }}</span>
      </label>

      @if (Route::has('password.request'))
        <a href="{{ route('password.request') }}" class="authlink">{{ __('Forgot password?') }}</a>
      @endif
    </div>

    <button type="submit" class="btn btn-gold" style="width:100%;height:50px">{{ __('Log in') }}</button>
  </form>

  <p class="authswap">
    {{ __("Don't have an account?") }}
    <a href="{{ route('register') }}">{{ __('Create one') }}</a>
  </p>

</x-auth-shell>
@endsection
