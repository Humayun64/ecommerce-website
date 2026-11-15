@extends('admin.layouts.app')
@section('title', 'Add product')

@section('content')
<form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
  @include('admin.products._form', ['method' => 'POST', 'submit' => 'Create product'])
</form>
@endsection

@push('scripts')
<script src="{{ asset('js/product-form.js') }}?v={{ filemtime(public_path('js/product-form.js')) }}"></script>
@endpush
