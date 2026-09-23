@extends('admin.layouts.app')
@section('title', 'Add coupon')

@section('content')
<form method="POST" action="{{ route('admin.coupons.store') }}">
  @include('admin.coupons._form', ['method' => 'POST', 'submit' => 'Create coupon'])
</form>
@endsection
