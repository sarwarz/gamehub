@extends('layouts.app')
@section('title', 'Create Page')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}">
@endpush

@section('content')
<div class="app-ecommerce">

    @include('partials.alerts')

    <form method="POST" action="{{ route('pages.store') }}" enctype="multipart/form-data" id="page-form">
        @csrf

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
            <div>
                <h4 class="mb-1">
                    <a href="{{ route('pages.index') }}" class="text-muted me-1"><i class="icon-base ti tabler-arrow-left icon-md"></i></a>
                    Create Page
                </h4>
                <p class="text-muted mb-0">Add a new CMS page to your store</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('pages.index') }}" class="btn btn-label-secondary">
                    <i class="icon-base ti tabler-x me-1"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="icon-base ti tabler-device-floppy me-1"></i> Save Page
                </button>
            </div>
        </div>

        @include('content.pages._form')
    </form>
</div>
@endsection

@include('content.pages._scripts')
