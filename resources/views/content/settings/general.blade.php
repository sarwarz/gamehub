@extends('layouts.app')
@section('title', 'General Settings')
@include('content.settings.partials.settings-layout')

@section('content')
<div class="row">
    <div class="col-lg-3">
        @include('content.settings.partials.settings-nav')
    </div>
    <div class="col-lg-9">
        <div class="settings-header d-flex align-items-center gap-3">
            <div class="settings-header-icon"><i class="ti tabler-adjustments-horizontal"></i></div>
            <div>
                <h4>General Settings</h4>
                <p>Configure your platform's basic information and preferences</p>
            </div>
        </div>

        <form id="settingsForm">
            @csrf
            @method('PUT')

            <div class="card setting-card">
                <div class="card-header">
                    <h5><i class="ti tabler-building text-primary me-2"></i>Site Information</h5>
                </div>
                <div class="card-body row g-4">
                    <div class="col-md-6">
                        <label class="form-label">Site Name <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-building"></i></span>
                            <input type="text" name="general[site_name]" class="form-control" value="{{ $settings['site_name'] ?? '' }}" placeholder="Your Platform Name">
                        </div>
                        <div class="form-label-description">Displayed in header, emails, and invoices</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tagline</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-quote"></i></span>
                            <input type="text" name="general[tagline]" class="form-control" value="{{ $settings['tagline'] ?? '' }}" placeholder="Your one-stop digital marketplace">
                        </div>
                        <div class="form-label-description">Short description shown in header and footer</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Contact Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-mail"></i></span>
                            <input type="email" name="general[contact_email]" class="form-control" value="{{ $settings['contact_email'] ?? '' }}" placeholder="support@yourstore.com">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Contact Phone</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-phone"></i></span>
                            <input type="tel" name="general[contact_phone]" class="form-control" value="{{ $settings['contact_phone'] ?? '' }}" placeholder="+1 234 567 890">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card setting-card">
                <div class="card-header">
                    <h5><i class="ti tabler-world text-primary me-2"></i>Regional Settings</h5>
                </div>
                <div class="card-body row g-4">
                    <div class="col-md-6">
                        <label class="form-label">Currency</label>
                        <select name="general[currency]" class="form-select">
                            <option value="">Select Currency</option>
                            @foreach ($currencies as $c)
                                <option value="{{ $c->code }}" {{ ($settings['currency'] ?? '') === $c->code ? 'selected' : '' }}>
                                    {{ $c->name }} ({{ $c->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Timezone</label>
                        <select name="general[timezone]" class="form-select">
                            @php
                                $commonTimezones = [
                                    'UTC', 'America/New_York', 'America/Chicago', 'America/Denver',
                                    'America/Los_Angeles', 'America/Toronto', 'America/Sao_Paulo',
                                    'Europe/London', 'Europe/Paris', 'Europe/Berlin', 'Europe/Moscow',
                                    'Asia/Dubai', 'Asia/Kolkata', 'Asia/Shanghai', 'Asia/Tokyo',
                                    'Asia/Singapore', 'Asia/Seoul', 'Australia/Sydney',
                                    'Pacific/Auckland', 'Africa/Cairo', 'Africa/Lagos',
                                ];
                            @endphp
                            @foreach($commonTimezones as $tz)
                                <option value="{{ $tz }}" {{ ($settings['timezone'] ?? 'UTC') === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Date Format</label>
                        <select name="general[date_format]" class="form-select">
                            @foreach([
                                'M d, Y' => 'Jan 15, 2026 (M d, Y)',
                                'd M Y'  => '15 Jan 2026 (d M Y)',
                                'Y-m-d'  => '2026-01-15 (Y-m-d)',
                                'm/d/Y'  => '01/15/2026 (m/d/Y)',
                            ] as $fmt => $label)
                                <option value="{{ $fmt }}" {{ ($settings['date_format'] ?? 'M d, Y') === $fmt ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Items Per Page</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-list-numbers"></i></span>
                            <input type="number" name="general[per_page]" class="form-control" value="{{ $settings['per_page'] ?? 12 }}" min="1" max="100">
                        </div>
                        <div class="form-label-description">Number of items shown per page in listings</div>
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
saveSettings('settingsForm', '{{ route("settings.general.update") }}');
</script>
@endpush
