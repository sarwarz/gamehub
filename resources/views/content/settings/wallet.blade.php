@extends('layouts.app')
@section('title', 'Payment & Wallet Settings')
@include('content.settings.partials.settings-layout')

@section('content')
<div class="row">
    <div class="col-lg-3">
        @include('content.settings.partials.settings-nav')
    </div>
    <div class="col-lg-9">
        <div class="settings-header d-flex align-items-center gap-3">
            <div class="settings-header-icon"><i class="ti tabler-wallet"></i></div>
            <div>
                <h4>Payment & Wallet</h4>
                <p>Configure wallet features, deposit/withdrawal limits, and fees</p>
            </div>
        </div>

        <form id="settingsForm">
            @csrf
            @method('PUT')

            <div class="card setting-card">
                <div class="card-header">
                    <h5><i class="ti tabler-wallet text-primary me-2"></i>Wallet Core</h5>
                    <p>Enable or disable core wallet features for your platform</p>
                </div>
                <div class="card-body">
                    <div class="setting-toggle">
                        <div class="setting-toggle-info">
                            <h6>Enable Wallet</h6>
                            <p>Allow users to maintain a wallet balance on the platform</p>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="wallet_enabled" value="1" {{ $walletSetting->wallet_enabled ? 'checked' : '' }}>
                        </div>
                    </div>
                    <div class="setting-toggle">
                        <div class="setting-toggle-info">
                            <h6>Enable Deposits</h6>
                            <p>Allow users to add funds to their wallet</p>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="deposit_enabled" value="1" {{ $walletSetting->deposit_enabled ? 'checked' : '' }}>
                        </div>
                    </div>
                    <div class="setting-toggle">
                        <div class="setting-toggle-info">
                            <h6>Partial Payment</h6>
                            <p>Allow users to pay partially with wallet balance during checkout</p>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="partial_payment_enabled" value="1" {{ $walletSetting->partial_payment_enabled ? 'checked' : '' }}>
                        </div>
                    </div>
                    <div class="setting-toggle">
                        <div class="setting-toggle-info">
                            <h6>Auto-Deduct for Partial</h6>
                            <p>Automatically deduct wallet balance first when partial payment is enabled</p>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="auto_deduct_wallet_for_partial" value="1" {{ $walletSetting->auto_deduct_wallet_for_partial ? 'checked' : '' }}>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card setting-card">
                <div class="card-header">
                    <h5><i class="ti tabler-cash-banknote text-primary me-2"></i>Deposit Settings</h5>
                    <p>Configure deposit limits and gateway charges</p>
                </div>
                <div class="card-body row g-4">
                    <div class="col-md-4">
                        <label class="form-label">Minimum Top-Up Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">{{ $currencyService->symbol() }}</span>
                            <input type="number" name="min_topup_amount" class="form-control" value="{{ $walletSetting->min_topup_amount }}" step="0.01" min="0">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Maximum Top-Up Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">{{ $currencyService->symbol() }}</span>
                            <input type="number" name="max_topup_amount" class="form-control" value="{{ $walletSetting->max_topup_amount }}" step="0.01" min="0">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Max Daily Deposit Limit</label>
                        <div class="input-group">
                            <span class="input-group-text">{{ $currencyService->symbol() }}</span>
                            <input type="number" name="max_daily_deposit_limit" class="form-control" value="{{ $walletSetting->max_daily_deposit_limit }}" step="0.01" min="0">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Gateway Charge Type</label>
                        <select name="gateway_charge_type" class="form-select">
                            <option value="percentage" {{ $walletSetting->gateway_charge_type === 'percentage' ? 'selected' : '' }}>Percentage</option>
                            <option value="fixed" {{ $walletSetting->gateway_charge_type === 'fixed' ? 'selected' : '' }}>Fixed</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Gateway Charge Amount</label>
                        <input type="number" name="gateway_charge_amount" class="form-control" value="{{ $walletSetting->gateway_charge_amount }}" step="0.01" min="0">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Allowed Payment Gateways</label>
                        <div class="row g-2">
                            @foreach($paymentMethods as $method)
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="allowed_payment_gateways[]" value="{{ $method->code }}" id="gw_{{ $method->id }}"
                                            {{ in_array($method->code, $walletSetting->allowed_payment_gateways ?? []) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="gw_{{ $method->id }}">{{ $method->name }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="card setting-card">
                <div class="card-header">
                    <h5><i class="ti tabler-transfer text-primary me-2"></i>Transfer Settings</h5>
                    <p>Configure wallet-to-wallet transfer rules and fees</p>
                </div>
                <div class="card-body">
                    <div class="setting-toggle mb-3">
                        <div class="setting-toggle-info">
                            <h6>Enable Wallet Transfers</h6>
                            <p>Allow users to transfer funds to other users' wallets</p>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="wallet_transfer_enabled" value="1" {{ $walletSetting->wallet_transfer_enabled ? 'checked' : '' }}>
                        </div>
                    </div>
                    <div class="row g-4">
                        <div class="col-md-4">
                            <label class="form-label">Minimum Transfer Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">{{ $currencyService->symbol() }}</span>
                                <input type="number" name="min_transfer_amount" class="form-control" value="{{ $walletSetting->min_transfer_amount }}" step="0.01" min="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Maximum Transfer Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">{{ $currencyService->symbol() }}</span>
                                <input type="number" name="max_transfer_amount" class="form-control" value="{{ $walletSetting->max_transfer_amount }}" step="0.01" min="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Max Daily Transfer Limit</label>
                            <div class="input-group">
                                <span class="input-group-text">{{ $currencyService->symbol() }}</span>
                                <input type="number" name="max_daily_transfer_limit" class="form-control" value="{{ $walletSetting->max_daily_transfer_limit }}" step="0.01" min="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Transfer Charge Type</label>
                            <select name="transfer_charge_type" class="form-select">
                                <option value="percentage" {{ $walletSetting->transfer_charge_type === 'percentage' ? 'selected' : '' }}>Percentage</option>
                                <option value="fixed" {{ $walletSetting->transfer_charge_type === 'fixed' ? 'selected' : '' }}>Fixed</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Transfer Charge Amount</label>
                            <input type="number" name="transfer_charge_amount" class="form-control" value="{{ $walletSetting->transfer_charge_amount }}" step="0.01" min="0">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card setting-card">
                <div class="card-header">
                    <h5><i class="ti tabler-arrow-bar-to-up text-primary me-2"></i>Withdrawal Settings</h5>
                    <p>Configure withdrawal limits, approval, and fees</p>
                </div>
                <div class="card-body">
                    <div class="setting-toggle">
                        <div class="setting-toggle-info">
                            <h6>Enable Withdrawals</h6>
                            <p>Allow users to withdraw funds from their wallet</p>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="withdraw_enabled" value="1" {{ $walletSetting->withdraw_enabled ? 'checked' : '' }}>
                        </div>
                    </div>
                    <div class="setting-toggle mb-3">
                        <div class="setting-toggle-info">
                            <h6>Auto-Approve Withdrawals</h6>
                            <p>Automatically approve withdrawal requests without admin review</p>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="auto_approve_withdraw" value="1" {{ $walletSetting->auto_approve_withdraw ? 'checked' : '' }}>
                        </div>
                    </div>
                    <div class="row g-4">
                        <div class="col-md-4">
                            <label class="form-label">Minimum Withdrawal</label>
                            <div class="input-group">
                                <span class="input-group-text">{{ $currencyService->symbol() }}</span>
                                <input type="number" name="min_withdraw_amount" class="form-control" value="{{ $walletSetting->min_withdraw_amount }}" step="0.01" min="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Maximum Withdrawal</label>
                            <div class="input-group">
                                <span class="input-group-text">{{ $currencyService->symbol() }}</span>
                                <input type="number" name="max_withdraw_amount" class="form-control" value="{{ $walletSetting->max_withdraw_amount }}" step="0.01" min="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Max Daily Withdrawal Limit</label>
                            <div class="input-group">
                                <span class="input-group-text">{{ $currencyService->symbol() }}</span>
                                <input type="number" name="max_daily_withdraw_limit" class="form-control" value="{{ $walletSetting->max_daily_withdraw_limit }}" step="0.01" min="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Withdrawal Charge Type</label>
                            <select name="withdraw_charge_type" class="form-select">
                                <option value="percentage" {{ $walletSetting->withdraw_charge_type === 'percentage' ? 'selected' : '' }}>Percentage</option>
                                <option value="fixed" {{ $walletSetting->withdraw_charge_type === 'fixed' ? 'selected' : '' }}>Fixed</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Withdrawal Charge Amount</label>
                            <input type="number" name="withdraw_charge_amount" class="form-control" value="{{ $walletSetting->withdraw_charge_amount }}" step="0.01" min="0">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card setting-card">
                <div class="card-header">
                    <h5><i class="ti tabler-bell-ringing text-primary me-2"></i>Notifications</h5>
                    <p>Configure wallet balance alerts and limits</p>
                </div>
                <div class="card-body">
                    <div class="setting-toggle mb-3">
                        <div class="setting-toggle-info">
                            <h6>Low Balance Alert</h6>
                            <p>Notify users when their wallet balance falls below a threshold</p>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="low_balance_alert_enabled" value="1" {{ $walletSetting->low_balance_alert_enabled ? 'checked' : '' }}>
                        </div>
                    </div>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label">Low Balance Threshold</label>
                            <div class="input-group">
                                <span class="input-group-text">{{ $currencyService->symbol() }}</span>
                                <input type="number" name="low_balance_threshold" class="form-control" value="{{ $walletSetting->low_balance_threshold }}" step="0.01" min="0">
                            </div>
                            <div class="form-label-description">Alert when balance drops below this amount</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Maximum Wallet Balance</label>
                            <div class="input-group">
                                <span class="input-group-text">{{ $currencyService->symbol() }}</span>
                                <input type="number" name="max_wallet_balance" class="form-control" value="{{ $walletSetting->max_wallet_balance }}" step="0.01" min="0">
                            </div>
                            <div class="form-label-description">Maximum amount a user can hold in their wallet (0 = unlimited)</div>
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
saveSettings('settingsForm', '{{ route("settings.wallet.update") }}');
</script>
@endpush
