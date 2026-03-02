@extends('layouts.app')
@section('title', 'Edit Coupon')

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
    .discount-type-card {
        cursor: pointer;
        border: 2px solid var(--bs-border-color);
        transition: all .2s ease;
        border-radius: 8px;
    }
    .discount-type-card:hover { border-color: rgba(105,108,255,.4); }
    .discount-type-card.active { border-color: #696cff; background: rgba(105,108,255,.06); }
    .max-cap-row:not(.show) { display: none !important; }
    .usage-progress { height: 6px; border-radius: 3px; }
</style>
@endpush

@section('content')
<div class="app-ecommerce">

    @include('partials.alerts')

    <form method="POST" action="{{ route('coupons.update', $coupon->id) }}" id="couponForm">
        @csrf @method('PUT')

        {{-- Page Header --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <div>
                <h4 class="fw-bold mb-1"><i class="ti tabler-ticket ti-md me-1 text-primary"></i> Edit Coupon</h4>
                <p class="text-muted mb-0">
                    Update coupon <code class="fs-6">{{ $coupon->code }}</code>
                    @if($coupon->seller_id)
                        <span class="badge bg-label-warning ms-2"><i class="ti tabler-building-store ti-xs me-1"></i>Seller Coupon</span>
                    @else
                        <span class="badge bg-label-primary ms-2"><i class="ti tabler-world ti-xs me-1"></i>Global</span>
                    @endif
                </p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('coupons.index') }}" class="btn btn-label-secondary">
                    <i class="ti tabler-arrow-left ti-xs me-1"></i> Back
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="ti tabler-check ti-xs me-1"></i> Update Coupon
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
        <div class="coupon-preview p-4 mb-4">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="ti tabler-ticket fs-3"></i>
                        <span class="fs-4 fw-bold font-monospace" id="previewCode">{{ $coupon->code }}</span>
                    </div>
                    <p class="mb-0 opacity-75" id="previewDesc">{{ $coupon->description ?: 'Discount coupon' }}</p>
                </div>
                <div class="dashed-border ps-4 text-center">
                    <div class="fs-1 fw-bold" id="previewValue">{{ $coupon->type === 'percent' ? $coupon->value . '%' : '$' . number_format($coupon->value, 2) }}</div>
                    <small class="text-uppercase opacity-75">Discount</small>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">

                {{-- Seller Banner --}}
                @if($coupon->seller_id)
                <div class="alert alert-warning d-flex align-items-center gap-3 mb-4 shadow-sm">
                    <div class="avatar avatar-sm bg-warning">
                        <i class="ti tabler-building-store text-white"></i>
                    </div>
                    <div>
                        <strong>Seller Coupon</strong> — Created by
                        <span class="fw-semibold">{{ $coupon->seller?->user?->name ?? 'Seller #'.$coupon->seller_id }}</span>.
                        This coupon only applies to this seller's products.
                    </div>
                </div>
                @endif

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
                                <input type="text" name="code" id="couponCode" class="form-control form-control-lg text-uppercase font-monospace @error('code') is-invalid @enderror" value="{{ old('code', $coupon->code) }}" required>
                            </div>
                            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Brief description visible to customers">{{ old('description', $coupon->description) }}</textarea>
                        </div>

                        <label class="form-label fw-semibold mb-3">Discount Type <span class="text-danger">*</span></label>
                        <div class="row g-3 mb-4">
                            <div class="col-6">
                                <label class="discount-type-card d-block p-3 text-center {{ old('type', $coupon->type) === 'fixed' ? 'active' : '' }}">
                                    <input type="radio" name="type" value="fixed" class="d-none discount-type-radio" {{ old('type', $coupon->type) === 'fixed' ? 'checked' : '' }} required>
                                    <div class="avatar avatar-md mx-auto mb-2 bg-label-success">
                                        <i class="ti tabler-currency-dollar fs-4"></i>
                                    </div>
                                    <div class="fw-semibold">Fixed Amount</div>
                                    <small class="text-muted">Flat dollar discount</small>
                                </label>
                            </div>
                            <div class="col-6">
                                <label class="discount-type-card d-block p-3 text-center {{ old('type', $coupon->type) === 'percent' ? 'active' : '' }}">
                                    <input type="radio" name="type" value="percent" class="d-none discount-type-radio" {{ old('type', $coupon->type) === 'percent' ? 'checked' : '' }}>
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
                                    <input type="number" step="0.01" name="value" id="discountValue" class="form-control @error('value') is-invalid @enderror" value="{{ old('value', $coupon->value) }}" required>
                                </div>
                                @error('value')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 max-cap-row {{ old('type', $coupon->type) === 'percent' ? 'show' : '' }}" id="maxCapCol">
                                <label class="form-label fw-semibold">Max Discount Cap</label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"><i class="ti tabler-shield-dollar ti-xs"></i></span>
                                    <input type="number" step="0.01" name="max_discount_amount" class="form-control" value="{{ old('max_discount_amount', $coupon->max_discount_amount) }}" placeholder="No cap">
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
                                    <input type="number" step="0.01" name="min_order_amount" class="form-control" value="{{ old('min_order_amount', $coupon->min_order_amount) }}" placeholder="No minimum">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Maximum Spend</label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"><i class="ti tabler-arrow-bar-up ti-xs"></i></span>
                                    <input type="number" step="0.01" name="max_order_amount" class="form-control" value="{{ old('max_order_amount', $coupon->max_order_amount) }}" placeholder="No maximum">
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
                                        <option value="{{ $cat->id }}" {{ in_array($cat->id, old('include_categories', $coupon->include_categories ?? [])) ? 'selected' : '' }}>{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Leave empty = applies to all</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Exclude Categories</label>
                                <select name="exclude_categories[]" class="form-select select2" multiple data-placeholder="None excluded">
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ in_array($cat->id, old('exclude_categories', $coupon->exclude_categories ?? [])) ? 'selected' : '' }}>{{ $cat->name }}</option>
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
                                        <option value="{{ $product->id }}" {{ in_array($product->id, old('include_products', $coupon->include_products ?? [])) ? 'selected' : '' }}>{{ $product->title }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Leave empty = applies to all</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Exclude Products</label>
                                <select name="exclude_products[]" class="form-select select2" multiple data-placeholder="None excluded">
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" {{ in_array($product->id, old('exclude_products', $coupon->exclude_products ?? [])) ? 'selected' : '' }}>{{ $product->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                {{-- Usage Stats --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header border-bottom">
                        <h5 class="card-title mb-0"><i class="ti tabler-chart-bar ti-md me-2 text-primary"></i> Usage Stats</h5>
                    </div>
                    <div class="card-body pt-4">
                        @php
                            $usagePercent = $coupon->usage_limit ? round(($coupon->used / $coupon->usage_limit) * 100) : 0;
                            $usageColor = $usagePercent >= 90 ? 'danger' : ($usagePercent >= 60 ? 'warning' : 'success');
                        @endphp
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="fw-semibold text-heading">Redemptions</span>
                            <span class="badge bg-label-{{ $usageColor }}">
                                {{ $coupon->used }} / {{ $coupon->usage_limit ?? '∞' }}
                            </span>
                        </div>
                        @if($coupon->usage_limit)
                        <div class="progress usage-progress mb-3">
                            <div class="progress-bar bg-{{ $usageColor }}" style="width: {{ $usagePercent }}%"></div>
                        </div>
                        @endif

                        <div class="row g-2 text-center">
                            <div class="col-4">
                                <div class="p-2 rounded bg-lighter">
                                    <div class="fw-bold text-heading">{{ $coupon->used }}</div>
                                    <small class="text-muted">Used</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 rounded bg-lighter">
                                    <div class="fw-bold text-heading">{{ $coupon->usage_limit ?? '∞' }}</div>
                                    <small class="text-muted">Limit</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 rounded bg-lighter">
                                    <div class="fw-bold text-heading">{{ $coupon->usage_limit ? max(0, $coupon->usage_limit - $coupon->used) : '∞' }}</div>
                                    <small class="text-muted">Left</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

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
                                <input type="number" name="usage_limit" class="form-control" value="{{ old('usage_limit', $coupon->usage_limit) }}" placeholder="Unlimited">
                            </div>
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-semibold">Per User Limit</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="ti tabler-user ti-xs"></i></span>
                                <input type="number" name="usage_limit_per_user" class="form-control" value="{{ old('usage_limit_per_user', $coupon->usage_limit_per_user) }}" placeholder="Unlimited">
                            </div>
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
                                <input type="text" name="starts_at" class="form-control flatpickr-date" value="{{ old('starts_at', optional($coupon->starts_at)->format('Y-m-d')) }}" placeholder="Immediately">
                            </div>
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-semibold">Expiry Date</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="ti tabler-calendar-x ti-xs"></i></span>
                                <input type="text" name="expires_at" class="form-control flatpickr-date @error('expires_at') is-invalid @enderror" value="{{ old('expires_at', optional($coupon->expires_at)->format('Y-m-d')) }}" placeholder="Never expires">
                            </div>
                            @error('expires_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        @if($coupon->expires_at)
                        <div class="mt-3">
                            @if($coupon->expires_at->isPast())
                                <div class="d-flex align-items-center gap-2 text-danger small">
                                    <i class="ti tabler-alert-circle ti-xs"></i>
                                    <span>Expired {{ $coupon->expires_at->diffForHumans() }}</span>
                                </div>
                            @else
                                <div class="d-flex align-items-center gap-2 text-success small">
                                    <i class="ti tabler-clock ti-xs"></i>
                                    <span>Expires {{ $coupon->expires_at->diffForHumans() }}</span>
                                </div>
                            @endif
                        </div>
                        @endif
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
                                <small class="text-muted">
                                    @if($coupon->isActive())
                                        Currently accepting redemptions
                                    @else
                                        Not available to customers
                                    @endif
                                </small>
                            </div>
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive" {{ old('is_active', $coupon->is_active) ? 'checked' : '' }} style="width:3em; height:1.5em;">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Danger Zone --}}
                <div class="card border-danger shadow-sm">
                    <div class="card-body text-center py-4">
                        <i class="ti tabler-alert-triangle ti-lg text-danger mb-2 d-block"></i>
                        <p class="text-muted small mb-3">Permanently remove this coupon</p>
                        <button type="button" class="btn btn-outline-danger btn-sm btn-delete-coupon" data-url="{{ route('coupons.destroy', $coupon->id) }}">
                            <i class="ti tabler-trash ti-xs me-1"></i> Delete Coupon
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
(function($) {
    'use strict';

    $('.flatpickr-date').flatpickr({
        dateFormat: 'Y-m-d',
        allowInput: true,
        altInput: true,
        altFormat: 'M j, Y',
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

    function updatePreview() {
        const code = $('#couponCode').val() || 'CODE';
        const desc = $('textarea[name="description"]').val() || 'Discount coupon';
        const type = $('input[name="type"]:checked').val();
        const value = $('#discountValue').val() || '0';
        $('#previewCode').text(code.toUpperCase());
        $('#previewDesc').text(desc);
        $('#previewValue').text(type === 'percent' ? value + '%' : '$' + parseFloat(value).toFixed(2));
    }

    $('#couponCode, textarea[name="description"], input[name="type"], #discountValue').on('input change', updatePreview);

    $(document).on('click', '.btn-delete-coupon', function() {
        const url = $(this).data('url');
        Swal.fire({
            title: 'Delete Coupon?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete',
            customClass: { confirmButton: 'btn btn-danger me-3', cancelButton: 'btn btn-label-secondary' },
            buttonsStyling: false
        }).then(r => {
            if (!r.isConfirmed) return;
            $.ajax({
                url,
                type: 'POST',
                data: { _token: $('meta[name="csrf-token"]').attr('content'), _method: 'DELETE' },
                success: () => {
                    Swal.fire({ icon: 'success', title: 'Deleted', showConfirmButton: false, timer: 1500, timerProgressBar: true })
                        .then(() => window.location.href = '{{ route("coupons.index") }}');
                },
                error: () => Swal.fire({ icon: 'error', title: 'Failed', timer: 1500, showConfirmButton: false })
            });
        });
    });

})(jQuery);
</script>
@endpush
