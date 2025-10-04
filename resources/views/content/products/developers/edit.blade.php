@extends('layouts.app')
@section('title', 'Edit Developer')

@section('content')
<div class="card p-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5>Edit Developer</h5>
        <a href="{{ route('developers.index') }}" class="btn btn-secondary">Back</a>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('developers.update', $developer->id) }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Developer Name</label>
                <input type="text" name="name" class="form-control"
                       value="{{ old('name', $developer->name) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Slug</label>
                <input type="text" name="slug" class="form-control"
                       value="{{ old('slug', $developer->slug) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Website</label>
                <input type="url" name="website" class="form-control"
                       value="{{ old('website', $developer->website) }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description', $developer->description) }}</textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="active" {{ $developer->status == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ $developer->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Update</button>
            <a href="{{ route('developers.index') }}" class="btn btn-label-danger">Cancel</a>
        </form>
    </div>
</div>
@endsection
