@extends('layouts.app')
@section('title', 'Edit Product Type')

@section('content')
<div class="card p-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5>Edit Product Type</h5>
        <a href="{{ route('types.index') }}" class="btn btn-secondary">Back</a>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('types.update', $type->id) }}">
            @csrf
            @method('PUT')

            <!-- Type Name -->
            <div class="mb-3">
                <label class="form-label">Type Name</label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name', $type->name) }}" required>
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <!-- Slug -->
            <div class="mb-3">
                <label class="form-label">Slug</label>
                <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror"
                       value="{{ old('slug', $type->slug) }}" required>
                @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <!-- Commission Rate -->
            <div class="mb-3">
                <label class="form-label">Commission Rate (%)</label>
                <input type="number" step="0.01" name="commission" class="form-control @error('commission') is-invalid @enderror"
                       value="{{ old('commission', $type->commission) }}" placeholder="e.g. 10.35">
                @error('commission') <div class="invalid-feedback">{{ $message }}</div> @enderror
                @if($commissionMode === 'product_type')
                <small class="text-success"><i class="ti tabler-circle-check ti-xs"></i> Per-type commission is active. This rate applies to all products with this type.</small>
                @else
                <small class="text-warning"><i class="ti tabler-alert-triangle ti-xs"></i> Commission mode is Fixed — this value won't be used until you switch to Per-type mode in <a href="{{ route('settings.vendor') }}">Settings</a>.</small>
                @endif
            </div>

            <!-- Status -->
            <div class="mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select @error('status') is-invalid @enderror">
                    <option value="active" {{ old('status', $type->status) == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status', $type->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <!-- Actions -->
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('types.index') }}" class="btn btn-label-danger">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
