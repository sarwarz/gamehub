@extends('layouts.app')
@section('title', 'Edit Tax Rule')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="ti tabler-receipt-tax ti-md me-1 text-primary"></i> Edit Tax Rule</h4>
        <p class="text-muted mb-0">Update tax configuration for <strong>{{ $tax->name }}</strong></p>
    </div>
    <a href="{{ route('taxes.index') }}" class="btn btn-label-secondary">
        <i class="ti tabler-arrow-left ti-xs me-1"></i> Back
    </a>
</div>

@if ($errors->any())
<div class="alert alert-danger alert-dismissible mb-4">
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    <div class="d-flex align-items-center">
        <i class="ti tabler-alert-circle ti-md me-2"></i>
        <div>
            <strong>Please fix the following errors:</strong>
            <ul class="mb-0 mt-1 ps-3">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    </div>
</div>
@endif

<form method="POST" action="{{ route('taxes.update', $tax->id) }}">
    @csrf @method('PUT')
    <div class="row">
        <div class="col-lg-8">
            {{-- Basic Info --}}
            <div class="card mb-4">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0"><i class="ti tabler-info-circle ti-md me-2 text-primary"></i> Tax Information</h5>
                </div>
                <div class="card-body pt-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Tax Name <span class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="ti tabler-tag ti-xs"></i></span>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $tax->name) }}" required>
                            </div>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tax Code</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="ti tabler-hash ti-xs"></i></span>
                                <input type="text" name="code" class="form-control" value="{{ old('code', $tax->code) }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Seller (Optional)</label>
                            <select name="seller_id" class="form-select">
                                <option value="">Global (All Sellers)</option>
                                @foreach($sellers as $seller)
                                    <option value="{{ $seller->id }}" {{ old('seller_id', $tax->seller_id) == $seller->id ? 'selected' : '' }}>{{ $seller->store_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Location --}}
            <div class="card mb-4">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0"><i class="ti tabler-map-pin ti-md me-2 text-primary"></i> Location</h5>
                </div>
                <div class="card-body pt-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Country</label>
                            <input type="text" name="country" class="form-control" value="{{ old('country', $tax->country) }}" placeholder="e.g. US">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">State</label>
                            <input type="text" name="state" class="form-control" value="{{ old('state', $tax->state) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control" value="{{ old('city', $tax->city) }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            {{-- Rate & Type --}}
            <div class="card mb-4">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0"><i class="ti tabler-percentage ti-md me-2 text-primary"></i> Rate & Type</h5>
                </div>
                <div class="card-body pt-4">
                    <div class="mb-3">
                        <label class="form-label">Tax Type <span class="text-danger">*</span></label>
                        <select name="type" class="form-select" required>
                            <option value="percent" {{ old('type', $tax->type) === 'percent' ? 'selected' : '' }}>Percentage (%)</option>
                            <option value="fixed" {{ old('type', $tax->type) === 'fixed' ? 'selected' : '' }}>Fixed Amount ($)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rate <span class="text-danger">*</span></label>
                        <input type="number" step="0.0001" name="rate" class="form-control @error('rate') is-invalid @enderror" value="{{ old('rate', $tax->rate) }}" required>
                        @error('rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Priority</label>
                        <input type="number" name="priority" class="form-control" value="{{ old('priority', $tax->priority) }}" min="1">
                        <small class="text-muted">Lower number applies first</small>
                    </div>
                    <hr>
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="is_compound" value="1" {{ old('is_compound', $tax->is_compound) ? 'checked' : '' }}>
                        <label class="form-check-label">Compound Tax</label>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ old('is_active', $tax->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold">Active</label>
                    </div>
                </div>
                <div class="card-footer bg-transparent pt-0">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="ti tabler-check ti-xs me-1"></i> Update Tax Rule
                    </button>
                </div>
            </div>

            {{-- Danger Zone --}}
            <div class="card border-danger">
                <div class="card-body text-center py-4">
                    <p class="text-muted small mb-3">Permanently remove this tax rule</p>
                    <button type="button" class="btn btn-outline-danger btn-sm btn-delete-tax" data-url="{{ route('taxes.destroy', $tax->id) }}">
                        <i class="ti tabler-trash ti-xs me-1"></i> Delete Tax Rule
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('page-js')
<script>
$(document).on('click', '.btn-delete-tax', function() {
    const url = $(this).data('url');
    Swal.fire({
        title: 'Delete Tax Rule?', text: 'This action cannot be undone.', icon: 'warning',
        showCancelButton: true, confirmButtonText: 'Yes, delete',
        customClass: { confirmButton: 'btn btn-danger me-3', cancelButton: 'btn btn-label-secondary' }, buttonsStyling: false
    }).then(r => {
        if (!r.isConfirmed) return;
        $.ajax({ url, type: 'DELETE', data: { _token: '{{ csrf_token() }}' },
            success: () => { Swal.fire({ icon: 'success', title: 'Deleted', showConfirmButton: false, timer: 1500, timerProgressBar: true }).then(() => window.location.href = '{{ route("taxes.index") }}'); },
            error: () => Swal.fire({ icon: 'error', title: 'Failed', timer: 1500, showConfirmButton: false })
        });
    });
});
</script>
@endpush
