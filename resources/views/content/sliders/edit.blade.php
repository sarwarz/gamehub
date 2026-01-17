@extends('layouts.app')
@section('title', 'Edit Slider')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}">
@endpush

@section('content')
<div class="app-ecommerce">

    @include('partials.alerts')

    <form method="POST"
          action="{{ route('sliders.update', $slider) }}"
          enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between
                    align-items-start align-items-md-center mb-4 gap-3">
            <div>
                <h4 class="mb-1">Edit Slider</h4>
                <p class="text-muted mb-0">
                    Update slider content and visibility
                </p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('sliders.index') }}"
                   class="btn btn-label-secondary">
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    Update Slider
                </button>
            </div>
        </div>

        @include('content.sliders._form', ['slider' => $slider])
    </form>
</div>
@endsection
