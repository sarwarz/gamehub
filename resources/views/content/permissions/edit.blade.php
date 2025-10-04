@extends('layouts.app')
@section('title', 'Edit Permission')

@section('content')
<div class="card p-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Edit Permission</h5>
        <a href="{{ route('permissions.index') }}" class="btn btn-label-secondary">
            <i class="ti ti-arrow-left"></i> Back
        </a>
    </div>

    <div class="card-body border-top mt-3">
        <form method="POST" action="{{ route('permissions.update', $permission->id) }}">
            @csrf
            @method('PUT')

            <!-- Name -->
            <div class="mb-3">
                <label class="form-label">Permission Name</label>
                <input type="text" 
                       name="name"
                       class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name', $permission->name) }}"
                       required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Label -->
            <div class="mb-3">
                <label class="form-label">Label (Optional)</label>
                <input type="text"
                       name="label"
                       class="form-control @error('label') is-invalid @enderror"
                       value="{{ old('label', $permission->label) }}">
                @error('label')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Buttons -->
            <div>
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('permissions.index') }}" class="btn btn-label-danger">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
