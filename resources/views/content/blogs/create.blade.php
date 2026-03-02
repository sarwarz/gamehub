@extends('layouts.app')
@section('title', 'Create Blog Post')

@section('content')
<form method="POST" action="{{ route('blogs.store') }}" enctype="multipart/form-data">
    @csrf
    @include('content.blogs._form')
</form>
@endsection
