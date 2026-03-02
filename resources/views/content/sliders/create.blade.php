@extends('layouts.app')
@section('title', 'Create Slider')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}">
@endpush

@section('content')

    @include('partials.alerts')

    <form method="POST" action="{{ route('sliders.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-1"><i class="ti tabler-slideshow me-2"></i>Create Slider</h4>
                <p class="text-muted mb-0">Configure slider content, appearance, and schedule</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('sliders.index') }}" class="btn btn-label-secondary">
                    <i class="ti tabler-arrow-left me-1"></i> Back
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="ti tabler-device-floppy me-1"></i> Save Slider
                </button>
            </div>
        </div>

        @include('content.sliders._form')
    </form>

@endsection
