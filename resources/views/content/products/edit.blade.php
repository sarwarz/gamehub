@extends('layouts.app')
@section('title', 'Edit Product')

@section('content')

    @include('partials.alerts')

    <form method="POST" action="{{ route('products.update', $product->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-1"><i class="ti tabler-pencil me-2"></i>Edit Product</h4>
                <p class="text-muted mb-0">Update <strong>{{ $product->title }}</strong></p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('products.index') }}" class="btn btn-label-secondary">
                    <i class="ti tabler-arrow-left me-1"></i> Back
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="ti tabler-device-floppy me-1"></i> Update Product
                </button>
            </div>
        </div>

        @include('content.products._form')
    </form>

@endsection
