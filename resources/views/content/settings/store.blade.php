@extends('layouts.app')
@section('title', 'Store & Commerce Settings')
@include('content.settings.partials.settings-layout')

@section('content')
<div class="row">
    <div class="col-lg-3">
        @include('content.settings.partials.settings-nav')
    </div>
    <div class="col-lg-9">
        <div class="settings-header d-flex align-items-center gap-3">
            <div class="settings-header-icon"><i class="ti tabler-building-store"></i></div>
            <div>
                <h4>Store & Commerce</h4>
                <p>Configure store behavior, taxes, and order handling</p>
            </div>
        </div>

        <form id="settingsForm">
            @csrf
            @method('PUT')

            <div class="card setting-card">
                <div class="card-header">
                    <h5>Order Settings</h5>
                    <p>Configure order numbering, limits, and auto-cancellation</p>
                </div>
                <div class="card-body row g-4">
                    <div class="col-md-4">
                        <label class="form-label">Order Number Prefix</label>
                        <input type="text" name="store[order_prefix]" class="form-control" value="{{ $settings['order_prefix'] ?? 'ORD-' }}" placeholder="ORD-">
                        <div class="form-label-description">e.g. ORD-00001</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Minimum Order Amount</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-currency-dollar"></i></span>
                            <input type="number" step="0.01" name="store[min_order_amount]" class="form-control" value="{{ $settings['min_order_amount'] ?? 0 }}" min="0">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Auto-cancel Unpaid After (hours)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-clock"></i></span>
                            <input type="number" name="store[auto_cancel_hours]" class="form-control" value="{{ $settings['auto_cancel_hours'] ?? 24 }}" min="1">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card setting-card">
                <div class="card-header">
                    <h5>Tax Configuration</h5>
                    <p>Enable and configure tax display for your store</p>
                </div>
                <div class="card-body row g-4">
                    <div class="col-md-6">
                        <label class="form-label">Enable Tax</label>
                        <select name="store[tax_enabled]" class="form-select">
                            <option value="1" {{ ($settings['tax_enabled'] ?? '1') == '1' ? 'selected' : '' }}>Enabled</option>
                            <option value="0" {{ ($settings['tax_enabled'] ?? '1') == '0' ? 'selected' : '' }}>Disabled</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tax Display</label>
                        <select name="store[tax_display]" class="form-select">
                            <option value="exclusive" {{ ($settings['tax_display'] ?? 'exclusive') === 'exclusive' ? 'selected' : '' }}>Exclusive (tax added at checkout)</option>
                            <option value="inclusive" {{ ($settings['tax_display'] ?? 'exclusive') === 'inclusive' ? 'selected' : '' }}>Inclusive (tax included in price)</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="card setting-card">
                <div class="card-header">
                    <h5>Stock Management</h5>
                    <p>Control stock alerts and visibility for out-of-stock products</p>
                </div>
                <div class="card-body">
                    <div class="row g-4 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Low Stock Threshold</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="ti tabler-alert-triangle"></i></span>
                                <input type="number" name="store[low_stock_threshold]" class="form-control" value="{{ $settings['low_stock_threshold'] ?? 5 }}" min="1">
                            </div>
                            <div class="form-label-description">Alert when keys drop below this number</div>
                        </div>
                    </div>
                    <div class="setting-toggle">
                        <div class="setting-toggle-info">
                            <h6>Show Out-of-Stock Products</h6>
                            <p>Display products in catalog even when stock is zero</p>
                        </div>
                        <div class="form-check form-switch">
                            <input type="hidden" name="store[show_out_of_stock]" value="0">
                            <input class="form-check-input" type="checkbox" name="store[show_out_of_stock]" value="1" {{ ($settings['show_out_of_stock'] ?? false) ? 'checked' : '' }}>
                        </div>
                    </div>
                    <div class="setting-toggle">
                        <div class="setting-toggle-info">
                            <h6>Guest Checkout</h6>
                            <p>Allow customers to purchase without creating an account</p>
                        </div>
                        <div class="form-check form-switch">
                            <input type="hidden" name="store[guest_checkout]" value="0">
                            <input class="form-check-input" type="checkbox" name="store[guest_checkout]" value="1" {{ ($settings['guest_checkout'] ?? false) ? 'checked' : '' }}>
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
saveSettings('settingsForm', '{{ route("settings.store.update") }}');
</script>
@endpush
