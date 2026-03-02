@extends('layouts.app')
@section('title', 'Email / SMTP Settings')
@include('content.settings.partials.settings-layout')

@section('content')
<div class="row">
    <div class="col-lg-3">
        @include('content.settings.partials.settings-nav')
    </div>
    <div class="col-lg-9">
        <div class="settings-header d-flex align-items-center gap-3">
            <div class="settings-header-icon"><i class="ti tabler-mail-cog"></i></div>
            <div>
                <h4>Email / SMTP</h4>
                <p>Configure email delivery service and credentials</p>
            </div>
        </div>

        <form id="settingsForm">
            @csrf
            @method('PUT')

            <div class="card setting-card">
                <div class="card-header">
                    <h5><i class="ti tabler-mail-forward text-primary me-2"></i>Mail Driver</h5>
                    <p>Select the email delivery service to use</p>
                </div>
                <div class="card-body row g-4">
                    <div class="col-md-6">
                        <label class="form-label">Mailer</label>
                        <select name="email[mailer]" class="form-select">
                            @foreach([
                                'smtp'     => 'SMTP',
                                'sendmail' => 'Sendmail',
                                'mailgun'  => 'Mailgun',
                                'ses'      => 'Amazon SES',
                                'postmark' => 'Postmark',
                            ] as $value => $label)
                                <option value="{{ $value }}" {{ ($settings['mailer'] ?? 'smtp') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="card setting-card">
                <div class="card-header">
                    <h5><i class="ti tabler-server text-primary me-2"></i>SMTP Configuration</h5>
                    <p>Server connection details for SMTP delivery</p>
                </div>
                <div class="card-body row g-4">
                    <div class="col-md-6">
                        <label class="form-label">SMTP Host</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-server"></i></span>
                            <input type="text" name="email[host]" class="form-control" value="{{ $settings['host'] ?? '' }}" placeholder="smtp.example.com">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Port</label>
                        <input type="number" name="email[port]" class="form-control" value="{{ $settings['port'] ?? 587 }}" placeholder="587">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Timeout (seconds)</label>
                        <input type="number" name="email[timeout]" class="form-control" value="{{ $settings['timeout'] ?? 30 }}" min="5" max="120">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Encryption</label>
                        <select name="email[encryption]" class="form-select">
                            <option value="tls" {{ ($settings['encryption'] ?? 'tls') === 'tls' ? 'selected' : '' }}>TLS</option>
                            <option value="ssl" {{ ($settings['encryption'] ?? 'tls') === 'ssl' ? 'selected' : '' }}>SSL</option>
                            <option value="none" {{ ($settings['encryption'] ?? 'tls') === 'none' ? 'selected' : '' }}>None</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="card setting-card">
                <div class="card-header">
                    <h5><i class="ti tabler-key text-primary me-2"></i>Credentials</h5>
                    <p>Authentication credentials for the mail server</p>
                </div>
                <div class="card-body row g-4">
                    <div class="col-md-6">
                        <label class="form-label">Username</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-user"></i></span>
                            <input type="text" name="email[username]" class="form-control" value="{{ $settings['username'] ?? '' }}" placeholder="your@email.com">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-lock"></i></span>
                            <input type="password" name="email[password]" class="form-control" value="{{ $settings['password'] ?? '' }}" placeholder="••••••••">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card setting-card">
                <div class="card-header">
                    <h5><i class="ti tabler-mail text-primary me-2"></i>Sender Information</h5>
                    <p>Default "From" address and name for outgoing emails</p>
                </div>
                <div class="card-body row g-4">
                    <div class="col-md-6">
                        <label class="form-label">From Address</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-at"></i></span>
                            <input type="email" name="email[from_address]" class="form-control" value="{{ $settings['from_address'] ?? '' }}" placeholder="noreply@yourstore.com">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">From Name</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-id"></i></span>
                            <input type="text" name="email[from_name]" class="form-control" value="{{ $settings['from_name'] ?? '' }}" placeholder="Your Platform Name">
                        </div>
                    </div>
                    <div class="col-12">
                        <button type="button" class="btn btn-label-info" disabled>
                            <i class="ti tabler-send me-1"></i> Send Test Email
                        </button>
                        <span class="text-muted ms-2 small">Save settings first, then test delivery</span>
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
saveSettings('settingsForm', '{{ route("settings.email.update") }}');
</script>
@endpush
