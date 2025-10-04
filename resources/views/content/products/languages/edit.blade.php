@extends('layouts.app')
@section('title', 'Edit Product Language')

@section('content')
<div class="card p-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5>Edit Product Language</h5>
        <a href="{{ route('languages.index') }}" class="btn btn-secondary">Back</a>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('languages.update', $language->id) }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Language Name</label>
                <input type="text" name="name" class="form-control"
                       value="{{ old('name', $language->name) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Slug</label>
                <input type="text" name="slug" class="form-control"
                       value="{{ old('slug', $language->slug) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Code</label>
                <input type="text" name="code" class="form-control"
                       value="{{ old('code', $language->code) }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="active" {{ $language->status == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ $language->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Update</button>
            <a href="{{ route('languages.index') }}" class="btn btn-label-danger">Cancel</a>
        </form>
    </div>
</div>
@endsection
