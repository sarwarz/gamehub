@extends('layouts.app')
@section('title', 'Edit Seller — ' . $seller->store_name)

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}" />
<style>
.img-upload-zone {
    border: 2px dashed #d9dee3;
    border-radius: .5rem;
    padding: 1.5rem;
    text-align: center;
    cursor: pointer;
    transition: border-color .2s, background .2s;
    position: relative;
}
.img-upload-zone:hover, .img-upload-zone.dragover {
    border-color: #7367f0;
    background: rgba(115,103,240,.04);
}
.img-upload-zone img {
    max-height: 120px;
    border-radius: .375rem;
}
.img-upload-zone input[type="file"] {
    position: absolute;
    inset: 0;
    opacity: 0;
    cursor: pointer;
}
.img-upload-zone .remove-preview {
    position: absolute;
    top: .5rem;
    right: .5rem;
}
</style>
@endpush

@section('content')
<div class="app-ecommerce">
    <form method="POST" action="{{ route('sellers.update', $seller->id) }}" enctype="multipart/form-data" id="sellerForm">
        @csrf
        @method('PUT')

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-6 gap-4">
            <div class="d-flex align-items-center gap-3">
                <img src="{{ $seller->logo ? asset($seller->logo) : asset('assets/img/avatars/1.png') }}"
                     class="rounded-circle shadow-sm" width="48" height="48" style="object-fit:cover" alt="Logo">
                <div>
                    <h4 class="mb-1">Edit Seller</h4>
                    <p class="text-muted mb-0">{{ $seller->store_name }}
                        <span class="badge bg-label-{{ match($seller->status) { 'active' => 'success', 'pending' => 'warning', 'suspended' => 'danger', default => 'secondary' } }} ms-2">{{ ucfirst($seller->status) }}</span>
                        @if($seller->is_verified)
                            <span class="badge bg-label-success ms-1"><i class="ti tabler-circle-check me-1 ti-xs"></i>Verified</span>
                        @endif
                    </p>
                </div>
            </div>
            <div class="d-flex gap-3">
                <a href="{{ route('sellers.show', $seller->id) }}" class="btn btn-label-info">
                    <i class="ti tabler-eye me-1 ti-xs"></i> View
                </a>
                <a href="{{ route('sellers.index') }}" class="btn btn-label-secondary">
                    <i class="ti tabler-arrow-left me-1 ti-xs"></i> Back
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="ti tabler-device-floppy me-1 ti-xs"></i> Update Seller
                </button>
            </div>
        </div>

        @if($errors->any())
        <div class="alert alert-danger alert-dismissible mb-6">
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            <div class="d-flex align-items-center mb-2">
                <i class="ti tabler-alert-circle me-2 ti-sm"></i>
                <strong>Please fix the following errors:</strong>
            </div>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="row">
            {{-- LEFT COLUMN --}}
            <div class="col-12 col-lg-8">

                {{-- Store Information --}}
                <div class="card mb-6">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="ti tabler-building-store me-2 ti-sm"></i>Store Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="form-label" for="user_id">Linked User</label>
                            <select name="user_id" id="user_id" class="form-select @error('user_id') is-invalid @enderror" disabled>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ $seller->user_id == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->email }})
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="form-text">User account cannot be changed after creation.</div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-7">
                                <label class="form-label" for="store_name">Store Name <span class="text-danger">*</span></label>
                                <input type="text" name="store_name" id="store_name"
                                       class="form-control @error('store_name') is-invalid @enderror"
                                       value="{{ old('store_name', $seller->store_name) }}" required>
                                @error('store_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-5">
                                <label class="form-label" for="slug">URL Slug</label>
                                <div class="input-group">
                                    <span class="input-group-text">/store/</span>
                                    <input type="text" name="slug" id="slug"
                                           class="form-control @error('slug') is-invalid @enderror"
                                           value="{{ old('slug', $seller->slug) }}">
                                </div>
                                @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mb-0">
                            <label class="form-label" for="description">Description</label>
                            <textarea name="description" id="description"
                                      class="form-control @error('description') is-invalid @enderror"
                                      rows="4">{{ old('description', $seller->description) }}</textarea>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                {{-- Contact Information --}}
                <div class="card mb-6">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="ti tabler-address-book me-2 ti-sm"></i>Contact Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label" for="email">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ti tabler-mail ti-xs"></i></span>
                                    <input type="email" name="email" id="email"
                                           class="form-control @error('email') is-invalid @enderror"
                                           value="{{ old('email', $seller->email) }}">
                                </div>
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="phone">Phone</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ti tabler-phone ti-xs"></i></span>
                                    <input type="text" name="phone" id="phone"
                                           class="form-control @error('phone') is-invalid @enderror"
                                           value="{{ old('phone', $seller->phone) }}">
                                </div>
                                @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="mb-0">
                            <label class="form-label" for="website">Website</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="ti tabler-world ti-xs"></i></span>
                                <input type="url" name="website" id="website"
                                       class="form-control @error('website') is-invalid @enderror"
                                       value="{{ old('website', $seller->website) }}">
                            </div>
                            @error('website') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                {{-- Business Information --}}
                <div class="card mb-6">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="ti tabler-briefcase me-2 ti-sm"></i>Business Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label" for="company_name">Company Name</label>
                                <input type="text" name="company_name" id="company_name"
                                       class="form-control @error('company_name') is-invalid @enderror"
                                       value="{{ old('company_name', $seller->company_name) }}">
                                @error('company_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="registration_number">Registration Number</label>
                                <input type="text" name="registration_number" id="registration_number"
                                       class="form-control @error('registration_number') is-invalid @enderror"
                                       value="{{ old('registration_number', $seller->registration_number) }}">
                                @error('registration_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label" for="vat_number">VAT Number</label>
                                <input type="text" name="vat_number" id="vat_number"
                                       class="form-control @error('vat_number') is-invalid @enderror"
                                       value="{{ old('vat_number', $seller->vat_number) }}">
                                @error('vat_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="tax_id">Tax ID</label>
                                <input type="text" name="tax_id" id="tax_id"
                                       class="form-control @error('tax_id') is-invalid @enderror"
                                       value="{{ old('tax_id', $seller->tax_id) }}">
                                @error('tax_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Address --}}
                <div class="card mb-6">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="ti tabler-map-pin me-2 ti-sm"></i>Address</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="form-label" for="address">Street Address</label>
                            <input type="text" name="address" id="address"
                                   class="form-control @error('address') is-invalid @enderror"
                                   value="{{ old('address', $seller->address) }}">
                            @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="form-label" for="city">City</label>
                                <input type="text" name="city" id="city"
                                       class="form-control @error('city') is-invalid @enderror"
                                       value="{{ old('city', $seller->city) }}">
                                @error('city') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="state">State / Province</label>
                                <input type="text" name="state" id="state"
                                       class="form-control @error('state') is-invalid @enderror"
                                       value="{{ old('state', $seller->state) }}">
                                @error('state') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="postal_code">Postal Code</label>
                                <input type="text" name="postal_code" id="postal_code"
                                       class="form-control @error('postal_code') is-invalid @enderror"
                                       value="{{ old('postal_code', $seller->postal_code) }}">
                                @error('postal_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="mb-0">
                            <label class="form-label" for="country">Country</label>
                            <input type="text" name="country" id="country"
                                   class="form-control @error('country') is-invalid @enderror"
                                   value="{{ old('country', $seller->country) }}">
                            @error('country') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

            </div>

            {{-- RIGHT COLUMN --}}
            <div class="col-12 col-lg-4">

                {{-- Status & Verification --}}
                <div class="card mb-6">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="ti tabler-settings me-2 ti-sm"></i>Status</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="form-label" for="status">Account Status <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-select">
                                <option value="pending" {{ old('status', $seller->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="active" {{ old('status', $seller->status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="suspended" {{ old('status', $seller->status) == 'suspended' ? 'selected' : '' }}>Suspended</option>
                            </select>
                        </div>
                        <div class="d-flex align-items-center justify-content-between p-3 bg-lighter rounded mb-4">
                            <div>
                                <span class="fw-semibold d-block">Verified Seller</span>
                                <small class="text-muted">Mark as a trusted seller</small>
                            </div>
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" name="is_verified" value="1" id="is_verified"
                                    {{ old('is_verified', $seller->is_verified) ? 'checked' : '' }}>
                            </div>
                        </div>
                        <div class="bg-lighter rounded p-3">
                            <div class="row text-center">
                                <div class="col-4">
                                    <h6 class="mb-0">{{ $seller->total_sales }}</h6>
                                    <small class="text-muted">Sales</small>
                                </div>
                                <div class="col-4">
                                    <h6 class="mb-0">{{ $seller->total_products }}</h6>
                                    <small class="text-muted">Products</small>
                                </div>
                                <div class="col-4">
                                    <h6 class="mb-0">{{ $seller->display_rating }}</h6>
                                    <small class="text-muted">Rating</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Seller Logo --}}
                <div class="card mb-6">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="ti tabler-photo me-2 ti-sm"></i>Seller Logo</h5>
                    </div>
                    <div class="card-body">
                        <div class="img-upload-zone" id="logo-zone">
                            <input type="file" name="logo" id="logo-input" accept="image/*">
                            <div class="preview-area {{ $seller->logo ? '' : 'd-none' }}" id="logo-preview-area">
                                <img id="logo-preview" src="{{ $seller->logo ? asset($seller->logo) : '' }}" alt="Logo preview" class="mb-2">
                                <button type="button" class="btn btn-sm btn-icon btn-label-danger remove-preview"
                                        onclick="clearImagePreview('logo')">
                                    <i class="ti tabler-x ti-xs"></i>
                                </button>
                            </div>
                            <div class="placeholder-area {{ $seller->logo ? 'd-none' : '' }}" id="logo-placeholder">
                                <i class="ti tabler-cloud-upload d-block mb-2" style="font-size:2rem;color:#a1acb8"></i>
                                <span class="text-muted d-block">Drop image here or click to upload</span>
                                <small class="text-muted">PNG, JPG up to 2MB. Recommended: 200x200px</small>
                            </div>
                        </div>
                        @error('logo') <small class="text-danger mt-1 d-block">{{ $message }}</small> @enderror
                    </div>
                </div>

                {{-- Seller Banner --}}
                <div class="card mb-6">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="ti tabler-panorama-horizontal me-2 ti-sm"></i>Seller Banner</h5>
                    </div>
                    <div class="card-body">
                        <div class="img-upload-zone" id="banner-zone">
                            <input type="file" name="banner" id="banner-input" accept="image/*">
                            <div class="preview-area {{ $seller->banner ? '' : 'd-none' }}" id="banner-preview-area">
                                <img id="banner-preview" src="{{ $seller->banner ? asset($seller->banner) : '' }}" alt="Banner preview" class="mb-2" style="max-width:100%">
                                <button type="button" class="btn btn-sm btn-icon btn-label-danger remove-preview"
                                        onclick="clearImagePreview('banner')">
                                    <i class="ti tabler-x ti-xs"></i>
                                </button>
                            </div>
                            <div class="placeholder-area {{ $seller->banner ? 'd-none' : '' }}" id="banner-placeholder">
                                <i class="ti tabler-cloud-upload d-block mb-2" style="font-size:2rem;color:#a1acb8"></i>
                                <span class="text-muted d-block">Drop image here or click to upload</span>
                                <small class="text-muted">PNG, JPG up to 4MB. Recommended: 1200x300px</small>
                            </div>
                        </div>
                        @error('banner') <small class="text-danger mt-1 d-block">{{ $message }}</small> @enderror
                    </div>
                </div>

                {{-- Danger Zone --}}
                <div class="card border-danger">
                    <div class="card-header">
                        <h5 class="card-title mb-0 text-danger"><i class="ti tabler-alert-triangle me-2 ti-sm"></i>Danger Zone</h5>
                    </div>
                    <div class="card-body">
                        <p class="small text-muted mb-3">Permanently delete this seller and all associated data. This action cannot be undone.</p>
                        <button type="button" class="btn btn-label-danger w-100" id="btn-delete-seller">
                            <i class="ti tabler-trash me-1 ti-xs"></i> Delete Seller
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>
@endsection

@push('page-js')
<script>
(function() {
'use strict';

['logo', 'banner'].forEach(name => {
    const input = document.getElementById(name + '-input');
    const zone = document.getElementById(name + '-zone');

    input.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = e => {
                document.getElementById(name + '-preview').src = e.target.result;
                document.getElementById(name + '-preview-area').classList.remove('d-none');
                document.getElementById(name + '-placeholder').classList.add('d-none');
            };
            reader.readAsDataURL(this.files[0]);
        }
    });

    ['dragenter', 'dragover'].forEach(evt => {
        zone.addEventListener(evt, e => { e.preventDefault(); zone.classList.add('dragover'); });
    });
    ['dragleave', 'drop'].forEach(evt => {
        zone.addEventListener(evt, e => { e.preventDefault(); zone.classList.remove('dragover'); });
    });
});

window.clearImagePreview = function(name) {
    document.getElementById(name + '-input').value = '';
    document.getElementById(name + '-preview-area').classList.add('d-none');
    document.getElementById(name + '-placeholder').classList.remove('d-none');
};

document.getElementById('btn-delete-seller').addEventListener('click', function() {
    Swal.fire({
        title: 'Delete this seller?',
        text: 'This will permanently delete the seller and all associated data.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete',
        confirmButtonColor: '#d33'
    }).then(res => {
        if (!res.isConfirmed) return;
        $.ajax({
            url: '{{ route("sellers.destroy", $seller->id) }}',
            method: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: () => window.location.href = '{{ route("sellers.index") }}',
            error: () => Swal.fire('Error', 'Failed to delete seller.', 'error')
        });
    });
});

})();
</script>
@endpush
