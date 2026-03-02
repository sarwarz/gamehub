@extends('layouts.app')
@section('title', 'Edit Blog Category')

@section('content')
<form method="POST" action="{{ route('blog-categories.update', $blogCategory) }}">
    @csrf
    @method('PUT')
    @include('content.blog-categories._form', ['blogCategory' => $blogCategory])
</form>
@endsection
