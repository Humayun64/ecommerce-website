@extends('admin.layouts.app')
@section('title', 'Edit coupon')

@section('content')
<form method="POST" action="{{ route('admin.coupons.update', $coupon) }}">
  @include('admin.coupons._form', ['method' => 'PUT', 'submit' => 'Save changes'])
</form>
@endsection
