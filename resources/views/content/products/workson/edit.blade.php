@extends('layouts.app')
@section('title', 'Edit Work On Option')

@section('content')
<div class="card p-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5>Edit Work On Option</h5>
        <a href="{{ route('workson.index') }}" class="btn btn-secondary">Back</a>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('workson.update', $workOn->id) }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control"
                       value="{{ old('name', $workOn->name) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Slug</label>
                <input type="text" name="slug" class="form-control"
                       value="{{ old('slug', $workOn->slug) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Icon</label>
                <input type="text" name="icon" class="form-control"
                       value="{{ old('icon', $workOn->icon) }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="active" {{ $workOn->status == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ $workOn->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Update</button>
            <a href="{{ route('workson.index') }}" class="btn btn-label-danger">Cancel</a>
        </form>
    </div>
</div>
@endsection
