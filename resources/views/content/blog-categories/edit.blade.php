@extends('layouts.app')
@section('title', 'Edit Blog Category')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}">
@endpush

@section('content')
<div class="app-ecommerce">

    @include('partials.alerts')

    <form method="POST"
          action="{{ route('blog-categories.update', $blogCategory) }}">
        @csrf
        @method('PUT')

        <div class="d-flex flex-column flex-md-row justify-content-between
                    align-items-start align-items-md-center mb-4 gap-3">
            <div>
                <h4>Edit Blog Category</h4>
                <p class="text-muted mb-0">Update category details</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('blog-categories.index') }}"
                   class="btn btn-label-secondary">Cancel</a>
                <button class="btn btn-primary">Update</button>
            </div>
        </div>

        @include('content.blog-categories._form', ['blogCategory' => $blogCategory])
    </form>
</div>
@endsection
