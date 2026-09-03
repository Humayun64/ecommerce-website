@extends('site.layouts.app')
@section('title', __('Verify your email'))
@push('head')<meta name="robots" content="noindex">@endpush

@section('content')
<x-auth-shell :heading="__('Check your inbox')"
              :sub="__('We sent you a verification link. Click it and you are all set.')">

  @if (session('status') === 'verification-link-sent')
    <div class="authflash">{{ __('A fresh link has been sent to your email address.') }}</div>
  @endif

  <form method="POST" action="{{ route('verification.send') }}">
    @csrf
    <button type="submit" class="btn btn-gold" style="width:100%;height:50px">{{ __('Send it again') }}</button>
  </form>

  <form method="POST" action="{{ route('logout') }}">
    @csrf
    <button type="submit" class="authswap" style="width:100%;text-align:center;margin-top:16px">{{ __('Log out') }}</button>
  </form>

</x-auth-shell>
@endsection
