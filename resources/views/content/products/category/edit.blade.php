@extends('layouts.app')
@section('title', 'Edit Category')

@section('content')
<div class="app-ecommerce-category">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Edit Category</h5>
            <a href="{{ route('categories.index') }}" class="btn btn-secondary">Back</a>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('categories.update', $category->id) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Title -->
                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" class="form-control" name="name" value="{{ old('name', $category->name) }}" required>
                </div>

                <!-- Slug -->
                <div class="mb-3">
                    <label class="form-label">Slug</label>
                    <input type="text" class="form-control" name="slug" value="{{ old('slug', $category->slug) }}" required>
                </div>

                <!-- Status -->
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active" {{ $category->status === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ $category->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <!-- Buttons -->
                <div>
                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="{{ route('categories.index') }}" class="btn btn-label-danger">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
