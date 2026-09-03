@extends('admin.layouts.app')
@section('title', 'Edit attribute')

@section('content')
<div class="panel" style="max-width:760px">
  <div class="panel-head"><h2>{{ $attribute->name }}</h2></div>
  <div class="panel-body">
    <form method="POST" action="{{ route('admin.attributes.update', $attribute) }}">
      @include('admin.attributes._form', ['method' => 'PUT', 'submit' => 'Save changes'])
    </form>
  </div>
</div>
@endsection

@push('scripts')<script src="{{ asset('js/attribute-form.js') }}"></script>@endpush
