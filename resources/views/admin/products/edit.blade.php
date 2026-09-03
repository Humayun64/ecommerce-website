@extends('admin.layouts.app')
@section('title', 'Edit product')

@section('content')
<form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
  @include('admin.products._form', ['method' => 'PUT', 'submit' => 'Save changes'])
</form>

{{-- Kept outside the main form: nested forms are invalid HTML. --}}
@foreach ($product->images as $image)
  <form id="del-{{ $image->id }}" method="POST" action="{{ route('admin.products.images.destroy', [$product, $image]) }}">
    @csrf @method('DELETE')
  </form>
  <form id="primary-{{ $image->id }}" method="POST" action="{{ route('admin.products.images.primary', [$product, $image]) }}">
    @csrf @method('PATCH')
  </form>
@endforeach

<form id="deleteProduct" method="POST" action="{{ route('admin.products.destroy', $product) }}">
  @csrf @method('DELETE')
</form>

<div style="margin-top:20px">
  <button type="button" class="btn btn-danger"
          onclick="if(confirm('Delete {{ $product->name }}? It will be hidden from the storefront.')) document.getElementById('deleteProduct').submit()">
    Delete this product
  </button>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/product-form.js') }}"></script>
@endpush
