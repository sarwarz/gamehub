@extends('layouts.app')
@section('title', 'Registration Settings')
@include('content.settings.partials.settings-layout')

@section('content')
<div class="row">
    <div class="col-lg-3">
        @include('content.settings.partials.settings-nav')
    </div>
    <div class="col-lg-9">
        <div class="settings-header d-flex align-items-center gap-3">
            <div class="settings-header-icon"><i class="ti tabler-user-plus"></i></div>
            <div>
                <h4>Registration Settings</h4>
                <p>Control how users can register and join your platform</p>
            </div>
        </div>

        <form id="settingsForm">
            @csrf
            @method('PUT')

            <div class="card setting-card">
                <div class="card-header">
                    <h5><i class="ti tabler-toggle-right text-primary me-2"></i>Registration Control</h5>
                </div>
                <div class="card-body">
                    <div class="setting-toggle">
                        <div class="setting-toggle-info">
                            <h6>Enable Registration</h6>
                            <p>Allow new users to create accounts on your platform</p>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="registration[registration_enabled]" value="1" {{ ($settings['registration_enabled'] ?? false) ? 'checked' : '' }}>
                        </div>
                    </div>
                    <div class="setting-toggle">
                        <div class="setting-toggle-info">
                            <h6>Welcome Email</h6>
                            <p>Send a welcome email to new users after successful registration</p>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="registration[welcome_email_enabled]" value="1" {{ ($settings['welcome_email_enabled'] ?? false) ? 'checked' : '' }}>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card setting-card">
                <div class="card-header">
                    <h5><i class="ti tabler-circle-check text-primary me-2"></i>Verification</h5>
                </div>
                <div class="card-body">
                    <div class="setting-toggle">
                        <div class="setting-toggle-info">
                            <h6>Require Email Verification</h6>
                            <p>Users must verify their email address before accessing the platform</p>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="registration[require_email_verification]" value="1" {{ ($settings['require_email_verification'] ?? false) ? 'checked' : '' }}>
                        </div>
                    </div>
                    <div class="setting-toggle">
                        <div class="setting-toggle-info">
                            <h6>Require Phone Number</h6>
                            <p>Users must provide a phone number during registration</p>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="registration[require_phone_number]" value="1" {{ ($settings['require_phone_number'] ?? false) ? 'checked' : '' }}>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card setting-card">
                <div class="card-header">
                    <h5><i class="ti tabler-brand-google text-primary me-2"></i>Social Login</h5>
                </div>
                <div class="card-body">
                    <div class="setting-toggle mb-3">
                        <div class="setting-toggle-info">
                            <h6>Allow Social Login</h6>
                            <p>Let users sign in using their social media accounts</p>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="registration[allow_social_login]" value="1" {{ ($settings['allow_social_login'] ?? false) ? 'checked' : '' }}>
                        </div>
                    </div>
                    <div>
                        <label class="form-label fw-medium">Social Providers</label>
                        <div class="form-label-description mb-3">Select which social login providers to enable</div>
                        @php $activeProviders = (array) ($settings['social_providers'] ?? []); @endphp
                        <div class="row g-3">
                            @foreach([
                                'google'   => ['label' => 'Google',   'icon' => 'tabler-brand-google'],
                                'github'   => ['label' => 'GitHub',   'icon' => 'tabler-brand-github'],
                                'facebook' => ['label' => 'Facebook', 'icon' => 'tabler-brand-facebook'],
                                'twitter'  => ['label' => 'Twitter',  'icon' => 'tabler-brand-twitter'],
                            ] as $provider => $meta)
                                <div class="col-md-3 col-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="registration[social_providers][]" value="{{ $provider }}" id="provider-{{ $provider }}" {{ in_array($provider, $activeProviders) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="provider-{{ $provider }}">
                                            <i class="ti {{ $meta['icon'] }} me-1"></i> {{ $meta['label'] }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="card setting-card">
                <div class="card-header">
                    <h5><i class="ti tabler-user-cog text-primary me-2"></i>User Defaults</h5>
                </div>
                <div class="card-body row g-4">
                    <div class="col-md-6">
                        <label class="form-label">Minimum Age Required</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-calendar-event"></i></span>
                            <input type="number" name="registration[min_age_required]" class="form-control" value="{{ $settings['min_age_required'] ?? 0 }}" min="0">
                        </div>
                        <div class="form-label-description">Set to 0 to disable age verification</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Auto-Assign Role</label>
                        <select name="registration[auto_assign_role]" class="form-select">
                            <option value="user" {{ ($settings['auto_assign_role'] ?? 'user') === 'user' ? 'selected' : '' }}>User</option>
                            <option value="customer" {{ ($settings['auto_assign_role'] ?? 'user') === 'customer' ? 'selected' : '' }}>Customer</option>
                        </select>
                        <div class="form-label-description">Default role assigned to newly registered users</div>
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
saveSettings('settingsForm', '{{ route("settings.registration.update") }}');
</script>
@endpush
