@extends('admin.layouts.app')
@section('title', 'Write a post')

@section('content')
<form method="POST" action="{{ route('admin.posts.store') }}" enctype="multipart/form-data">
  @include('admin.posts._form', ['method' => 'POST', 'submit' => 'Save post'])
</form>
@endsection

@push('scripts')
<script src="{{ asset('js/seo-analyser.js') }}"></script>
<script src="{{ asset('js/post-editor.js') }}"></script>
@endpush
