@extends('layouts.app')
@section('title', 'Edit Seller')

@section('content')
<div class="app-ecommerce">
    <form method="POST" action="{{ route('sellers.update', $seller->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-6 row-gap-4">
            <div class="d-flex flex-column justify-content-center">
                <h4 class="mb-1">Edit Seller</h4>
                <p class="mb-0">Update details for seller <strong>{{ $seller->store_name }}</strong></p>
            </div>
            <div class="d-flex align-content-center flex-wrap gap-4">
                <a href="{{ route('sellers.index') }}" class="btn btn-label-secondary">Back</a>
                <button type="submit" class="btn btn-primary">Update Seller</button>
            </div>
        </div>

        <div class="row">
            <!-- Left Column -->
            <div class="col-12 col-lg-8">
                <!-- Seller Information -->
                <div class="card mb-6">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Seller Information</h5>
                    </div>
                    <div class="card-body">
                        <!-- Linked User -->
                        <div class="mb-6">
                            <label class="form-label">Linked User</label>
                            <select name="user_id" class="form-select @error('user_id') is-invalid @enderror">
                                <option value="">-- Select User --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ $seller->user_id == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->email }})
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <!-- Store Name -->
                        <div class="mb-6">
                            <label class="form-label">Store Name</label>
                            <input type="text" name="store_name" class="form-control @error('store_name') is-invalid @enderror"
                                   value="{{ old('store_name', $seller->store_name) }}" required>
                            @error('store_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <!-- Slug -->
                        <div class="mb-6">
                            <label class="form-label">Slug</label>
                            <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror"
                                   value="{{ old('slug', $seller->slug) }}">
                            @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <!-- Description -->
                        <div class="mb-6">
                            <label class="mb-1">Description</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                      rows="5">{{ old('description', $seller->description) }}</textarea>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <!-- Contact Info -->
                <div class="card mb-6">
                    <div class="card-header"><h5 class="card-title mb-0">Contact Information</h5></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $seller->email) }}">
                        </div>
                        <div class="mb-3">
                            <label>Phone</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $seller->phone) }}">
                        </div>
                        <div class="mb-3">
                            <label>Website</label>
                            <input type="text" name="website" class="form-control" value="{{ old('website', $seller->website) }}">
                        </div>
                    </div>
                </div>

                <!-- Business Info -->
                <div class="card mb-6">
                    <div class="card-header"><h5 class="card-title mb-0">Business Information</h5></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label>Company Name</label>
                            <input type="text" name="company_name" class="form-control" value="{{ old('company_name', $seller->company_name) }}">
                        </div>
                        <div class="mb-3">
                            <label>VAT Number</label>
                            <input type="text" name="vat_number" class="form-control" value="{{ old('vat_number', $seller->vat_number) }}">
                        </div>
                        <div class="mb-3">
                            <label>Address</label>
                            <input type="text" name="address" class="form-control" value="{{ old('address', $seller->address) }}">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>City</label>
                                <input type="text" name="city" class="form-control" value="{{ old('city', $seller->city) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Country</label>
                                <input type="text" name="country" class="form-control" value="{{ old('country', $seller->country) }}">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label>Postal Code</label>
                            <input type="text" name="postal_code" class="form-control" value="{{ old('postal_code', $seller->postal_code) }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-12 col-lg-4">
                <!-- Status & Verification -->
                <div class="card mb-6">
                    <div class="card-header"><h5 class="card-title mb-0">Status & Verification</h5></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label>Status</label>
                            <select name="status" class="form-select">
                                <option value="pending" {{ old('status', $seller->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="active" {{ old('status', $seller->status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="suspended" {{ old('status', $seller->status) == 'suspended' ? 'selected' : '' }}>Suspended</option>
                            </select>
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="is_verified" value="1" id="is_verified"
                                {{ old('is_verified', $seller->is_verified) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_verified">Verified Seller</label>
                        </div>
                    </div>
                </div>

                <!-- Logo -->
                <div class="card mb-6">
                    <div class="card-header"><h5 class="mb-0 card-title">Seller Logo</h5></div>
                    <div class="card-body">
                        @if($seller->logo)
                            <div class="mb-2"><img src="{{ asset('storage/'.$seller->logo) }}" alt="Logo" width="120"></div>
                        @endif
                        <input type="file" name="logo" class="form-control">
                    </div>
                </div>

                <!-- Banner -->
                <div class="card mb-6">
                    <div class="card-header"><h5 class="mb-0 card-title">Seller Banner</h5></div>
                    <div class="card-body">
                        @if($seller->banner)
                            <div class="mb-2"><img src="{{ asset('storage/'.$seller->banner) }}" alt="Banner" width="120"></div>
                        @endif
                        <input type="file" name="banner" class="form-control">
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
