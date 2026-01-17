@extends('layouts.app')
@section('title', 'Edit Blog')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}">
@endpush

@section('content')
<div class="app-ecommerce">
<form method="POST" action="{{ route('blogs.update',$blog) }}" enctype="multipart/form-data">
@csrf
@method('PUT')

@include('content.blogs._form',['blog'=>$blog])

</form>
</div>
@endsection
