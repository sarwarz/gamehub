@extends('layouts.app')
@section('title', 'Create Blog Category')

@section('content')
<form method="POST" action="{{ route('blog-categories.store') }}">
    @csrf
    @include('content.blog-categories._form')
</form>
@endsection
