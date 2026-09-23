@extends('admin.layouts.app')
@section('title', 'New payment method')

@section('content')
<form method="POST" action="{{ route('admin.payment-methods.store') }}" enctype="multipart/form-data">
  @include('admin.payment-methods._form')

  <div style="display:flex;gap:10px;margin-top:18px">
    <button class="btn btn-navy">Add method</button>
    <a href="{{ route('admin.payment-methods.index') }}" class="btn btn-line">Cancel</a>
  </div>
</form>
@endsection
