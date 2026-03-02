@extends('layouts.app')
@section('title', 'Security Settings')
@include('content.settings.partials.settings-layout')

@section('content')
<div class="row">
    <div class="col-lg-3">
        @include('content.settings.partials.settings-nav')
    </div>
    <div class="col-lg-9">
        <div class="settings-header d-flex align-items-center gap-3">
            <div class="settings-header-icon"><i class="ti tabler-shield-lock"></i></div>
            <div>
                <h4>Security Settings</h4>
                <p>Configure authentication and access control policies</p>
            </div>
        </div>

        <form id="settingsForm">
            @csrf
            @method('PUT')

            <div class="card setting-card">
                <div class="card-header">
                    <h5><i class="ti tabler-auth-2fa text-primary me-2"></i>Two-Factor Authentication</h5>
                </div>
                <div class="card-body">
                    <div class="setting-toggle">
                        <div class="setting-toggle-info">
                            <h6>Enable Two-Factor Authentication</h6>
                            <p>Require users to verify their identity with a second factor during login</p>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="security[two_factor_enabled]" value="1" {{ ($settings['two_factor_enabled'] ?? false) ? 'checked' : '' }}>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card setting-card">
                <div class="card-header">
                    <h5><i class="ti tabler-clock text-primary me-2"></i>Session Management</h5>
                </div>
                <div class="card-body row g-4">
                    <div class="col-md-6">
                        <label class="form-label">Session Timeout (minutes)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-clock-pause"></i></span>
                            <input type="number" name="security[session_timeout_minutes]" class="form-control" value="{{ $settings['session_timeout_minutes'] ?? 120 }}" min="1">
                        </div>
                        <div class="form-label-description">Automatically log out inactive users after this period</div>
                    </div>
                </div>
            </div>

            <div class="card setting-card">
                <div class="card-header">
                    <h5><i class="ti tabler-lock text-primary me-2"></i>Password Policy</h5>
                </div>
                <div class="card-body">
                    <div class="row g-4 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Minimum Password Length</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="ti tabler-ruler-2"></i></span>
                                <input type="number" name="security[password_min_length]" class="form-control" value="{{ $settings['password_min_length'] ?? 8 }}" min="4" max="128">
                            </div>
                        </div>
                    </div>
                    <div class="setting-toggle">
                        <div class="setting-toggle-info">
                            <h6>Require Uppercase Letter</h6>
                            <p>Password must contain at least one uppercase character (A–Z)</p>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="security[password_require_uppercase]" value="1" {{ ($settings['password_require_uppercase'] ?? false) ? 'checked' : '' }}>
                        </div>
                    </div>
                    <div class="setting-toggle">
                        <div class="setting-toggle-info">
                            <h6>Require Number</h6>
                            <p>Password must contain at least one numeric digit (0–9)</p>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="security[password_require_number]" value="1" {{ ($settings['password_require_number'] ?? false) ? 'checked' : '' }}>
                        </div>
                    </div>
                    <div class="setting-toggle">
                        <div class="setting-toggle-info">
                            <h6>Require Symbol</h6>
                            <p>Password must contain at least one special character (!@#$%...)</p>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="security[password_require_symbol]" value="1" {{ ($settings['password_require_symbol'] ?? false) ? 'checked' : '' }}>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card setting-card">
                <div class="card-header">
                    <h5><i class="ti tabler-ban text-primary me-2"></i>Login Protection</h5>
                </div>
                <div class="card-body row g-4">
                    <div class="col-md-6">
                        <label class="form-label">Max Login Attempts</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-alert-triangle"></i></span>
                            <input type="number" name="security[max_login_attempts]" class="form-control" value="{{ $settings['max_login_attempts'] ?? 5 }}" min="1">
                        </div>
                        <div class="form-label-description">Lock account after this many failed attempts</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Lockout Duration (minutes)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-clock-x"></i></span>
                            <input type="number" name="security[lockout_duration_minutes]" class="form-control" value="{{ $settings['lockout_duration_minutes'] ?? 15 }}" min="1">
                        </div>
                        <div class="form-label-description">How long the account stays locked after exceeding attempts</div>
                    </div>
                </div>
            </div>

            <div class="card setting-card">
                <div class="card-header">
                    <h5><i class="ti tabler-network text-primary me-2"></i>IP Restrictions</h5>
                </div>
                <div class="card-body">
                    <div class="setting-toggle mb-3">
                        <div class="setting-toggle-info">
                            <h6>Enable IP Whitelist</h6>
                            <p>Only allow admin access from specific IP addresses</p>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="security[ip_whitelist_enabled]" value="1" {{ ($settings['ip_whitelist_enabled'] ?? false) ? 'checked' : '' }}>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Whitelisted IPs</label>
                        <textarea name="security[ip_whitelist]" class="form-control" rows="4" placeholder="Enter one IP address per line&#10;e.g.&#10;192.168.1.1&#10;10.0.0.1">{{ is_array($settings['ip_whitelist'] ?? null) ? implode("\n", $settings['ip_whitelist']) : ($settings['ip_whitelist'] ?? '') }}</textarea>
                        <div class="form-label-description">One IP address per line. Only applies when IP whitelist is enabled.</div>
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
saveSettings('settingsForm', '{{ route("settings.security.update") }}');
</script>
@endpush
