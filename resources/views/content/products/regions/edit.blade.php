@extends('layouts.app')
@section('title', 'Edit Product Region')

@section('content')
<div class="card p-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5>Edit Product Region</h5>
        <a href="{{ route('regions.index') }}" class="btn btn-secondary">Back</a>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('regions.update', $region->id) }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Region Name</label>
                <input type="text" name="name" class="form-control"
                       value="{{ old('name', $region->name) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Slug</label>
                <input type="text" name="slug" class="form-control"
                       value="{{ old('slug', $region->slug) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="active" {{ $region->status == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ $region->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Update</button>
            <a href="{{ route('regions.index') }}" class="btn btn-label-danger">Cancel</a>
        </form>
    </div>
</div>
@endsection
