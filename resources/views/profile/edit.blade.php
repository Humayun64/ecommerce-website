@extends('site.layouts.app')
@section('title', __('Profile') . ' — ' . ($settings['store_name'] ?? 'AMJR Global'))
@push('head')<meta name="robots" content="noindex">@endpush

@section('content')
<div class="wrap acctpage">
  @include('site.account._nav')

  <div class="acctmain">
    <div class="accthead"><h1>{{ __('Profile and password') }}</h1></div>

    <div class="cobox" style="margin-bottom:18px">
      <h2>{{ __('Your details') }}</h2>

      <form method="POST" action="{{ route('profile.update') }}">
        @csrf @method('PATCH')

        <div class="corow">
          <div class="cofield">
            <label for="name">{{ __('Full name') }}</label>
            <input type="text" id="name" name="name" value="{{ old('name', auth()->user()->name) }}" required>
            @error('name')<small style="color:#B3261E">{{ $message }}</small>@enderror
          </div>
          <div class="cofield">
            <label for="phone">{{ __('Mobile number') }}</label>
            <input type="tel" id="phone" name="phone" value="{{ old('phone', auth()->user()->phone) }}" placeholder="01XXXXXXXXX">
            <small>{{ __('Used to match guest orders and to call before delivery.') }}</small>
            @error('phone')<small style="color:#B3261E">{{ $message }}</small>@enderror
          </div>
        </div>

        <div class="cofield">
          <label for="email">{{ __('Email') }}</label>
          <input type="email" id="email" name="email" value="{{ old('email', auth()->user()->email) }}" required>
          @error('email')<small style="color:#B3261E">{{ $message }}</small>@enderror
        </div>

        <button type="submit" class="btn btn-gold">{{ __('Save changes') }}</button>

        @if (session('status') === 'profile-updated')
          <span class="savedok">{{ __('Saved.') }}</span>
        @endif
      </form>
    </div>

    <div class="cobox" style="margin-bottom:18px">
      <h2>{{ __('Change password') }}</h2>

      <form method="POST" action="{{ route('password.update') }}">
        @csrf @method('PUT')

        <div class="cofield">
          <label for="current_password">{{ __('Current password') }}</label>
          <input type="password" id="current_password" name="current_password" autocomplete="current-password">
          @error('current_password', 'updatePassword')<small style="color:#B3261E">{{ $message }}</small>@enderror
        </div>

        <div class="corow">
          <div class="cofield">
            <label for="new_password">{{ __('New password') }}</label>
            <input type="password" id="new_password" name="password" autocomplete="new-password">
            @error('password', 'updatePassword')<small style="color:#B3261E">{{ $message }}</small>@enderror
          </div>
          <div class="cofield">
            <label for="new_password_confirmation">{{ __('Repeat new password') }}</label>
            <input type="password" id="new_password_confirmation" name="password_confirmation" autocomplete="new-password">
          </div>
        </div>

        <button type="submit" class="btn btn-navy">{{ __('Update password') }}</button>

        @if (session('status') === 'password-updated')
          <span class="savedok">{{ __('Password changed.') }}</span>
        @endif
      </form>
    </div>

    <div class="cobox dangerbox">
      <h2>{{ __('Delete account') }}</h2>
      <p class="sub" style="margin:0 0 16px">
        {{ __('This cannot be undone. Your past orders stay in our records for accounting, but you will lose access to them.') }}
      </p>

      <form method="POST" action="{{ route('profile.destroy') }}"
            onsubmit="return confirm('{{ __('Delete your account permanently?') }}')">
        @csrf @method('DELETE')
        <div class="cofield" style="max-width:320px">
          <label for="delete_password">{{ __('Confirm with your password') }}</label>
          <input type="password" id="delete_password" name="password" required>
          @error('password', 'userDeletion')<small style="color:#B3261E">{{ $message }}</small>@enderror
        </div>
        <button type="submit" class="btn btn-danger-solid">{{ __('Delete my account') }}</button>
      </form>
    </div>
  </div>
</div>
@endsection
