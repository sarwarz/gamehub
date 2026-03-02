@extends('layouts.app')
@section('title', 'Create Coupon')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}">
<style>
    .coupon-preview {
        background: linear-gradient(135deg, #696cff 0%, #8592ff 100%);
        border-radius: 12px;
        color: #fff;
        position: relative;
        overflow: hidden;
    }
    .coupon-preview::before {
        content: '';
        position: absolute;
        width: 24px; height: 24px;
        background: var(--bs-body-bg);
        border-radius: 50%;
        top: 50%; left: -12px;
        transform: translateY(-50%);
    }
    .coupon-preview::after {
        content: '';
        position: absolute;
        width: 24px; height: 24px;
        background: var(--bs-body-bg);
        border-radius: 50%;
        top: 50%; right: -12px;
        transform: translateY(-50%);
    }
    .coupon-preview .dashed-border {
        border-left: 2px dashed rgba(255,255,255,0.35);
    }
    .code-gen-btn { cursor: pointer; transition: color .2s; }
    .code-gen-btn:hover { color: var(--bs-primary) !important; }
    .discount-type-card {
        cursor: pointer;
        border: 2px solid var(--bs-border-color);
        transition: all .2s ease;
        border-radius: 8px;
    }
    .discount-type-card:hover { border-color: rgba(105,108,255,.4); }
    .discount-type-card.active { border-color: #696cff; background: rgba(105,108,255,.06); }
    .max-cap-row:not(.show) { display: none !important; }
</style>
@endpush

@section('content')
<div class="app-ecommerce">

    @include('partials.alerts')

    <form method="POST" action="{{ route('coupons.store') }}" id="couponForm">
        @csrf

        {{-- Page Header --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <div>
                <h4 class="fw-bold mb-1"><i class="ti tabler-ticket ti-md me-1 text-primary"></i> Create Coupon</h4>
                <p class="text-muted mb-0">Configure discount rules, usage restrictions and schedule</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('coupons.index') }}" class="btn btn-label-secondary">
                    <i class="ti tabler-arrow-left ti-xs me-1"></i> Back
                </a>
                <button type="button" class="btn btn-label-primary" id="previewBtn">
                    <i class="ti tabler-eye ti-xs me-1"></i> Preview
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="ti tabler-check ti-xs me-1"></i> Save Coupon
                </button>
            </div>
        </div>

        @if ($errors->any())
        <div class="alert alert-danger alert-dismissible mb-4">
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
        @endif

        {{-- Live Preview --}}
        <div class="coupon-preview p-4 mb-4 d-none" id="couponPreview">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="ti tabler-ticket fs-3"></i>
                        <span class="fs-4 fw-bold font-monospace" id="previewCode">CODE</span>
                    </div>
                    <p class="mb-0 opacity-75" id="previewDesc">Discount coupon</p>
                </div>
                <div class="dashed-border ps-4 text-center">
                    <div class="fs-1 fw-bold" id="previewValue">0%</div>
                    <small class="text-uppercase opacity-75">Discount</small>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">

                {{-- Coupon Info --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header border-bottom d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0"><i class="ti tabler-info-circle ti-md me-2 text-primary"></i> Coupon Information</h5>
                        <span class="badge bg-label-primary">Required</span>
                    </div>
                    <div class="card-body pt-4">
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Coupon Code <span class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="ti tabler-hash ti-xs"></i></span>
                                <input type="text" name="code" id="couponCode" class="form-control form-control-lg text-uppercase font-monospace @error('code') is-invalid @enderror" value="{{ old('code') }}" placeholder="e.g. SUMMER2026" required>
                                <span class="input-group-text code-gen-btn" id="generateCode" title="Generate random code">
                                    <i class="ti tabler-refresh ti-xs"></i>
                                </span>
                            </div>
                            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="text-muted">Alphanumeric, uppercase. Click <i class="ti tabler-refresh ti-xs"></i> to auto-generate.</small>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Brief description visible to customers (e.g. &quot;Get 20% off all games this summer!&quot;)">{{ old('description') }}</textarea>
                        </div>

                        <label class="form-label fw-semibold mb-3">Discount Type <span class="text-danger">*</span></label>
                        <div class="row g-3 mb-4">
                            <div class="col-6">
                                <label class="discount-type-card d-block p-3 text-center {{ old('type', 'fixed') === 'fixed' ? 'active' : '' }}">
                                    <input type="radio" name="type" value="fixed" class="d-none discount-type-radio" {{ old('type', 'fixed') === 'fixed' ? 'checked' : '' }} required>
                                    <div class="avatar avatar-md mx-auto mb-2 bg-label-success">
                                        <i class="ti tabler-currency-dollar fs-4"></i>
                                    </div>
                                    <div class="fw-semibold">Fixed Amount</div>
                                    <small class="text-muted">Flat dollar discount</small>
                                </label>
                            </div>
                            <div class="col-6">
                                <label class="discount-type-card d-block p-3 text-center {{ old('type') === 'percent' ? 'active' : '' }}">
                                    <input type="radio" name="type" value="percent" class="d-none discount-type-radio" {{ old('type') === 'percent' ? 'checked' : '' }}>
                                    <div class="avatar avatar-md mx-auto mb-2 bg-label-info">
                                        <i class="ti tabler-percentage fs-4"></i>
                                    </div>
                                    <div class="fw-semibold">Percentage</div>
                                    <small class="text-muted">Percent off subtotal</small>
                                </label>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Discount Value <span class="text-danger">*</span></label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text" id="valuePrefix"><i class="ti tabler-currency-dollar ti-xs" id="valuePrefixIcon"></i></span>
                                    <input type="number" step="0.01" name="value" id="discountValue" class="form-control @error('value') is-invalid @enderror" value="{{ old('value') }}" placeholder="0.00" required>
                                </div>
                                @error('value')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 max-cap-row {{ old('type') === 'percent' ? 'show' : '' }}" id="maxCapCol">
                                <label class="form-label fw-semibold">Max Discount Cap</label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"><i class="ti tabler-shield-dollar ti-xs"></i></span>
                                    <input type="number" step="0.01" name="max_discount_amount" class="form-control" value="{{ old('max_discount_amount') }}" placeholder="No cap">
                                </div>
                                <small class="text-muted">Caps the max dollar amount for % coupons</small>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Usage Restrictions --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header border-bottom d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0"><i class="ti tabler-shield-lock ti-md me-2 text-warning"></i> Usage Restrictions</h5>
                        <span class="badge bg-label-secondary">Optional</span>
                    </div>
                    <div class="card-body pt-4">
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Minimum Spend</label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"><i class="ti tabler-arrow-bar-down ti-xs"></i></span>
                                    <input type="number" step="0.01" name="min_order_amount" class="form-control" value="{{ old('min_order_amount') }}" placeholder="No minimum">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Maximum Spend</label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"><i class="ti tabler-arrow-bar-up ti-xs"></i></span>
                                    <input type="number" step="0.01" name="max_order_amount" class="form-control" value="{{ old('max_order_amount') }}" placeholder="No maximum">
                                </div>
                            </div>
                        </div>

                        <div class="divider text-muted">
                            <div class="divider-text"><i class="ti tabler-category ti-xs me-1"></i> Category Filters</div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Include Categories</label>
                                <select name="include_categories[]" class="form-select select2" multiple data-placeholder="All categories">
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ in_array($cat->id, old('include_categories', [])) ? 'selected' : '' }}>{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Leave empty = applies to all</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Exclude Categories</label>
                                <select name="exclude_categories[]" class="form-select select2" multiple data-placeholder="None excluded">
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ in_array($cat->id, old('exclude_categories', [])) ? 'selected' : '' }}>{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="divider text-muted">
                            <div class="divider-text"><i class="ti tabler-package ti-xs me-1"></i> Product Filters</div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Include Products</label>
                                <select name="include_products[]" class="form-select select2" multiple data-placeholder="All products">
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" {{ in_array($product->id, old('include_products', [])) ? 'selected' : '' }}>{{ $product->title }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Leave empty = applies to all</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Exclude Products</label>
                                <select name="exclude_products[]" class="form-select select2" multiple data-placeholder="None excluded">
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" {{ in_array($product->id, old('exclude_products', [])) ? 'selected' : '' }}>{{ $product->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                {{-- Usage Limits --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header border-bottom">
                        <h5 class="card-title mb-0"><i class="ti tabler-abacus ti-md me-2 text-info"></i> Usage Limits</h5>
                    </div>
                    <div class="card-body pt-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Total Usage Limit</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="ti tabler-users-group ti-xs"></i></span>
                                <input type="number" name="usage_limit" class="form-control" value="{{ old('usage_limit') }}" placeholder="Unlimited">
                            </div>
                            <small class="text-muted">Max total redemptions allowed</small>
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-semibold">Per User Limit</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="ti tabler-user ti-xs"></i></span>
                                <input type="number" name="usage_limit_per_user" class="form-control" value="{{ old('usage_limit_per_user') }}" placeholder="Unlimited">
                            </div>
                            <small class="text-muted">Max times a single user can redeem</small>
                        </div>
                    </div>
                </div>

                {{-- Schedule --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header border-bottom">
                        <h5 class="card-title mb-0"><i class="ti tabler-calendar-event ti-md me-2 text-success"></i> Schedule</h5>
                    </div>
                    <div class="card-body pt-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Start Date</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="ti tabler-calendar-plus ti-xs"></i></span>
                                <input type="text" name="starts_at" class="form-control flatpickr-date" value="{{ old('starts_at') }}" placeholder="Immediately">
                            </div>
                            <small class="text-muted">Leave empty for immediate start</small>
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-semibold">Expiry Date</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="ti tabler-calendar-x ti-xs"></i></span>
                                <input type="text" name="expires_at" class="form-control flatpickr-date @error('expires_at') is-invalid @enderror" value="{{ old('expires_at') }}" placeholder="Never expires">
                            </div>
                            @error('expires_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="text-muted">Auto-deactivates after this date</small>
                        </div>
                    </div>
                </div>

                {{-- Status --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header border-bottom">
                        <h5 class="card-title mb-0"><i class="ti tabler-toggle-right ti-md me-2 text-primary"></i> Status</h5>
                    </div>
                    <div class="card-body pt-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="fw-semibold">Coupon is Active</div>
                                <small class="text-muted">Enable to start accepting this coupon</small>
                            </div>
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive" checked style="width:3em; height:1.5em;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('page-js')
<script>
(function($) {
    'use strict';

    $('.flatpickr-date').flatpickr({
        dateFormat: 'Y-m-d',
        allowInput: true,
        altInput: true,
        altFormat: 'M j, Y',
    });

    $('#generateCode').on('click', function() {
        const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        let code = '';
        for (let i = 0; i < 8; i++) code += chars.charAt(Math.floor(Math.random() * chars.length));
        $('#couponCode').val(code).trigger('input');
    });

    $('.discount-type-card').on('click', function() {
        $('.discount-type-card').removeClass('active');
        $(this).addClass('active');
    });

    $('input[name="type"]').on('change', function() {
        const isPercent = $(this).val() === 'percent';
        $('#valuePrefixIcon').attr('class', isPercent ? 'ti tabler-percentage ti-xs' : 'ti tabler-currency-dollar ti-xs');
        $('#maxCapCol').toggleClass('show', isPercent);
    }).filter(':checked').trigger('change');

    $('#previewBtn').on('click', function() {
        const $preview = $('#couponPreview');
        $preview.toggleClass('d-none');
        if (!$preview.hasClass('d-none')) updatePreview();
    });

    function updatePreview() {
        const code = $('#couponCode').val() || 'CODE';
        const desc = $('textarea[name="description"]').val() || 'Discount coupon';
        const type = $('input[name="type"]:checked').val();
        const value = $('#discountValue').val() || '0';
        $('#previewCode').text(code.toUpperCase());
        $('#previewDesc').text(desc);
        $('#previewValue').text(type === 'percent' ? value + '%' : '$' + parseFloat(value).toFixed(2));
    }

    $('#couponCode, textarea[name="description"], input[name="type"], #discountValue').on('input change', function() {
        if (!$('#couponPreview').hasClass('d-none')) updatePreview();
    });

})(jQuery);
</script>
@endpush
