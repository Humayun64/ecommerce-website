@extends('admin.layouts.app')
@section('title', 'Add category')

@section('content')
<div class="panel" style="max-width:760px">
  <div class="panel-head"><h2>New category</h2></div>
  <div class="panel-body">
    <form method="POST" action="{{ route('admin.categories.store') }}">
      @include('admin.categories._form', ['method' => 'POST', 'submit' => 'Create category'])
    </form>
  </div>
</div>
@endsection
