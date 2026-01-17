@extends('layouts.app')
@section('title', 'Edit Coupon')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}">
@endpush

@section('content')
<div class="app-ecommerce">

    @include('partials.alerts')

    <form method="POST" action="{{ route('coupons.update', $coupon->id) }}">
        @csrf
        @method('PUT')

        <!-- Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
            <div>
                <h4 class="mb-1">Edit Coupon</h4>
                <p class="text-muted mb-0">
                    Update coupon rules and restrictions
                </p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('coupons.index') }}" class="btn btn-label-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Coupon</button>
            </div>
        </div>

        <div class="row">

            <!-- LEFT COLUMN -->
            <div class="col-lg-8">

                <!-- Coupon Info -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Coupon Information</h5>
                    </div>
                    <div class="card-body">

                        <!-- Code -->
                        <div class="mb-3">
                            <label class="form-label">Coupon Code</label>
                            <input type="text"
                                   name="code"
                                   class="form-control text-uppercase @error('code') is-invalid @enderror"
                                   value="{{ old('code', $coupon->code) }}"
                                   required>
                            @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <!-- Type & Value -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Discount Type</label>
                                <select name="type"
                                        class="form-select @error('type') is-invalid @enderror"
                                        required>
                                    <option value="fixed" {{ old('type', $coupon->type) === 'fixed' ? 'selected' : '' }}>
                                        Fixed Amount
                                    </option>
                                    <option value="percent" {{ old('type', $coupon->type) === 'percent' ? 'selected' : '' }}>
                                        Percentage (%)
                                    </option>
                                </select>
                                @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Discount Value</label>
                                <input type="number"
                                       step="0.01"
                                       name="value"
                                       class="form-control @error('value') is-invalid @enderror"
                                       value="{{ old('value', $coupon->value) }}"
                                       required>
                                @error('value') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Usage Restrictions -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Usage Restrictions</h5>
                    </div>
                    <div class="card-body">

                        <!-- Min / Max -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Minimum Spend</label>
                                <input type="number" step="0.01"
                                       name="min_order_amount"
                                       class="form-control"
                                       value="{{ old('min_order_amount', $coupon->min_order_amount) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Maximum Spend</label>
                                <input type="number" step="0.01"
                                       name="max_order_amount"
                                       class="form-control"
                                       value="{{ old('max_order_amount', $coupon->max_order_amount) }}">
                            </div>
                        </div>

                        <!-- Include Categories -->
                        <div class="mb-3">
                            <label class="form-label">Include Categories</label>
                            <select name="include_categories[]"
                                    class="form-select select2"
                                    multiple>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}"
                                        {{ in_array($cat->id, old('include_categories', $coupon->include_categories ?? [])) ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Exclude Categories -->
                        <div class="mb-3">
                            <label class="form-label">Exclude Categories</label>
                            <select name="exclude_categories[]"
                                    class="form-select select2"
                                    multiple>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}"
                                        {{ in_array($cat->id, old('exclude_categories', $coupon->exclude_categories ?? [])) ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Include Products -->
                        <div class="mb-3">
                            <label class="form-label">Include Products</label>
                            <select name="include_products[]"
                                    class="form-select select2"
                                    multiple>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}"
                                        {{ in_array($product->id, old('include_products', $coupon->include_products ?? [])) ? 'selected' : '' }}>
                                        {{ $product->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Exclude Products -->
                        <div class="mb-3">
                            <label class="form-label">Exclude Products</label>
                            <select name="exclude_products[]"
                                    class="form-select select2"
                                    multiple>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}"
                                        {{ in_array($product->id, old('exclude_products', $coupon->exclude_products ?? [])) ? 'selected' : '' }}>
                                        {{ $product->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                    </div>
                </div>

            </div>

            <!-- RIGHT COLUMN -->
            <div class="col-lg-4">

                <!-- Usage Limits -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Usage Limits</h5>
                    </div>
                    <div class="card-body">

                        <div class="mb-3">
                            <label class="form-label">Usage Limit</label>
                            <input type="number"
                                   name="usage_limit"
                                   class="form-control"
                                   value="{{ old('usage_limit', $coupon->usage_limit) }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Usage Limit Per User</label>
                            <input type="number"
                                   name="usage_limit_per_user"
                                   class="form-control"
                                   value="{{ old('usage_limit_per_user', $coupon->usage_limit_per_user) }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Expiry Date</label>
                            <input type="date"
                                   name="expires_at"
                                   class="form-control"
                                   value="{{ old('expires_at', optional($coupon->expires_at)->format('Y-m-d')) }}">
                        </div>

                        <hr>

                        <div class="form-check form-switch">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="is_active"
                                   value="1"
                                   {{ old('is_active', $coupon->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold">
                                Coupon is active
                            </label>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </form>
</div>
@endsection

