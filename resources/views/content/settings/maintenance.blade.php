@extends('layouts.app')
@section('title', 'Maintenance Mode')
@include('content.settings.partials.settings-layout')

@section('content')
<div class="row">
    <div class="col-lg-3">
        @include('content.settings.partials.settings-nav')
    </div>
    <div class="col-lg-9">
        <div class="settings-header d-flex align-items-center gap-3">
            <div class="settings-header-icon"><i class="ti tabler-tool"></i></div>
            <div>
                <h4>Maintenance Mode</h4>
                <p>Control maintenance mode and display messages to visitors</p>
            </div>
        </div>

        <form id="settingsForm">
            @csrf
            @method('PUT')

            <div class="card setting-card">
                <div class="card-header">
                    <h5><i class="ti tabler-power text-primary me-2"></i>Maintenance Status</h5>
                </div>
                <div class="card-body">
                    @if($settings['enabled'] ?? false)
                    <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
                        <i class="ti tabler-alert-triangle me-2 fs-5"></i>
                        <div>Your site is currently <strong>in maintenance mode</strong>. Only allowed IPs can access the frontend.</div>
                    </div>
                    @endif
                    <div class="setting-toggle">
                        <div class="setting-toggle-info">
                            <h6>Enable Maintenance Mode</h6>
                            <p>When enabled, visitors will see a maintenance page instead of your site</p>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <span class="badge bg-label-{{ ($settings['enabled'] ?? false) ? 'danger' : 'success' }}">
                                {{ ($settings['enabled'] ?? false) ? 'Maintenance ON' : 'Site Live' }}
                            </span>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="maintenance[enabled]" value="1" {{ ($settings['enabled'] ?? false) ? 'checked' : '' }}>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card setting-card">
                <div class="card-header">
                    <h5><i class="ti tabler-message-dots text-primary me-2"></i>Maintenance Message</h5>
                    <p>The message displayed to visitors during maintenance</p>
                </div>
                <div class="card-body">
                    <label class="form-label">Message</label>
                    <textarea name="maintenance[message]" class="form-control" rows="4" placeholder="We're currently performing scheduled maintenance. We'll be back shortly!">{{ $settings['message'] ?? '' }}</textarea>
                    <div class="form-label-description">Supports plain text. Shown on the maintenance page.</div>
                </div>
            </div>

            <div class="card setting-card">
                <div class="card-header">
                    <h5><i class="ti tabler-lock-access text-primary me-2"></i>Access Control</h5>
                    <p>Control who can bypass maintenance mode</p>
                </div>
                <div class="card-body row g-4">
                    <div class="col-12">
                        <label class="form-label">Allowed IP Addresses</label>
                        <textarea name="maintenance[allowed_ips]" class="form-control" rows="4" placeholder="Enter one IP address per line&#10;e.g.&#10;192.168.1.1&#10;10.0.0.1">{{ is_array($settings['allowed_ips'] ?? null) ? implode("\n", $settings['allowed_ips']) : ($settings['allowed_ips'] ?? '') }}</textarea>
                        <div class="form-label-description">One IP per line. These IPs can still access the site during maintenance.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Expected Back</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-calendar-time"></i></span>
                            <input type="text" id="expectedBackPicker" name="maintenance[expected_back]" class="form-control" placeholder="Select date & time" value="{{ $settings['expected_back'] ?? '' }}">
                        </div>
                        <div class="form-label-description">Optionally show visitors when the site will return</div>
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
flatpickr('#expectedBackPicker', {
    enableTime: true,
    dateFormat: 'Y-m-d H:i',
    altInput: true,
    altFormat: 'M j, Y h:i K',
    time_24hr: false,
    minDate: 'today',
});

saveSettings('settingsForm', '{{ route("settings.maintenance.update") }}', {reload: true});
</script>
@endpush
