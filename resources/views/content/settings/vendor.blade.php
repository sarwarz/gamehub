@extends('layouts.app')
@section('title', 'Vendor / Seller Settings')
@include('content.settings.partials.settings-layout')

@section('content')
<div class="row">
    <div class="col-lg-3">
        @include('content.settings.partials.settings-nav')
    </div>
    <div class="col-lg-9">
        <div class="settings-header d-flex align-items-center gap-3">
            <div class="settings-header-icon"><i class="ti tabler-users-group"></i></div>
            <div>
                <h4>Vendor / Seller Settings</h4>
                <p>Manage seller registration, commissions, and payouts</p>
            </div>
        </div>

        <form id="settingsForm">
            @csrf
            @method('PUT')

            <div class="card setting-card">
                <div class="card-header">
                    <h5>Registration</h5>
                    <p>Control seller registration and verification requirements</p>
                </div>
                <div class="card-body row g-4">
                    <div class="col-md-4">
                        <label class="form-label">Allow Seller Registration</label>
                        <select name="vendor[registration_enabled]" class="form-select">
                            <option value="1" {{ ($settings['registration_enabled'] ?? '1') == '1' ? 'selected' : '' }}>Enabled</option>
                            <option value="0" {{ ($settings['registration_enabled'] ?? '1') == '0' ? 'selected' : '' }}>Disabled</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Auto-approve New Sellers</label>
                        <select name="vendor[auto_approve]" class="form-select">
                            <option value="1" {{ ($settings['auto_approve'] ?? '0') == '1' ? 'selected' : '' }}>Yes (instant activation)</option>
                            <option value="0" {{ ($settings['auto_approve'] ?? '0') == '0' ? 'selected' : '' }}>No (manual review)</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Require Verification Documents</label>
                        <select name="vendor[require_documents]" class="form-select">
                            <option value="1" {{ ($settings['require_documents'] ?? '0') == '1' ? 'selected' : '' }}>Required</option>
                            <option value="0" {{ ($settings['require_documents'] ?? '0') == '0' ? 'selected' : '' }}>Optional</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="card setting-card">
                <div class="card-header">
                    <h5>Commission</h5>
                    <p>Platform commission settings for each sale</p>
                </div>
                <div class="card-body row g-4">
                    @php $commissionMode = $settings['commission_mode'] ?? 'fixed'; @endphp
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Commission Mode</label>
                        <div class="d-flex gap-3 mt-1">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="vendor[commission_mode]" id="mode-fixed" value="fixed" {{ $commissionMode === 'fixed' ? 'checked' : '' }}>
                                <label class="form-check-label" for="mode-fixed">
                                    <strong>Fixed for all products</strong>
                                    <span class="d-block text-muted small">Same commission rate applies to every sale</span>
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="vendor[commission_mode]" id="mode-product-type" value="product_type" {{ $commissionMode === 'product_type' ? 'checked' : '' }}>
                                <label class="form-check-label" for="mode-product-type">
                                    <strong>Per product type</strong>
                                    <span class="d-block text-muted small">Each product type has its own commission rate</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6" id="fixed-commission-fields">
                        <label class="form-label">Default Commission Rate</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-percentage"></i></span>
                            <input type="number" step="0.01" name="vendor[commission_rate]" class="form-control" value="{{ $settings['commission_rate'] ?? 10 }}" min="0" max="100">
                            <span class="input-group-text">%</span>
                        </div>
                        <div class="form-label-description">Platform commission on each sale.</div>
                    </div>
                    <div class="col-md-6" id="fixed-commission-type">
                        <label class="form-label">Commission Type</label>
                        <select name="vendor[commission_type]" class="form-select">
                            <option value="percentage" {{ ($settings['commission_type'] ?? 'percentage') === 'percentage' ? 'selected' : '' }}>Percentage</option>
                            <option value="fixed" {{ ($settings['commission_type'] ?? 'percentage') === 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                        </select>
                    </div>

                    <div class="col-md-12 d-none" id="product-type-commission-info">
                        <div class="alert alert-info mb-0 d-flex align-items-start gap-3">
                            <i class="ti tabler-info-circle ti-md mt-1"></i>
                            <div>
                                <strong>Per-product-type commission is active.</strong><br>
                                Commission rates are configured individually on each product type.
                                If a product has multiple types, the highest rate is used.
                                If no type has a rate set, the default rate above is used as fallback.
                                <a href="{{ route('types.index') }}" class="fw-semibold">Manage Product Types →</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card setting-card">
                <div class="card-header">
                    <h5>Payout Configuration</h5>
                    <p>Configure withdrawal limits, schedule, and available methods</p>
                </div>
                <div class="card-body row g-4">
                    <div class="col-md-4">
                        <label class="form-label">Minimum Withdrawal</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-currency-dollar"></i></span>
                            <input type="number" step="0.01" name="vendor[min_withdrawal]" class="form-control" value="{{ $settings['min_withdrawal'] ?? 50 }}" min="0">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Payout Schedule</label>
                        <select name="vendor[payout_schedule]" class="form-select">
                            @foreach(['manual' => 'Manual', 'weekly' => 'Weekly', 'biweekly' => 'Bi-weekly', 'monthly' => 'Monthly'] as $val => $label)
                                <option value="{{ $val }}" {{ ($settings['payout_schedule'] ?? 'manual') === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Hold Period (days)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-clock-pause"></i></span>
                            <input type="number" name="vendor[hold_period_days]" class="form-control" value="{{ $settings['hold_period_days'] ?? 7 }}" min="0">
                        </div>
                        <div class="form-label-description">Days before pending earnings become available</div>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Allowed Payout Methods</label>
                        @php $payoutMethods = (array)($settings['payout_methods'] ?? ['bank', 'paypal']); @endphp
                        <div class="d-flex flex-wrap gap-3 mt-1">
                            @foreach(['bank' => 'Bank Transfer', 'paypal' => 'PayPal', 'crypto' => 'Cryptocurrency'] as $val => $label)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="vendor[payout_methods][]" value="{{ $val }}" id="payout-{{ $val }}" {{ in_array($val, $payoutMethods) ? 'checked' : '' }}>
                                <label class="form-check-label" for="payout-{{ $val }}">{{ $label }}</label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="card setting-card">
                <div class="card-header">
                    <h5>Limits</h5>
                    <p>Set seller product and withdrawal constraints</p>
                </div>
                <div class="card-body row g-4">
                    <div class="col-md-6">
                        <label class="form-label">Max Products Per Seller</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-box"></i></span>
                            <input type="number" name="vendor[max_products]" class="form-control" value="{{ $settings['max_products'] ?? 0 }}" min="0">
                        </div>
                        <div class="form-label-description">Set to 0 for unlimited</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Max Pending Withdrawals</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-clock-dollar"></i></span>
                            <input type="number" name="vendor[max_pending_withdrawals]" class="form-control" value="{{ $settings['max_pending_withdrawals'] ?? 1 }}" min="1">
                        </div>
                    </div>
                </div>
            </div>

            <div class="save-bar">
                <button type="button" class="btn btn-label-secondary" onclick="location.reload()">Discard</button>
                <button type="submit" class="btn btn-primary"><i class="ti tabler-device-floppy me-1"></i> Save Changes</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('page-js')
<script>
saveSettings('settingsForm', '{{ route("settings.vendor.update") }}');

function toggleCommissionMode() {
    var mode = document.querySelector('input[name="vendor[commission_mode]"]:checked').value;
    var fixedFields = document.getElementById('fixed-commission-fields');
    var fixedType = document.getElementById('fixed-commission-type');
    var typeInfo = document.getElementById('product-type-commission-info');

    if (mode === 'product_type') {
        fixedFields.classList.add('d-none');
        fixedType.classList.add('d-none');
        typeInfo.classList.remove('d-none');
    } else {
        fixedFields.classList.remove('d-none');
        fixedType.classList.remove('d-none');
        typeInfo.classList.add('d-none');
    }
}

document.querySelectorAll('input[name="vendor[commission_mode]"]').forEach(function(radio) {
    radio.addEventListener('change', toggleCommissionMode);
});
toggleCommissionMode();
</script>
@endpush
