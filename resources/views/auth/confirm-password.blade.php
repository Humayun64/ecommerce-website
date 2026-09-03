@extends('site.layouts.app')
@section('title', __('Confirm your password'))
@push('head')<meta name="robots" content="noindex">@endpush

@section('content')
<x-auth-shell :heading="__('Confirm your password')"
              :sub="__('This is a secure area. Please confirm your password before continuing.')">

  <form method="POST" action="{{ route('password.confirm') }}">
    @csrf
    <div class="cofield">
      <label for="password">{{ __('Password') }}</label>
      <input type="password" id="password" name="password" required autocomplete="current-password">
    </div>
    <button type="submit" class="btn btn-gold" style="width:100%;height:50px">{{ __('Confirm') }}</button>
  </form>

</x-auth-shell>
@endsection
