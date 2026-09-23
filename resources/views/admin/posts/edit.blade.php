@extends('admin.layouts.app')
@section('title', 'Edit post')

@section('content')
<form method="POST" action="{{ route('admin.posts.update', $post) }}" enctype="multipart/form-data">
  @include('admin.posts._form', ['method' => 'PUT', 'submit' => 'Save changes'])
</form>
@endsection

@push('scripts')
<script src="{{ asset('js/seo-analyser.js') }}"></script>
<script src="{{ asset('js/post-editor.js') }}"></script>
@endpush
