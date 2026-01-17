@extends('layouts.app')
@section('title', 'Edit Tax')

@section('content')
<form method="POST" action="{{ route('taxes.update', $tax->id) }}">
    @csrf
    @method('PUT')

    <div class="card p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="mb-0">Edit Tax Rule</h5>
                <small class="text-muted">Update tax configuration</small>
            </div>
            <a href="{{ route('taxes.index') }}" class="btn btn-label-secondary">Back</a>
        </div>

        <!-- Tax Name & Seller -->
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Tax Name</label>
                <input type="text"
                       name="name"
                       value="{{ old('name', $tax->name) }}"
                       class="form-control @error('name') is-invalid @enderror"
                       required>
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Seller (Optional)</label>
                <select name="seller_id"
                        class="form-select @error('seller_id') is-invalid @enderror">
                    <option value="">Global</option>
                    @foreach($sellers as $seller)
                        <option value="{{ $seller->id }}"
                            {{ old('seller_id', $tax->seller_id) == $seller->id ? 'selected' : '' }}>
                            {{ $seller->store_name }}
                        </option>
                    @endforeach
                </select>
                @error('seller_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <!-- Location -->
        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Country</label>
                <input type="text"
                       name="country"
                       value="{{ old('country', $tax->country) }}"
                       class="form-control"
                       placeholder="US">
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label">State</label>
                <input type="text"
                       name="state"
                       value="{{ old('state', $tax->state) }}"
                       class="form-control">
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label">City</label>
                <input type="text"
                       name="city"
                       value="{{ old('city', $tax->city) }}"
                       class="form-control">
            </div>
        </div>

        <!-- Type & Rate -->
        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Tax Type</label>
                <select name="type"
                        class="form-select @error('type') is-invalid @enderror"
                        required>
                    <option value="percent"
                        {{ old('type', $tax->type) === 'percent' ? 'selected' : '' }}>
                        Percentage (%)
                    </option>
                    <option value="fixed"
                        {{ old('type', $tax->type) === 'fixed' ? 'selected' : '' }}>
                        Fixed Amount
                    </option>
                </select>
                @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label">Rate</label>
                <input type="number"
                       step="0.0001"
                       name="rate"
                       value="{{ old('rate', $tax->rate) }}"
                       class="form-control @error('rate') is-invalid @enderror"
                       required>
                @error('rate') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label">Priority</label>
                <input type="number"
                       name="priority"
                       value="{{ old('priority', $tax->priority) }}"
                       class="form-control">
                <small class="text-muted">
                    Lower number applies first
                </small>
            </div>
        </div>

        <!-- Flags -->
        <div class="row mt-2">
            <div class="col-md-4">
                <div class="form-check form-switch">
                    <input class="form-check-input"
                           type="checkbox"
                           name="is_compound"
                           value="1"
                           {{ old('is_compound', $tax->is_compound) ? 'checked' : '' }}>
                    <label class="form-check-label">Compound Tax</label>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-check form-switch">
                    <input class="form-check-input"
                           type="checkbox"
                           name="is_active"
                           value="1"
                           {{ old('is_active', $tax->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label">Active</label>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="mt-4 d-flex justify-content-end gap-2">
            <a href="{{ route('taxes.index') }}" class="btn btn-label-secondary">
                Cancel
            </a>
            <button type="submit" class="btn btn-primary">
                Update Tax
            </button>
        </div>
    </div>
</form>
@endsection
