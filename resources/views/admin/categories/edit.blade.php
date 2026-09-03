@extends('admin.layouts.app')
@section('title', 'Edit category')

@section('content')
<div class="panel" style="max-width:760px">
  <div class="panel-head"><h2>{{ $category->name }}</h2></div>
  <div class="panel-body">
    <form method="POST" action="{{ route('admin.categories.update', $category) }}">
      @include('admin.categories._form', ['method' => 'PUT', 'submit' => 'Save changes'])
    </form>
  </div>
</div>
@endsection
