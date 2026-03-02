@extends('layouts.app')
@section('title', 'Refund & Escrow Settings')
@include('content.settings.partials.settings-layout')

@section('content')
<div class="row">
    <div class="col-lg-3">
        @include('content.settings.partials.settings-nav')
    </div>
    <div class="col-lg-9">
        <div class="settings-header d-flex align-items-center gap-3">
            <div class="settings-header-icon"><i class="ti tabler-receipt-refund"></i></div>
            <div>
                <h4>Refund & Escrow</h4>
                <p>Configure buyer protection, refund policies, and seller escrow</p>
            </div>
        </div>

        <form id="settingsForm">
            @csrf
            @method('PUT')

            <div class="card setting-card">
                <div class="card-header">
                    <h5>Refund Policy</h5>
                    <p>Set refund eligibility windows and partial refund options</p>
                </div>
                <div class="card-body">
                    <div class="setting-toggle">
                        <div class="setting-toggle-info">
                            <h6>Enable Refunds</h6>
                            <p>Allow customers to request refunds on eligible orders</p>
                        </div>
                        <div class="form-check form-switch">
                            <input type="hidden" name="refund_escrow[refund_enabled]" value="0">
                            <input class="form-check-input" type="checkbox" name="refund_escrow[refund_enabled]" value="1" {{ ($settings['refund_enabled'] ?? false) ? 'checked' : '' }}>
                        </div>
                    </div>
                    <div class="row g-4 mt-1">
                        <div class="col-md-6">
                            <label class="form-label">Auto-refund Window (hours)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="ti tabler-clock"></i></span>
                                <input type="number" name="refund_escrow[auto_refund_window_hours]" class="form-control" value="{{ $settings['auto_refund_window_hours'] ?? 48 }}" min="0">
                            </div>
                            <div class="form-label-description">Refunds within this window are automatically approved</div>
                        </div>
                    </div>
                    <div class="setting-toggle mt-3">
                        <div class="setting-toggle-info">
                            <h6>Partial Refunds</h6>
                            <p>Allow partial refunds instead of full order refunds only</p>
                        </div>
                        <div class="form-check form-switch">
                            <input type="hidden" name="refund_escrow[partial_refund_enabled]" value="0">
                            <input class="form-check-input" type="checkbox" name="refund_escrow[partial_refund_enabled]" value="1" {{ ($settings['partial_refund_enabled'] ?? false) ? 'checked' : '' }}>
                        </div>
                    </div>
                    <div class="row g-4 mt-1">
                        <div class="col-md-6">
                            <label class="form-label">Max Refund Percentage</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="ti tabler-percentage"></i></span>
                                <input type="number" step="1" name="refund_escrow[max_refund_percentage]" class="form-control" value="{{ $settings['max_refund_percentage'] ?? 100 }}" min="1" max="100">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card setting-card">
                <div class="card-header">
                    <h5>Refund Methods</h5>
                    <p>Choose how refunds are issued to customers</p>
                </div>
                <div class="card-body">
                    <div class="setting-toggle">
                        <div class="setting-toggle-info">
                            <h6>Refund to Wallet</h6>
                            <p>Issue refunds as wallet credit for future purchases</p>
                        </div>
                        <div class="form-check form-switch">
                            <input type="hidden" name="refund_escrow[refund_to_wallet_enabled]" value="0">
                            <input class="form-check-input" type="checkbox" name="refund_escrow[refund_to_wallet_enabled]" value="1" {{ ($settings['refund_to_wallet_enabled'] ?? false) ? 'checked' : '' }}>
                        </div>
                    </div>
                    <div class="setting-toggle">
                        <div class="setting-toggle-info">
                            <h6>Refund to Original Payment</h6>
                            <p>Refund back to the original payment method used at checkout</p>
                        </div>
                        <div class="form-check form-switch">
                            <input type="hidden" name="refund_escrow[refund_to_original_enabled]" value="0">
                            <input class="form-check-input" type="checkbox" name="refund_escrow[refund_to_original_enabled]" value="1" {{ ($settings['refund_to_original_enabled'] ?? false) ? 'checked' : '' }}>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card setting-card">
                <div class="card-header">
                    <h5>Escrow Protection</h5>
                    <p>Hold seller earnings to protect buyers during the dispute window</p>
                </div>
                <div class="card-body row g-4">
                    <div class="col-md-6">
                        <label class="form-label">Escrow Period (days)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-shield-lock"></i></span>
                            <input type="number" name="refund_escrow[escrow_period_days]" class="form-control" value="{{ $settings['escrow_period_days'] ?? 7 }}" min="0">
                        </div>
                        <div class="form-label-description">Number of days seller earnings are held before release</div>
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
saveSettings('settingsForm', '{{ route("settings.refund-escrow.update") }}');
</script>
@endpush
