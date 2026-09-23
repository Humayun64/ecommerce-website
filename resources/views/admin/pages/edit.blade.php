@extends('admin.layouts.app')
@section('title', 'Edit page')

@section('content')
<form method="POST" action="{{ route('admin.pages.update', $page) }}">
  @include('admin.pages._form', ['method' => 'PUT', 'submit' => 'Save changes'])
</form>
@endsection
