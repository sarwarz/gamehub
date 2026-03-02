@extends('layouts.app')
@section('title', 'Wallet Settings')

@push('page-css')
<style>
    .ws-header {
        background: linear-gradient(135deg, #696cff 0%, #8592ff 50%, #a3acff 100%);
        border-radius: 12px;
        padding: 1.75rem 2rem;
        color: #fff;
        position: relative;
        overflow: hidden;
    }
    .ws-header::before {
        content: '';
        position: absolute;
        width: 200px; height: 200px;
        border-radius: 50%;
        background: rgba(255,255,255,.08);
        top: -60px; right: -40px;
    }
    .ws-header::after {
        content: '';
        position: absolute;
        width: 120px; height: 120px;
        border-radius: 50%;
        background: rgba(255,255,255,.06);
        bottom: -30px; right: 80px;
    }

    .ws-nav {
        position: sticky;
        top: 80px;
        z-index: 5;
    }
    .ws-nav .nav-link {
        display: flex;
        align-items: center;
        gap: .625rem;
        padding: .65rem 1rem;
        border-radius: 8px;
        color: #6f6b7d;
        font-weight: 500;
        font-size: .875rem;
        transition: all .2s;
        border: 1px solid transparent;
    }
    .ws-nav .nav-link:hover {
        background: rgba(105,108,255,.06);
        color: #696cff;
    }
    .ws-nav .nav-link.active {
        background: rgba(105,108,255,.1);
        color: #696cff;
        border-color: rgba(105,108,255,.2);
        box-shadow: 0 2px 8px rgba(105,108,255,.12);
    }
    .ws-nav .nav-icon {
        width: 32px; height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .ws-section {
        scroll-margin-top: 90px;
    }
    .ws-card {
        border: 1px solid #e7e7e8;
        border-radius: 12px;
        transition: box-shadow .25s, border-color .25s;
    }
    .ws-card:hover {
        border-color: rgba(105,108,255,.25);
        box-shadow: 0 4px 18px rgba(105,108,255,.08);
    }
    .ws-card .card-header {
        background: transparent !important;
        border-bottom: 1px solid #f0f0f0;
        padding: 1.5rem 2rem !important;
    }
    .ws-card .card-body {
        padding: 2rem !important;
    }
    .ws-section-icon {
        width: 38px; height: 38px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        flex-shrink: 0;
    }

    .ws-toggle {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        padding: 1.25rem 1.5rem;
        border-radius: 10px;
        border: 1px solid #ededee;
        transition: all .2s;
        background: #fafafa;
    }
    .ws-toggle:has(.form-check-input:checked) {
        background: rgba(40,199,111,.04);
        border-color: rgba(40,199,111,.3);
    }
    .ws-toggle .form-check-input {
        width: 2.75em;
        height: 1.4em;
        margin-top: .15rem;
        flex-shrink: 0;
    }
    .ws-toggle-info h6 { margin-bottom: .15rem; font-size: .9rem; }
    .ws-toggle-info p  { margin-bottom: 0; font-size: .8rem; color: #a5a3ae; line-height: 1.35; }

    .ws-input-label {
        font-size: .8125rem;
        font-weight: 600;
        color: #5d596c;
        margin-bottom: .4rem;
    }
    .ws-input-group .input-group-text {
        background: #f7f7f8;
        border-color: #e0e0e0;
        font-weight: 600;
        color: #696cff;
        min-width: 52px;
        justify-content: center;
    }
    .ws-input-group .form-control,
    .ws-input-group .form-select {
        border-color: #e0e0e0;
    }
    .ws-input-group .form-control:focus,
    .ws-input-group .form-select:focus {
        border-color: #696cff;
        box-shadow: 0 0 0 .2rem rgba(105,108,255,.12);
    }
    .ws-hint {
        font-size: .75rem;
        color: #a5a3ae;
        margin-top: .3rem;
    }

    .ws-sidebar-card {
        border-radius: 12px;
        border: 1px solid #e7e7e8;
    }
    .ws-sidebar-card .card-header {
        padding: 1.25rem 1.5rem !important;
    }
    .ws-sidebar-card .card-body {
        padding: 1.5rem !important;
    }
    .ws-status-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: .5rem 0;
    }
    .ws-status-row + .ws-status-row {
        border-top: 1px solid #f5f5f5;
    }
    .ws-status-dot {
        width: 8px; height: 8px;
        border-radius: 50%;
        display: inline-block;
        margin-right: .5rem;
    }
    .ws-fee-badge {
        display: inline-flex;
        align-items: center;
        padding: .25rem .65rem;
        border-radius: 6px;
        font-size: .8rem;
        font-weight: 600;
        background: #f7f7f8;
        color: #5d596c;
    }

    .ws-save-bar {
        position: sticky;
        bottom: 0;
        z-index: 10;
        background: rgba(255,255,255,.92);
        backdrop-filter: blur(8px);
        border-top: 1px solid #eee;
        padding: .85rem 0;
        margin: 0 -1.5rem;
        padding-left: 1.5rem;
        padding-right: 1.5rem;
    }
</style>
@endpush

@section('content')
<div class="container-xxl flex-grow-1">

    {{-- Header --}}
    <div class="ws-header mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center position-relative" style="z-index:1;">
            <div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <div style="width:42px;height:42px;border-radius:10px;background:rgba(255,255,255,.18);display:flex;align-items:center;justify-content:center;">
                        <i class="ti tabler-wallet" style="font-size:1.35rem;"></i>
                    </div>
                    <h4 class="text-white mb-0 fw-bold">Wallet Settings</h4>
                </div>
                <p class="mb-0" style="opacity:.8;font-size:.9rem;">Configure deposits, transfers, withdrawals, fees and notifications for your platform wallet.</p>
            </div>
            <div class="d-flex gap-2 mt-2 mt-md-0">
                <a href="{{ route('wallet-settings.edit') }}" class="btn btn-sm" style="background:rgba(255,255,255,.18);color:#fff;border:1px solid rgba(255,255,255,.25);">
                    <i class="ti tabler-refresh ti-xs me-1"></i> Reset
                </a>
                <button type="submit" form="wallet-settings-form" class="btn btn-sm btn-light fw-semibold">
                    <i class="ti tabler-device-floppy ti-xs me-1"></i> Save Changes
                </button>
            </div>
        </div>
    </div>

    {{-- Alerts --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <div class="d-flex align-items-center gap-2">
                <span class="alert-icon"><i class="ti tabler-check"></i></span>
                {{ session('success') }}
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <div class="d-flex align-items-start gap-2">
                <span class="alert-icon mt-1"><i class="ti tabler-alert-circle"></i></span>
                <div>
                    <strong>Please fix the following:</strong>
                    <ul class="mb-0 mt-1 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('wallet-settings.update') }}" id="wallet-settings-form">
        @csrf
        @method('PUT')

        <div class="row">
            {{-- ============================== LEFT COLUMN ============================== --}}
            <div class="col-xl-8 col-lg-7">

                {{-- 1 · WALLET CORE --}}
                <div id="section-core" class="ws-section mb-4">
                    <div class="card ws-card">
                        <div class="card-header d-flex align-items-center gap-3">
                            <span class="ws-section-icon" style="background:rgba(105,108,255,.1);color:#696cff;">
                                <i class="ti tabler-plug-connected"></i>
                            </span>
                            <div>
                                <h6 class="mb-0">Wallet Core</h6>
                                <small class="text-muted">Master switch for the entire wallet system</small>
                            </div>
                        </div>
                        <div class="card-body">
                            <input type="hidden" name="wallet_enabled" value="0">
                            <div class="ws-toggle">
                                <input class="form-check-input" type="checkbox" id="walletEnabled"
                                    name="wallet_enabled" value="1"
                                    {{ old('wallet_enabled', $walletSetting->wallet_enabled) ? 'checked' : '' }}>
                                <div class="ws-toggle-info">
                                    <h6>Enable Wallet System</h6>
                                    <p>Turning this off disables the entire wallet feature for all users. Deposits, transfers, withdrawals and wallet payments will all be unavailable.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 2 · DEPOSIT --}}
                <div id="section-deposit" class="ws-section mb-4">
                    <div class="card ws-card">
                        <div class="card-header d-flex align-items-center gap-3">
                            <span class="ws-section-icon" style="background:rgba(40,199,111,.1);color:#28c76f;">
                                <i class="ti tabler-arrow-down-left"></i>
                            </span>
                            <div>
                                <h6 class="mb-0">Deposit (Top-Up)</h6>
                                <small class="text-muted">Control how users add funds to their wallets</small>
                            </div>
                        </div>
                        <div class="card-body">
                            <input type="hidden" name="deposit_enabled" value="0">
                            <div class="ws-toggle mb-4">
                                <input class="form-check-input" type="checkbox" id="depositEnabled"
                                    name="deposit_enabled" value="1"
                                    {{ old('deposit_enabled', $walletSetting->deposit_enabled) ? 'checked' : '' }}>
                                <div class="ws-toggle-info">
                                    <h6>Allow Wallet Deposits</h6>
                                    <p>Users can top-up their wallet balance via payment gateways.</p>
                                </div>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <label class="ws-input-label">Minimum Deposit</label>
                                    <div class="input-group ws-input-group">
                                        <span class="input-group-text">{{ $walletSetting->currency ?? 'USD' }}</span>
                                        <input type="number" step="0.01" min="0" name="min_topup_amount"
                                            class="form-control @error('min_topup_amount') is-invalid @enderror"
                                            value="{{ old('min_topup_amount', $walletSetting->min_topup_amount) }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="ws-input-label">Maximum Deposit</label>
                                    <div class="input-group ws-input-group">
                                        <span class="input-group-text">{{ $walletSetting->currency ?? 'USD' }}</span>
                                        <input type="number" step="0.01" min="0" name="max_topup_amount"
                                            class="form-control @error('max_topup_amount') is-invalid @enderror"
                                            value="{{ old('max_topup_amount', $walletSetting->max_topup_amount) }}"
                                            placeholder="No limit">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="ws-input-label">Daily Deposit Limit</label>
                                    <div class="input-group ws-input-group">
                                        <span class="input-group-text">{{ $walletSetting->currency ?? 'USD' }}</span>
                                        <input type="number" step="0.01" min="0" name="max_daily_deposit_limit"
                                            class="form-control @error('max_daily_deposit_limit') is-invalid @enderror"
                                            value="{{ old('max_daily_deposit_limit', $walletSetting->max_daily_deposit_limit) }}"
                                            placeholder="No limit">
                                    </div>
                                    <div class="ws-hint">Max total deposits per user per day</div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="ws-input-label">Allowed Payment Gateways</label>
                                <select name="allowed_payment_gateways[]" class="form-select select2" multiple>
                                    @foreach ($paymentMethods as $method)
                                        <option value="{{ $method->code }}"
                                            {{ in_array($method->code, old('allowed_payment_gateways', $walletSetting->allowed_payment_gateways ?? [])) ? 'selected' : '' }}>
                                            {{ $method->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="ws-hint">Select which payment gateways users can use to top-up. Only enabled gateways shown.</div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="ws-input-label">Deposit Fee Type</label>
                                    <select name="gateway_charge_type" class="form-select ws-input-group @error('gateway_charge_type') is-invalid @enderror" id="depositFeeType">
                                        <option value="percentage" {{ old('gateway_charge_type', $walletSetting->gateway_charge_type) === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                        <option value="fixed" {{ old('gateway_charge_type', $walletSetting->gateway_charge_type) === 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="ws-input-label">Deposit Fee Amount</label>
                                    <div class="input-group ws-input-group">
                                        <input type="number" step="0.01" min="0" name="gateway_charge_amount"
                                            class="form-control @error('gateway_charge_amount') is-invalid @enderror"
                                            value="{{ old('gateway_charge_amount', $walletSetting->gateway_charge_amount) }}">
                                        <span class="input-group-text" id="depositFeeUnit">
                                            {{ old('gateway_charge_type', $walletSetting->gateway_charge_type) === 'percentage' ? '%' : ($walletSetting->currency ?? 'USD') }}
                                        </span>
                                    </div>
                                    <div class="ws-hint">Set to 0 for no fee</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 3 · USAGE & LIMITS --}}
                <div id="section-usage" class="ws-section mb-4">
                    <div class="card ws-card">
                        <div class="card-header d-flex align-items-center gap-3">
                            <span class="ws-section-icon" style="background:rgba(0,186,209,.1);color:#00bad1;">
                                <i class="ti tabler-adjustments-horizontal"></i>
                            </span>
                            <div>
                                <h6 class="mb-0">Usage & Limits</h6>
                                <small class="text-muted">Partial payments, auto-deduct and balance caps</small>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <input type="hidden" name="partial_payment_enabled" value="0">
                                    <div class="ws-toggle">
                                        <input class="form-check-input" type="checkbox" id="partialPayment"
                                            name="partial_payment_enabled" value="1"
                                            {{ old('partial_payment_enabled', $walletSetting->partial_payment_enabled) ? 'checked' : '' }}>
                                        <div class="ws-toggle-info">
                                            <h6>Partial Wallet Payment</h6>
                                            <p>Pay partially with wallet, rest via another gateway.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <input type="hidden" name="auto_deduct_wallet_for_partial" value="0">
                                    <div class="ws-toggle">
                                        <input class="form-check-input" type="checkbox" id="autoDeduct"
                                            name="auto_deduct_wallet_for_partial" value="1"
                                            {{ old('auto_deduct_wallet_for_partial', $walletSetting->auto_deduct_wallet_for_partial) ? 'checked' : '' }}>
                                        <div class="ws-toggle-info">
                                            <h6>Auto-Deduct Balance</h6>
                                            <p>Automatically apply wallet balance at checkout.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="ws-input-label">Maximum Wallet Balance</label>
                                    <div class="input-group ws-input-group">
                                        <span class="input-group-text">{{ $walletSetting->currency ?? 'USD' }}</span>
                                        <input type="number" step="0.01" min="0" name="max_wallet_balance"
                                            class="form-control @error('max_wallet_balance') is-invalid @enderror"
                                            value="{{ old('max_wallet_balance', $walletSetting->max_wallet_balance) }}"
                                            placeholder="No limit">
                                    </div>
                                    <div class="ws-hint">Max balance a single wallet can hold. Empty = no cap.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 4 · TRANSFER --}}
                <div id="section-transfer" class="ws-section mb-4">
                    <div class="card ws-card">
                        <div class="card-header d-flex align-items-center gap-3">
                            <span class="ws-section-icon" style="background:rgba(255,159,67,.1);color:#ff9f43;">
                                <i class="ti tabler-arrows-exchange"></i>
                            </span>
                            <div>
                                <h6 class="mb-0">Wallet Transfer</h6>
                                <small class="text-muted">User-to-user balance transfers</small>
                            </div>
                        </div>
                        <div class="card-body">
                            <input type="hidden" name="wallet_transfer_enabled" value="0">
                            <div class="ws-toggle mb-4">
                                <input class="form-check-input" type="checkbox" id="transferEnabled"
                                    name="wallet_transfer_enabled" value="1"
                                    {{ old('wallet_transfer_enabled', $walletSetting->wallet_transfer_enabled) ? 'checked' : '' }}>
                                <div class="ws-toggle-info">
                                    <h6>Enable Wallet Transfers</h6>
                                    <p>Allow users to send wallet balance to other users.</p>
                                </div>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <label class="ws-input-label">Min Transfer</label>
                                    <div class="input-group ws-input-group">
                                        <span class="input-group-text">{{ $walletSetting->currency ?? 'USD' }}</span>
                                        <input type="number" step="0.01" min="0" name="min_transfer_amount"
                                            class="form-control @error('min_transfer_amount') is-invalid @enderror"
                                            value="{{ old('min_transfer_amount', $walletSetting->min_transfer_amount) }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="ws-input-label">Max Transfer</label>
                                    <div class="input-group ws-input-group">
                                        <span class="input-group-text">{{ $walletSetting->currency ?? 'USD' }}</span>
                                        <input type="number" step="0.01" min="0" name="max_transfer_amount"
                                            class="form-control @error('max_transfer_amount') is-invalid @enderror"
                                            value="{{ old('max_transfer_amount', $walletSetting->max_transfer_amount) }}"
                                            placeholder="No limit">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="ws-input-label">Daily Transfer Limit</label>
                                    <div class="input-group ws-input-group">
                                        <span class="input-group-text">{{ $walletSetting->currency ?? 'USD' }}</span>
                                        <input type="number" step="0.01" min="0" name="max_daily_transfer_limit"
                                            class="form-control @error('max_daily_transfer_limit') is-invalid @enderror"
                                            value="{{ old('max_daily_transfer_limit', $walletSetting->max_daily_transfer_limit) }}"
                                            placeholder="No limit">
                                    </div>
                                    <div class="ws-hint">Max total transfers per user per day</div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="ws-input-label">Transfer Fee Type</label>
                                    <select name="transfer_charge_type" class="form-select @error('transfer_charge_type') is-invalid @enderror" id="transferFeeType">
                                        <option value="percentage" {{ old('transfer_charge_type', $walletSetting->transfer_charge_type) === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                        <option value="fixed" {{ old('transfer_charge_type', $walletSetting->transfer_charge_type) === 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="ws-input-label">Transfer Fee Amount</label>
                                    <div class="input-group ws-input-group">
                                        <input type="number" step="0.01" min="0" name="transfer_charge_amount"
                                            class="form-control @error('transfer_charge_amount') is-invalid @enderror"
                                            value="{{ old('transfer_charge_amount', $walletSetting->transfer_charge_amount) }}">
                                        <span class="input-group-text" id="transferFeeUnit">
                                            {{ old('transfer_charge_type', $walletSetting->transfer_charge_type) === 'percentage' ? '%' : ($walletSetting->currency ?? 'USD') }}
                                        </span>
                                    </div>
                                    <div class="ws-hint">Set to 0 for no fee</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 5 · WITHDRAWAL --}}
                <div id="section-withdraw" class="ws-section mb-4">
                    <div class="card ws-card">
                        <div class="card-header d-flex align-items-center gap-3">
                            <span class="ws-section-icon" style="background:rgba(234,84,85,.1);color:#ea5455;">
                                <i class="ti tabler-arrow-up-right"></i>
                            </span>
                            <div>
                                <h6 class="mb-0">Withdrawal</h6>
                                <small class="text-muted">How users withdraw funds from their wallets</small>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <input type="hidden" name="withdraw_enabled" value="0">
                                    <div class="ws-toggle">
                                        <input class="form-check-input" type="checkbox" id="withdrawEnabled"
                                            name="withdraw_enabled" value="1"
                                            {{ old('withdraw_enabled', $walletSetting->withdraw_enabled) ? 'checked' : '' }}>
                                        <div class="ws-toggle-info">
                                            <h6>Enable Withdrawals</h6>
                                            <p>Allow users to withdraw wallet balance to their bank or payment account.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <input type="hidden" name="auto_approve_withdraw" value="0">
                                    <div class="ws-toggle">
                                        <input class="form-check-input" type="checkbox" id="autoApproveWithdraw"
                                            name="auto_approve_withdraw" value="1"
                                            {{ old('auto_approve_withdraw', $walletSetting->auto_approve_withdraw) ? 'checked' : '' }}>
                                        <div class="ws-toggle-info">
                                            <h6>Auto-Approve Withdrawals</h6>
                                            <p>Skip manual review and process withdrawals automatically.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <label class="ws-input-label">Min Withdrawal</label>
                                    <div class="input-group ws-input-group">
                                        <span class="input-group-text">{{ $walletSetting->currency ?? 'USD' }}</span>
                                        <input type="number" step="0.01" min="0" name="min_withdraw_amount"
                                            class="form-control @error('min_withdraw_amount') is-invalid @enderror"
                                            value="{{ old('min_withdraw_amount', $walletSetting->min_withdraw_amount) }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="ws-input-label">Max Withdrawal</label>
                                    <div class="input-group ws-input-group">
                                        <span class="input-group-text">{{ $walletSetting->currency ?? 'USD' }}</span>
                                        <input type="number" step="0.01" min="0" name="max_withdraw_amount"
                                            class="form-control @error('max_withdraw_amount') is-invalid @enderror"
                                            value="{{ old('max_withdraw_amount', $walletSetting->max_withdraw_amount) }}"
                                            placeholder="No limit">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="ws-input-label">Daily Withdrawal Limit</label>
                                    <div class="input-group ws-input-group">
                                        <span class="input-group-text">{{ $walletSetting->currency ?? 'USD' }}</span>
                                        <input type="number" step="0.01" min="0" name="max_daily_withdraw_limit"
                                            class="form-control @error('max_daily_withdraw_limit') is-invalid @enderror"
                                            value="{{ old('max_daily_withdraw_limit', $walletSetting->max_daily_withdraw_limit) }}"
                                            placeholder="No limit">
                                    </div>
                                    <div class="ws-hint">Max total withdrawals per user per day</div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="ws-input-label">Withdrawal Fee Type</label>
                                    <select name="withdraw_charge_type" class="form-select @error('withdraw_charge_type') is-invalid @enderror" id="withdrawFeeType">
                                        <option value="percentage" {{ old('withdraw_charge_type', $walletSetting->withdraw_charge_type) === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                        <option value="fixed" {{ old('withdraw_charge_type', $walletSetting->withdraw_charge_type) === 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="ws-input-label">Withdrawal Fee Amount</label>
                                    <div class="input-group ws-input-group">
                                        <input type="number" step="0.01" min="0" name="withdraw_charge_amount"
                                            class="form-control @error('withdraw_charge_amount') is-invalid @enderror"
                                            value="{{ old('withdraw_charge_amount', $walletSetting->withdraw_charge_amount) }}">
                                        <span class="input-group-text" id="withdrawFeeUnit">
                                            {{ old('withdraw_charge_type', $walletSetting->withdraw_charge_type) === 'percentage' ? '%' : ($walletSetting->currency ?? 'USD') }}
                                        </span>
                                    </div>
                                    <div class="ws-hint">Set to 0 for no fee</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 6 · NOTIFICATIONS --}}
                <div id="section-notifications" class="ws-section mb-4">
                    <div class="card ws-card">
                        <div class="card-header d-flex align-items-center gap-3">
                            <span class="ws-section-icon" style="background:rgba(168,170,174,.1);color:#a8aaae;">
                                <i class="ti tabler-bell"></i>
                            </span>
                            <div>
                                <h6 class="mb-0">Notifications</h6>
                                <small class="text-muted">Low balance alerts for users</small>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-6">
                                    <input type="hidden" name="low_balance_alert_enabled" value="0">
                                    <div class="ws-toggle">
                                        <input class="form-check-input" type="checkbox" id="lowBalanceAlert"
                                            name="low_balance_alert_enabled" value="1"
                                            {{ old('low_balance_alert_enabled', $walletSetting->low_balance_alert_enabled) ? 'checked' : '' }}>
                                        <div class="ws-toggle-info">
                                            <h6>Low Balance Alert</h6>
                                            <p>Notify users when wallet balance drops below a threshold.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="ws-input-label">Low Balance Threshold</label>
                                    <div class="input-group ws-input-group">
                                        <span class="input-group-text">{{ $walletSetting->currency ?? 'USD' }}</span>
                                        <input type="number" step="0.01" min="0" name="low_balance_threshold"
                                            class="form-control @error('low_balance_threshold') is-invalid @enderror"
                                            value="{{ old('low_balance_threshold', $walletSetting->low_balance_threshold) }}">
                                    </div>
                                    <div class="ws-hint">Alert triggers when balance falls below this amount</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Sticky Save Bar --}}
                <div class="ws-save-bar d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="ti tabler-device-floppy ti-xs me-1"></i> Save All Settings
                    </button>
                </div>

            </div>

            {{-- ============================== RIGHT COLUMN ============================== --}}
            <div class="col-xl-4 col-lg-5">
                <div class="ws-nav mb-4">
                    <div class="card ws-sidebar-card">
                        <div class="card-body p-4">
                            <h6 class="text-uppercase text-muted fw-bold mb-3" style="font-size:.7rem;letter-spacing:1px;">Navigation</h6>
                            <nav class="nav flex-column gap-1">
                                <a href="#section-core" class="nav-link active" data-section="section-core">
                                    <span class="ws-nav-icon" style="background:rgba(105,108,255,.1);color:#696cff;"><i class="ti tabler-plug-connected"></i></span>
                                    Wallet Core
                                </a>
                                <a href="#section-deposit" class="nav-link" data-section="section-deposit">
                                    <span class="ws-nav-icon" style="background:rgba(40,199,111,.1);color:#28c76f;"><i class="ti tabler-arrow-down-left"></i></span>
                                    Deposit
                                </a>
                                <a href="#section-usage" class="nav-link" data-section="section-usage">
                                    <span class="ws-nav-icon" style="background:rgba(0,186,209,.1);color:#00bad1;"><i class="ti tabler-adjustments-horizontal"></i></span>
                                    Usage & Limits
                                </a>
                                <a href="#section-transfer" class="nav-link" data-section="section-transfer">
                                    <span class="ws-nav-icon" style="background:rgba(255,159,67,.1);color:#ff9f43;"><i class="ti tabler-arrows-exchange"></i></span>
                                    Transfer
                                </a>
                                <a href="#section-withdraw" class="nav-link" data-section="section-withdraw">
                                    <span class="ws-nav-icon" style="background:rgba(234,84,85,.1);color:#ea5455;"><i class="ti tabler-arrow-up-right"></i></span>
                                    Withdrawal
                                </a>
                                <a href="#section-notifications" class="nav-link" data-section="section-notifications">
                                    <span class="ws-nav-icon" style="background:rgba(168,170,174,.1);color:#a8aaae;"><i class="ti tabler-bell"></i></span>
                                    Notifications
                                </a>
                            </nav>
                        </div>
                    </div>
                </div>

                {{-- Quick Reference --}}
                <div class="position-sticky" style="top:380px;">
                    <div class="card ws-sidebar-card mb-4">
                        <div class="card-header py-3 px-4" style="background:#f8f8f9;">
                            <h6 class="mb-0 d-flex align-items-center gap-2">
                                <i class="ti tabler-info-circle text-muted"></i> Quick Reference
                            </h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <div class="text-uppercase text-muted fw-bold mb-2" style="font-size:.68rem;letter-spacing:.8px;">Currency</div>
                                <span class="badge bg-label-primary px-3 py-2" style="font-size:.85rem;">{{ $walletSetting->currency ?? 'USD' }}</span>
                            </div>

                            <div class="mb-3" id="features-panel">
                                <div class="text-uppercase text-muted fw-bold mb-2" style="font-size:.68rem;letter-spacing:.8px;">Features</div>
                                @php
                                    $features = [
                                        ['Wallet',      'walletEnabled',       'tabler-wallet'],
                                        ['Deposits',    'depositEnabled',      'tabler-arrow-down-left'],
                                        ['Transfers',   'transferEnabled',     'tabler-arrows-exchange'],
                                        ['Withdrawals', 'withdrawEnabled',     'tabler-arrow-up-right'],
                                        ['Partial Pay', 'partialPayment',      'tabler-discount-2'],
                                        ['Low Alert',   'lowBalanceAlert',     'tabler-bell'],
                                    ];
                                @endphp
                                @foreach ($features as [$label, $checkboxId, $icon])
                                    <div class="ws-status-row" data-toggle-id="{{ $checkboxId }}">
                                        <span class="d-flex align-items-center gap-2">
                                            <span class="ws-status-dot"></span>
                                            <i class="ti {{ $icon }} ti-xs text-muted"></i>
                                            <span style="font-size:.85rem;">{{ $label }}</span>
                                        </span>
                                        <span class="badge ws-feature-badge" style="font-size:.7rem;"></span>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mb-3">
                                <div class="text-uppercase text-muted fw-bold mb-2" style="font-size:.68rem;letter-spacing:.8px;">Fee Summary</div>
                                @php
                                    $cur = $walletSetting->currency ?? 'USD';
                                    $fees = [
                                        ['Deposit',  $walletSetting->gateway_charge_amount,  $walletSetting->gateway_charge_type],
                                        ['Transfer', $walletSetting->transfer_charge_amount, $walletSetting->transfer_charge_type],
                                        ['Withdraw', $walletSetting->withdraw_charge_amount, $walletSetting->withdraw_charge_type],
                                    ];
                                @endphp
                                @foreach ($fees as [$feeLabel, $feeAmt, $feeType])
                                    <div class="ws-status-row">
                                        <span style="font-size:.85rem;color:#6f6b7d;">{{ $feeLabel }}</span>
                                        <span class="ws-fee-badge">
                                            {{ $feeAmt }}{{ $feeType === 'percentage' ? '%' : ' '.$cur }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>

                            <div>
                                <div class="text-uppercase text-muted fw-bold mb-2" style="font-size:.68rem;letter-spacing:.8px;">Limits</div>
                                @php
                                    $limits = [
                                        ['Max Balance',    $walletSetting->max_wallet_balance],
                                        ['Daily Deposit',  $walletSetting->max_daily_deposit_limit],
                                        ['Daily Transfer', $walletSetting->max_daily_transfer_limit],
                                        ['Daily Withdraw', $walletSetting->max_daily_withdraw_limit],
                                    ];
                                @endphp
                                @foreach ($limits as [$limLabel, $limVal])
                                    <div class="ws-status-row">
                                        <span style="font-size:.85rem;color:#6f6b7d;">{{ $limLabel }}</span>
                                        <span class="ws-fee-badge">
                                            {{ $limVal ? number_format($limVal, 2) . ' ' . $cur : 'No limit' }}
                                        </span>
                                    </div>
                                @endforeach
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
$(function () {
    // Scrollspy for sidebar navigation
    var sections = document.querySelectorAll('.ws-section');
    var navLinks = document.querySelectorAll('.ws-nav .nav-link');

    function updateActiveNav() {
        var scrollPos = window.scrollY + 120;
        sections.forEach(function (section) {
            if (section.offsetTop <= scrollPos && (section.offsetTop + section.offsetHeight) > scrollPos) {
                navLinks.forEach(function (link) { link.classList.remove('active'); });
                var active = document.querySelector('.ws-nav .nav-link[data-section="' + section.id + '"]');
                if (active) active.classList.add('active');
            }
        });
    }

    window.addEventListener('scroll', updateActiveNav);

    navLinks.forEach(function (link) {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            var target = document.getElementById(this.dataset.section);
            if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    // Dynamic fee unit toggle
    function bindFeeToggle(selectId, unitId, currency) {
        $('#' + selectId).on('change', function () {
            $('#' + unitId).text($(this).val() === 'percentage' ? '%' : currency);
        });
    }
    var cur = '{{ $walletSetting->currency ?? "USD" }}';
    bindFeeToggle('depositFeeType', 'depositFeeUnit', cur);
    bindFeeToggle('transferFeeType', 'transferFeeUnit', cur);
    bindFeeToggle('withdrawFeeType', 'withdrawFeeUnit', cur);

    // Live-sync sidebar features with form toggles
    function syncFeatures() {
        $('#features-panel .ws-status-row').each(function () {
            var toggleId = $(this).data('toggle-id');
            var isOn = $('#' + toggleId).is(':checked');
            $(this).find('.ws-status-dot').css('background', isOn ? '#28c76f' : '#ea5455');
            var badge = $(this).find('.ws-feature-badge');
            badge.text(isOn ? 'ON' : 'OFF')
                 .removeClass('bg-label-success bg-label-danger')
                 .addClass(isOn ? 'bg-label-success' : 'bg-label-danger');
        });
    }
    syncFeatures();
    $('#walletEnabled, #depositEnabled, #transferEnabled, #withdrawEnabled, #partialPayment, #autoDeduct, #lowBalanceAlert, #autoApproveWithdraw').on('change', syncFeatures);
});
</script>
@endpush
