@extends('layouts.app')
@section('title', 'Edit Product Label')

@section('content')
<div class="app-ecommerce-category">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Edit Product Label</h5>
            <a href="{{ route('labels.index') }}" class="btn btn-secondary">
                Back
            </a>
        </div>

        <div class="card-body">
            <form method="POST"
                  action="{{ route('labels.update', $label->id) }}">
                @csrf
                @method('PUT')

                <!-- Label Name -->
                <div class="mb-3">
                    <label class="form-label">Label Name</label>
                    <input type="text"
                           class="form-control @error('name') is-invalid @enderror"
                           name="name"
                           value="{{ old('name', $label->name) }}"
                           placeholder="e.g. Hot, Sale, New"
                           required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Background Color -->
                <div class="mb-3">
                    <label class="form-label">Background Color</label>
                    <input type="color"
                           class="form-control form-control-color @error('bg_color') is-invalid @enderror"
                           name="bg_color"
                           value="{{ old('bg_color', $label->bg_color) }}">
                    @error('bg_color')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Text Color -->
                <div class="mb-3">
                    <label class="form-label">Text Color</label>
                    <input type="color"
                           class="form-control form-control-color @error('text_color') is-invalid @enderror"
                           name="text_color"
                           value="{{ old('text_color', $label->text_color) }}">
                    @error('text_color')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Live Preview -->
                <div class="mb-3">
                    <label class="form-label d-block">Preview</label>
                    <span class="badge px-3 py-2"
                          style="background-color: {{ $label->bg_color }};
                                 color: {{ $label->text_color }};">
                        {{ $label->name }}
                    </span>
                </div>

                <!-- Status -->
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status"
                            class="form-select @error('status') is-invalid @enderror">
                        <option value="active"
                            {{ old('status', $label->status) === 'active' ? 'selected' : '' }}>
                            Active
                        </option>
                        <option value="inactive"
                            {{ old('status', $label->status) === 'inactive' ? 'selected' : '' }}>
                            Inactive
                        </option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Buttons -->
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        Update
                    </button>
                    <a href="{{ route('labels.index') }}"
                       class="btn btn-label-danger">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
