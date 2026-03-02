@extends('layouts.app')
@section('title', 'Edit Blog Post')

@section('content')
<form method="POST" action="{{ route('blogs.update', $blog) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    @include('content.blogs._form', ['blog' => $blog])
</form>
@endsection
