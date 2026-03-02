@extends('layouts.app')
@section('title', 'Invoice Settings')
@include('content.settings.partials.settings-layout')

@section('content')
<div class="row">
    <div class="col-lg-3">
        @include('content.settings.partials.settings-nav')
    </div>
    <div class="col-lg-9">
        <div class="settings-header d-flex align-items-center gap-3">
            <div class="settings-header-icon"><i class="ti tabler-file-invoice"></i></div>
            <div>
                <h4>Invoice Settings</h4>
                <p>Configure invoice generation and company details</p>
            </div>
        </div>

        <form id="settingsForm">
            @csrf
            @method('PUT')

            <div class="card setting-card">
                <div class="card-header">
                    <h5><i class="ti tabler-settings text-primary me-2"></i>Invoice Configuration</h5>
                    <p>Set up invoice numbering and generation preferences</p>
                </div>
                <div class="card-body row g-4">
                    <div class="col-md-6">
                        <label class="form-label">Invoice Prefix</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-hash"></i></span>
                            <input type="text" name="invoice[prefix]" class="form-control" value="{{ $settings['prefix'] ?? 'INV-' }}" placeholder="INV-">
                        </div>
                        <div class="form-label-description">Prefix added before invoice numbers (e.g. INV-00001)</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Auto-Generate Invoices</label>
                        <select name="invoice[auto_generate]" class="form-select">
                            <option value="1" {{ ($settings['auto_generate'] ?? '1') == '1' ? 'selected' : '' }}>Enabled</option>
                            <option value="0" {{ ($settings['auto_generate'] ?? '1') == '0' ? 'selected' : '' }}>Disabled</option>
                        </select>
                        <div class="form-label-description">Automatically generate invoices when orders are completed</div>
                    </div>
                </div>
            </div>

            <div class="card setting-card">
                <div class="card-header">
                    <h5><i class="ti tabler-building text-primary me-2"></i>Company Information</h5>
                    <p>Details displayed on generated invoices</p>
                </div>
                <div class="card-body row g-4">
                    <div class="col-md-6">
                        <label class="form-label">Company Name</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-building"></i></span>
                            <input type="text" name="invoice[company_name]" class="form-control" value="{{ $settings['company_name'] ?? '' }}" placeholder="Your Company LLC">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tax / VAT Number</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-receipt-tax"></i></span>
                            <input type="text" name="invoice[tax_number]" class="form-control" value="{{ $settings['tax_number'] ?? '' }}" placeholder="XX-1234567890">
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Company Address</label>
                        <textarea name="invoice[company_address]" class="form-control" rows="3" placeholder="123 Business St, Suite 100&#10;City, State ZIP&#10;Country">{{ $settings['company_address'] ?? '' }}</textarea>
                    </div>
                </div>
            </div>

            <div class="card setting-card">
                <div class="card-header">
                    <h5><i class="ti tabler-notes text-primary me-2"></i>Invoice Footer</h5>
                    <p>Custom note displayed at the bottom of every invoice</p>
                </div>
                <div class="card-body">
                    <div>
                        <label class="form-label">Footer Note</label>
                        <textarea name="invoice[footer_note]" class="form-control" rows="4" placeholder="Thank you for your purchase! For any questions, contact support@yourstore.com">{{ $settings['footer_note'] ?? '' }}</textarea>
                        <div class="form-label-description">This text appears at the bottom of all generated invoices</div>
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
saveSettings('settingsForm', '{{ route("settings.invoice.update") }}');
</script>
@endpush
