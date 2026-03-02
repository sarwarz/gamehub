@extends('layouts.app')
@section('title', 'Checkout Settings')
@include('content.settings.partials.settings-layout')

@section('content')
<div class="row">
    <div class="col-lg-3">
        @include('content.settings.partials.settings-nav')
    </div>
    <div class="col-lg-9">
        <div class="settings-header d-flex align-items-center gap-3">
            <div class="settings-header-icon"><i class="ti tabler-shopping-cart"></i></div>
            <div>
                <h4>Checkout Settings</h4>
                <p>Configure checkout flow, session timeouts, and payment behavior</p>
            </div>
        </div>

        <form id="settingsForm">
            @csrf
            @method('PUT')

            <div class="card setting-card">
                <div class="card-header">
                    <h5>Session Configuration</h5>
                    <p>Manage checkout session timeouts and key reservations</p>
                </div>
                <div class="card-body row g-4">
                    <div class="col-md-4">
                        <label class="form-label">Session Timeout (minutes)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-clock"></i></span>
                            <input type="number" name="checkout[session_timeout_minutes]" class="form-control" value="{{ $settings['session_timeout_minutes'] ?? 30 }}" min="5">
                        </div>
                        <div class="form-label-description">How long a checkout session stays active</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Key Reservation (minutes)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-key"></i></span>
                            <input type="number" name="checkout[key_reservation_minutes]" class="form-control" value="{{ $settings['key_reservation_minutes'] ?? 15 }}" min="1">
                        </div>
                        <div class="form-label-description">Duration keys are held during checkout</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Abandoned Cleanup (minutes)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-trash"></i></span>
                            <input type="number" name="checkout[abandoned_cleanup_minutes]" class="form-control" value="{{ $settings['abandoned_cleanup_minutes'] ?? 60 }}" min="10">
                        </div>
                        <div class="form-label-description">After this time, abandoned sessions are purged</div>
                    </div>
                </div>
            </div>

            <div class="card setting-card">
                <div class="card-header">
                    <h5>Order Limits</h5>
                    <p>Set maximum items and payment retry constraints</p>
                </div>
                <div class="card-body row g-4">
                    <div class="col-md-6">
                        <label class="form-label">Max Items Per Order</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-stack-2"></i></span>
                            <input type="number" name="checkout[max_items_per_order]" class="form-control" value="{{ $settings['max_items_per_order'] ?? 10 }}" min="1">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Payment Retry Limit</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-refresh"></i></span>
                            <input type="number" name="checkout[payment_retry_limit]" class="form-control" value="{{ $settings['payment_retry_limit'] ?? 3 }}" min="1">
                        </div>
                        <div class="form-label-description">Maximum payment attempts before order is locked</div>
                    </div>
                </div>
            </div>

            <div class="card setting-card">
                <div class="card-header">
                    <h5>Checkout Options</h5>
                    <p>Toggle checkout requirements and guest access</p>
                </div>
                <div class="card-body">
                    <div class="setting-toggle">
                        <div class="setting-toggle-info">
                            <h6>Require Billing Address</h6>
                            <p>Customers must provide a billing address during checkout</p>
                        </div>
                        <div class="form-check form-switch">
                            <input type="hidden" name="checkout[require_billing_address]" value="0">
                            <input class="form-check-input" type="checkbox" name="checkout[require_billing_address]" value="1" {{ ($settings['require_billing_address'] ?? false) ? 'checked' : '' }}>
                        </div>
                    </div>
                    <div class="setting-toggle">
                        <div class="setting-toggle-info">
                            <h6>Guest Checkout</h6>
                            <p>Allow users to complete purchases without registering an account</p>
                        </div>
                        <div class="form-check form-switch">
                            <input type="hidden" name="checkout[guest_checkout_enabled]" value="0">
                            <input class="form-check-input" type="checkbox" name="checkout[guest_checkout_enabled]" value="1" {{ ($settings['guest_checkout_enabled'] ?? false) ? 'checked' : '' }}>
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
saveSettings('settingsForm', '{{ route("settings.checkout.update") }}');
</script>
@endpush
