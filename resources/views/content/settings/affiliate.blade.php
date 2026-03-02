@extends('layouts.app')
@section('title', 'Affiliate Settings')
@include('content.settings.partials.settings-layout')

@section('content')
<div class="row">
    <div class="col-lg-3">
        @include('content.settings.partials.settings-nav')
    </div>
    <div class="col-lg-9">
        <div class="settings-header d-flex align-items-center gap-3">
            <div class="settings-header-icon"><i class="ti tabler-affiliate"></i></div>
            <div>
                <h4>Affiliate Program</h4>
                <p>Configure commissions, payouts, and referral tracking</p>
            </div>
        </div>

        <form id="settingsForm">
            @csrf
            @method('PUT')

            <div class="card setting-card">
                <div class="card-header">
                    <h5>General</h5>
                    <p>Control affiliate program availability and approval flow</p>
                </div>
                <div class="card-body row g-4">
                    <div class="col-md-4">
                        <label class="form-label">Affiliate Program</label>
                        <select name="affiliate[is_enabled]" class="form-select">
                            <option value="1" {{ ($settings['is_enabled'] ?? false) ? 'selected' : '' }}>Enabled</option>
                            <option value="0" {{ !($settings['is_enabled'] ?? false) ? 'selected' : '' }}>Disabled</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Auto-approve Applications</label>
                        <select name="affiliate[auto_approve]" class="form-select">
                            <option value="1" {{ ($settings['auto_approve'] ?? false) ? 'selected' : '' }}>Yes (instant activation)</option>
                            <option value="0" {{ !($settings['auto_approve'] ?? false) ? 'selected' : '' }}>No (manual review)</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Commission Basis</label>
                        <select class="form-select" name="affiliate[commission_basis]">
                            <option value="net" {{ ($settings['commission_basis'] ?? 'net') === 'net' ? 'selected' : '' }}>Net (after tax)</option>
                            <option value="gross" {{ ($settings['commission_basis'] ?? '') === 'gross' ? 'selected' : '' }}>Gross (total amount)</option>
                        </select>
                        <div class="form-label-description">How the order amount is calculated for commission</div>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Advanced Options</label>
                        <div class="d-flex flex-wrap gap-4 mt-1">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="affiliate[allow_l2_commissions]" id="allow_l2" value="1" {{ ($settings['allow_l2_commissions'] ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="allow_l2">Enable 2-Level Commissions</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="affiliate[allow_self_referral]" id="allow_self" value="1" {{ ($settings['allow_self_referral'] ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="allow_self">Allow Self-Referral</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card setting-card">
                <div class="card-header">
                    <h5>Commission Rates</h5>
                    <p>Default commission percentages for affiliates</p>
                </div>
                <div class="card-body row g-4">
                    <div class="col-md-4">
                        <label class="form-label">Default Commission Rate</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-percentage"></i></span>
                            <input type="number" step="0.01" class="form-control" name="affiliate[default_commission_rate]" value="{{ $settings['default_commission_rate'] ?? 5 }}" min="0" max="100">
                            <span class="input-group-text">%</span>
                        </div>
                        <div class="form-label-description">Applied on each qualified order</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Level 2 Commission Rate</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-percentage"></i></span>
                            <input type="number" step="0.01" class="form-control" name="affiliate[default_l2_rate]" value="{{ $settings['default_l2_rate'] ?? 2 }}" min="0" max="100">
                            <span class="input-group-text">%</span>
                        </div>
                        <div class="form-label-description">For sub-affiliate referrals (if enabled)</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Cookie Duration</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-clock"></i></span>
                            <input type="number" class="form-control" name="affiliate[cookie_duration_days]" value="{{ $settings['cookie_duration_days'] ?? 30 }}" min="1">
                            <span class="input-group-text">days</span>
                        </div>
                        <div class="form-label-description">How long the referral cookie stays active</div>
                    </div>
                </div>
            </div>

            <div class="card setting-card">
                <div class="card-header">
                    <h5>Payout Configuration</h5>
                    <p>Configure withdrawal limits, hold periods, and available methods</p>
                </div>
                <div class="card-body row g-4">
                    <div class="col-md-4">
                        <label class="form-label">Hold Period</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-clock-pause"></i></span>
                            <input type="number" class="form-control" name="affiliate[hold_period_days]" value="{{ $settings['hold_period_days'] ?? 14 }}" min="0">
                            <span class="input-group-text">days</span>
                        </div>
                        <div class="form-label-description">Days before pending commissions become available</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Minimum Withdrawal</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-currency-dollar"></i></span>
                            <input type="number" step="0.01" class="form-control" name="affiliate[min_withdrawal]" value="{{ $settings['min_withdrawal'] ?? 50 }}" min="0">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Withdrawal Fee</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-currency-dollar"></i></span>
                            <input type="number" step="0.01" class="form-control" name="affiliate[withdrawal_fee]" value="{{ $settings['withdrawal_fee'] ?? 0 }}" min="0">
                        </div>
                        <div class="form-label-description">Set to 0 for no fee</div>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Allowed Payout Methods</label>
                        @php $methods = (array)($settings['payout_methods'] ?? ['wallet']); @endphp
                        <div class="d-flex flex-wrap gap-3 mt-1">
                            @foreach(['wallet' => 'Wallet Transfer', 'paypal' => 'PayPal', 'bank' => 'Bank Transfer', 'crypto' => 'Cryptocurrency'] as $val => $label)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="affiliate[payout_methods][]" value="{{ $val }}" id="payout-{{ $val }}" {{ in_array($val, $methods) ? 'checked' : '' }}>
                                <label class="form-check-label" for="payout-{{ $val }}">{{ $label }}</label>
                            </div>
                            @endforeach
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
saveSettings('settingsForm', '{{ route("settings.affiliate.update") }}');
</script>
@endpush
