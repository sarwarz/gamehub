@extends('layouts.app')
@section('title', 'Edit Platform')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}" />
@endpush

@section('content')
<div class="app-ecommerce-platform">

    @include('partials.alerts')

    <div class="card p-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Edit Platform</h5>
            <a href="{{ route('platforms.index') }}" class="btn btn-secondary">
                <i class="menu-icon icon-base ti tabler-arrow-left"></i> Back
            </a>
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('platforms.update', $platform->id) }}">
                @csrf
                @method('PUT')

                <!-- Name -->
                <div class="mb-3">
                    <label class="form-label">Platform Name</label>
                    <input type="text"
                           class="form-control @error('name') is-invalid @enderror"
                           name="name"
                           value="{{ old('name', $platform->name) }}"
                           placeholder="Enter platform name" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Slug -->
                <div class="mb-3">
                    <label class="form-label">Slug</label>
                    <input type="text"
                           class="form-control @error('slug') is-invalid @enderror"
                           name="slug"
                           value="{{ old('slug', $platform->slug) }}"
                           placeholder="Enter slug" required>
                    @error('slug')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Status -->
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror">
                        <option value="active" {{ old('status', $platform->status) == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $platform->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Buttons -->
                <div>
                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="{{ route('platforms.index') }}" class="btn btn-label-danger">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
