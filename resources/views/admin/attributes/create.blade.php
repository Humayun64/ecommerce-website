@extends('admin.layouts.app')
@section('title', 'Add attribute')

@section('content')
<div class="panel" style="max-width:760px">
  <div class="panel-head"><h2>New attribute</h2></div>
  <div class="panel-body">
    <form method="POST" action="{{ route('admin.attributes.store') }}">
      @include('admin.attributes._form', ['method' => 'POST', 'submit' => 'Create attribute'])
    </form>
  </div>
</div>
@endsection

@push('scripts')<script src="{{ asset('js/attribute-form.js') }}"></script>@endpush
