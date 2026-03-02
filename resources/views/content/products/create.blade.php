@extends('layouts.app')
@section('title', 'Create Product')

@section('content')

    @include('partials.alerts')

    <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-1"><i class="ti tabler-package me-2"></i>Add New Product</h4>
                <p class="text-muted mb-0">Fill in the details to create a new product listing</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('products.index') }}" class="btn btn-label-secondary">
                    <i class="ti tabler-arrow-left me-1"></i> Back
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="ti tabler-device-floppy me-1"></i> Publish Product
                </button>
            </div>
        </div>

        @include('content.products._form')
    </form>

@endsection
