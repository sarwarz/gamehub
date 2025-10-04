@extends('layouts.app')
@section('title', 'Create Seller')

@section('content')
<div class="app-ecommerce">
    <form method="POST" action="{{ route('sellers.store') }}" enctype="multipart/form-data">
        @csrf

        <!-- Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-6 row-gap-4">
            <div class="d-flex flex-column justify-content-center">
                <h4 class="mb-1">Add a new Seller</h4>
                <p class="mb-0">Fill in the details to create a seller profile</p>
            </div>
            <div class="d-flex align-content-center flex-wrap gap-4">
                <a href="{{ route('sellers.index') }}" class="btn btn-label-secondary">Discard</a>
                <button type="submit" class="btn btn-primary">Save Seller</button>
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
                        <!-- User ID -->
                        <div class="mb-6">
                            <label class="form-label">Linked User</label>
                            <select name="user_id" class="form-select @error('user_id') is-invalid @enderror">
                                <option value="">-- Select User --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
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
                                   value="{{ old('store_name') }}" placeholder="Store name" required>
                            @error('store_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <!-- Slug -->
                        <div class="mb-6">
                            <label class="form-label">Slug</label>
                            <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror"
                                   value="{{ old('slug') }}" placeholder="store-slug">
                            @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <!-- Description -->
                        <div class="mb-6">
                            <label class="mb-1">Description</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                      rows="5" placeholder="Enter seller description">{{ old('description') }}</textarea>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <!-- Contact Info -->
                <div class="card mb-6">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Contact Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}" placeholder="seller@example.com">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                   value="{{ old('phone') }}" placeholder="+8801XXXXXXXXX">
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Website</label>
                            <input type="text" name="website" class="form-control @error('website') is-invalid @enderror"
                                   value="{{ old('website') }}" placeholder="https://example.com">
                            @error('website') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <!-- Business Info -->
                <div class="card mb-6">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Business Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Company Name</label>
                            <input type="text" name="company_name" class="form-control" value="{{ old('company_name') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">VAT Number</label>
                            <input type="text" name="vat_number" class="form-control" value="{{ old('vat_number') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <input type="text" name="address" class="form-control" value="{{ old('address') }}">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">City</label>
                                <input type="text" name="city" class="form-control" value="{{ old('city') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Country</label>
                                <input type="text" name="country" class="form-control" value="{{ old('country') }}">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Postal Code</label>
                            <input type="text" name="postal_code" class="form-control" value="{{ old('postal_code') }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-12 col-lg-4">
                <!-- Status & Verified -->
                <div class="card mb-6">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Status & Verification</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="suspended" {{ old('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                            </select>
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="is_verified" value="1" id="is_verified"
                                {{ old('is_verified') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_verified">Verified Seller</label>
                        </div>
                    </div>
                </div>

                <!-- Logo -->
                <div class="card mb-6">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 card-title">Seller Logo</h5>
                    </div>
                    <div class="card-body">
                        <input type="file" name="logo" class="form-control @error('logo') is-invalid @enderror" />
                        @error('logo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <!-- Banner -->
                <div class="card mb-6">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 card-title">Seller Banner</h5>
                    </div>
                    <div class="card-body">
                        <input type="file" name="banner" class="form-control @error('banner') is-invalid @enderror" />
                        @error('banner') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
