@extends('site.layouts.app')
@section('title', __('Choose a new password') . ' — ' . ($settings['store_name'] ?? 'AMJR Global'))
@push('head')<meta name="robots" content="noindex">@endpush

@section('content')
<x-auth-shell :heading="__('Choose a new password')">

  <form method="POST" action="{{ route('password.store') }}">
    @csrf
    <input type="hidden" name="token" value="{{ $request->route('token') }}">

    <div class="cofield">
      <label for="email">{{ __('Email') }}</label>
      <input type="email" id="email" name="email" value="{{ old('email', $request->email) }}" required>
    </div>
    <div class="cofield">
      <label for="password">{{ __('New password') }}</label>
      <input type="password" id="password" name="password" required autocomplete="new-password">
    </div>
    <div class="cofield">
      <label for="password_confirmation">{{ __('Repeat new password') }}</label>
      <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password">
    </div>

    <button type="submit" class="btn btn-gold" style="width:100%;height:50px">{{ __('Save new password') }}</button>
  </form>

</x-auth-shell>
@endsection
