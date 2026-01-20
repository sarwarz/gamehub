@extends('layouts.app')
@section('title', 'Wallet Settings')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}" />
@endpush

@section('content')
<div class="app-ecommerce">

    {{-- Success Message --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bx bx-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('wallet-settings.update') }}">
        @csrf
        @method('PUT')

        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-6">
            <div>
                <h4 class="mb-1">Wallet Settings</h4>
                <p class="mb-0">Global wallet configuration</p>
            </div>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>

        <div class="row">
            <div class="col-12">

                {{-- Wallet Status --}}
                <div class="card mb-6">
                    <div class="card-header">
                        <h5 class="mb-0">Wallet Status</h5>
                    </div>
                    <div class="card-body">
                        <input type="hidden" name="wallet_enabled" value="0">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="wallet_enabled" value="1"
                                {{ old('wallet_enabled', $walletSetting->wallet_enabled) ? 'checked' : '' }}>
                            <label class="form-check-label">Wallet Enabled</label>
                        </div>
                    </div>
                </div>

                {{-- Deposit (Topup) Settings --}}
                <div class="card mb-6">
                    <div class="card-header">
                        <h5 class="mb-0">Wallet Deposit (Top-Up)</h5>
                    </div>
                    <div class="card-body row g-4">

                        <div class="col-md-6">
                            <label class="form-label">Minimum Topup Amount</label>
                            <input type="number" step="0.01" name="min_topup_amount"
                                class="form-control"
                                value="{{ old('min_topup_amount', $walletSetting->min_topup_amount) }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Maximum Topup Amount</label>
                            <input type="number" step="0.01" name="max_topup_amount"
                                class="form-control"
                                value="{{ old('max_topup_amount', $walletSetting->max_topup_amount) }}">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Allowed Payment Gateways</label>

                            <select name="allowed_payment_gateways[]" class="form-select select2" multiple>
                                @foreach ($paymentMethods as $method)
                                    <option value="{{ $method->code }}"
                                        {{ in_array(
                                            $method->code,
                                            old('allowed_payment_gateways', $walletSetting->allowed_payment_gateways ?? [])
                                        ) ? 'selected' : '' }}>
                                        {{ $method->name }}
                                    </option>
                                @endforeach
                            </select>

                            <small class="text-muted">
                                Only enabled payment methods are available
                            </small>
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
                            <input type="number" step="0.01" name="gateway_charge_amount"
                                class="form-control"
                                value="{{ old('gateway_charge_amount', $walletSetting->gateway_charge_amount) }}">
                        </div>

                    </div>
                </div>

                {{-- Wallet Usage --}}
                <div class="card mb-6">
                    <div class="card-header">
                        <h5 class="mb-0">Wallet Usage</h5>
                    </div>
                    <div class="card-body">

                        <input type="hidden" name="partial_payment_enabled" value="0">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="partial_payment_enabled" value="1"
                                {{ old('partial_payment_enabled', $walletSetting->partial_payment_enabled) ? 'checked' : '' }}>
                            <label class="form-check-label">Allow Partial Payment</label>
                        </div>

                        <input type="hidden" name="auto_deduct_wallet_for_partial" value="0">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="auto_deduct_wallet_for_partial" value="1"
                                {{ old('auto_deduct_wallet_for_partial', $walletSetting->auto_deduct_wallet_for_partial) ? 'checked' : '' }}>
                            <label class="form-check-label">Auto Deduct Wallet Balance</label>
                        </div>

                    </div>
                </div>

                {{-- Wallet Transfer --}}
                <div class="card mb-6">
                    <div class="card-header">
                        <h5 class="mb-0">Wallet Transfer</h5>
                    </div>
                    <div class="card-body row g-4">

                        <div class="col-md-12">
                            <input type="hidden" name="wallet_transfer_enabled" value="0">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="wallet_transfer_enabled" value="1"
                                    {{ old('wallet_transfer_enabled', $walletSetting->wallet_transfer_enabled) ? 'checked' : '' }}>
                                <label class="form-check-label">Enable Wallet Transfer</label>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Minimum Transfer Amount</label>
                            <input type="number" step="0.01" name="min_transfer_amount"
                                class="form-control"
                                value="{{ old('min_transfer_amount', $walletSetting->min_transfer_amount) }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Transfer Charge Type</label>
                            <select name="transfer_charge_type" class="form-select">
                                <option value="percentage" {{ $walletSetting->transfer_charge_type === 'percentage' ? 'selected' : '' }}>Percentage</option>
                                <option value="fixed" {{ $walletSetting->transfer_charge_type === 'fixed' ? 'selected' : '' }}>Fixed</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Transfer Charge Amount</label>
                            <input type="number" step="0.01" name="transfer_charge_amount"
                                class="form-control"
                                value="{{ old('transfer_charge_amount', $walletSetting->transfer_charge_amount) }}">
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </form>
</div>
@endsection
