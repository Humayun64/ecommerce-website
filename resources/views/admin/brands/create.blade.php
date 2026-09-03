@extends('admin.layouts.app')
@section('title', 'Add brand')

@section('content')
<div class="panel" style="max-width:760px">
  <div class="panel-head"><h2>New brand</h2></div>
  <div class="panel-body">
    <form method="POST" action="{{ route('admin.brands.store') }}">
      @include('admin.brands._form', ['method' => 'POST', 'submit' => 'Create brand'])
    </form>
  </div>
</div>
@endsection
