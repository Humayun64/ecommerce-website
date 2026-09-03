@extends('site.layouts.app')
@section('title', __('Reset your password') . ' — ' . ($settings['store_name'] ?? 'AMJR Global'))
@push('head')<meta name="robots" content="noindex">@endpush

@section('content')
<x-auth-shell :heading="__('Reset your password')"
              :sub="__('Give us the email on your account and we will send a reset link.')">

  <form method="POST" action="{{ route('password.email') }}">
    @csrf
    <div class="cofield">
      <label for="email">{{ __('Email') }}</label>
      <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>
    </div>
    <button type="submit" class="btn btn-gold" style="width:100%;height:50px">{{ __('Send reset link') }}</button>
  </form>

  <p class="authswap"><a href="{{ route('login') }}">{{ __('Back to log in') }}</a></p>

</x-auth-shell>
@endsection
