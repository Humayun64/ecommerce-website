@extends('admin.layouts.app')
@section('title', 'Edit brand')

@section('content')
<div class="panel" style="max-width:760px">
  <div class="panel-head"><h2>{{ $brand->name }}</h2></div>
  <div class="panel-body">
    <form method="POST" action="{{ route('admin.brands.update', $brand) }}">
      @include('admin.brands._form', ['method' => 'PUT', 'submit' => 'Save changes'])
    </form>
  </div>
</div>
@endsection
