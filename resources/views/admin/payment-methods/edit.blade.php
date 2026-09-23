@extends('admin.layouts.app')
@section('title', 'Edit ' . $method->name)

@section('content')
<form method="POST" action="{{ route('admin.payment-methods.update', $method) }}" enctype="multipart/form-data">
  @method('PATCH')
  @include('admin.payment-methods._form')

  <div style="display:flex;gap:10px;margin-top:18px;align-items:center">
    <button class="btn btn-navy">Save changes</button>
    <a href="{{ route('admin.payment-methods.index') }}" class="btn btn-line">Cancel</a>
  </div>
</form>

<form method="POST" action="{{ route('admin.payment-methods.destroy', $method) }}"
      style="margin-top:14px"
      onsubmit="return confirm('Delete {{ $method->name }}? Switching it off is usually what you want.')">
  @csrf
  @method('DELETE')
  <button class="btn btn-line btn-sm" style="color:var(--bad)">Delete this method</button>
</form>
@endsection
