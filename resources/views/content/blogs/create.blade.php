@extends('layouts.app')
@section('title', 'Create Blog')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}">
@endpush

@section('content')
<div class="app-ecommerce">
<form method="POST" action="{{ route('blogs.store') }}" enctype="multipart/form-data">
@csrf

@include('content.blogs._form')

</form>
</div>
@endsection
