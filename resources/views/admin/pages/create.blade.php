@extends('admin.layouts.app')
@section('title', 'Create a page')

@section('content')
<form method="POST" action="{{ route('admin.pages.store') }}">
  @include('admin.pages._form', ['method' => 'POST', 'submit' => 'Create page'])
</form>
@endsection
