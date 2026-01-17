@extends('layouts.app')
@section('title', 'Create Blog Category')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}">
@endpush

@section('content')
<div class="app-ecommerce">

    @include('partials.alerts')

    <form method="POST" action="{{ route('blog-categories.store') }}">
        @csrf

        <div class="d-flex flex-column flex-md-row justify-content-between
                    align-items-start align-items-md-center mb-4 gap-3">
            <div>
                <h4>Create Blog Category</h4>
                <p class="text-muted mb-0">Organize blog posts</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('blog-categories.index') }}"
                   class="btn btn-label-secondary">Cancel</a>
                <button class="btn btn-primary">Save</button>
            </div>
        </div>

        @include('content.blog-categories._form')
    </form>
</div>
@endsection
