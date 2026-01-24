@extends('layouts.app')

@section('title', 'Settings')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}" />
@endpush

@section('content')
<div class="app-ecommerce">


    {{-- Success Message --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4">
            <i class="bx bx-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('settings.update') }}">
        @csrf
        @method('PUT')

        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-6">
            <div>
                <h4 class="mb-1">Application Settings</h4>
                <p class="mb-0 text-muted">
                    Manage global configuration used across the entire platform
                </p>
            </div>
            <button type="submit" class="btn btn-primary">
                Save Changes
            </button>
        </div>

        <div class="row">
            <div class="col-12">

                {{-- General Settings --}}
                <div class="card mb-6">
                    <div class="card-header">
                        <div>
                            <h5 class="mb-0">General</h5>
                            <small class="text-muted">
                                Basic information that identifies your platform
                            </small>
                        </div>
                    </div>
                    <div class="card-body row g-4">

                        <div class="col-md-6">
                            <label class="form-label">Site Name</label>
                            <input type="text"
                                   name="general[site_name]"
                                   class="form-control"
                                   value="{{ old('general.site_name', $settings['general']['site_name'] ?? '') }}">
                            <small class="text-muted">
                                Displayed in headers, emails, and invoices
                            </small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Default Currency</label>
                            <select name="general[currency]" class="form-select select2">
                                <option value="">Select Currency</option>
                                @foreach ($currencies as $currency)
                                    <option value="{{ $currency->code }}"
                                        {{ old('general.currency', $settings['general']['currency'] ?? '') === $currency->code ? 'selected' : '' }}>
                                        {{ $currency->name }} ({{ $currency->code }}) {{ $currency->symbol }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">
                                Used for pricing, orders, and financial reports
                            </small>
                        </div>

                    </div>
                </div>

                {{-- UI / Branding --}}
                <div class="card mb-6">
                    <div class="card-header">
                        <div>
                            <h5 class="mb-0">UI & Branding</h5>
                            <small class="text-muted">
                                Customize the visual identity of your platform
                            </small>
                        </div>
                    </div>
                    <div class="card-body row g-4">

                        <div class="col-md-6">
                            <label class="form-label">Logo URL</label>
                            <input type="text"
                                   name="ui[logo]"
                                   class="form-control"
                                   value="{{ old('ui.logo', $settings['ui']['logo'] ?? '') }}">
                            <small class="text-muted">
                                Displayed in the header, login page, and emails
                            </small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Primary Color</label>
                            <input type="color"
                                   name="ui[primary_color]"
                                   class="form-control form-control-color"
                                   value="{{ old('ui.primary_color', $settings['ui']['primary_color'] ?? '#0d6efd') }}">
                            <small class="text-muted">
                                Used for buttons, links, and highlights
                            </small>
                        </div>

                    </div>
                </div>

                {{-- Email / SMTP Settings --}}
                <div class="card mb-6">
                    <div class="card-header">
                        <div>
                            <h5 class="mb-0">Email (SMTP) Settings</h5>
                            <small class="text-muted">
                                Configure outgoing email delivery for orders, invoices, and system notifications
                            </small>
                        </div>
                    </div>

                    <div class="card-body row g-4">

                        {{-- Mailer --}}
                        <div class="col-md-6">
                            <label class="form-label">Mailer</label>
                            <select name="email[mailer]" class="form-select">
                                @foreach (['smtp','log','array'] as $mailer)
                                    <option value="{{ $mailer }}"
                                        {{ old('email.mailer', $settings['email']['mailer'] ?? 'smtp') === $mailer ? 'selected' : '' }}>
                                        {{ strtoupper($mailer) }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">
                                Choose <strong>SMTP</strong> for real email sending. Use LOG or ARRAY for development.
                            </small>
                        </div>

                        {{-- SMTP Host --}}
                        <div class="col-md-6">
                            <label class="form-label">SMTP Host</label>
                            <input type="text"
                                name="email[host]"
                                class="form-control"
                                value="{{ old('email.host', $settings['email']['host'] ?? '') }}">
                            <small class="text-muted">
                                Mail server hostname (e.g. <code>smtp.gmail.com</code>, <code>smtp.mailtrap.io</code>)
                            </small>
                        </div>

                        {{-- SMTP Port --}}
                        <div class="col-md-6">
                            <label class="form-label">SMTP Port</label>
                            <input type="number"
                                name="email[port]"
                                class="form-control"
                                value="{{ old('email.port', $settings['email']['port'] ?? 587) }}">
                            <small class="text-muted">
                                Common ports: <strong>587</strong> (TLS), <strong>465</strong> (SSL)
                            </small>
                        </div>

                        {{-- Encryption --}}
                        <div class="col-md-6">
                            <label class="form-label">Encryption</label>
                            <select name="email[encryption]" class="form-select">
                                @foreach (['tls','ssl',null] as $enc)
                                    <option value="{{ $enc }}"
                                        {{ old('email.encryption', $settings['email']['encryption'] ?? 'tls') === $enc ? 'selected' : '' }}>
                                        {{ strtoupper($enc ?? 'NONE') }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">
                                Encryption method used by your SMTP provider
                            </small>
                        </div>

                        {{-- SMTP Username --}}
                        <div class="col-md-6">
                            <label class="form-label">SMTP Username</label>
                            <input type="text"
                                name="email[username]"
                                class="form-control"
                                value="{{ old('email.username', $settings['email']['username'] ?? '') }}">
                            <small class="text-muted">
                                Usually your email address or SMTP account username
                            </small>
                        </div>

                        {{-- SMTP Password --}}
                        <div class="col-md-6">
                            <label class="form-label">SMTP Password</label>
                            <input type="password"
                                name="email[password]"
                                class="form-control"
                                value="{{ old('email.password', $settings['email']['password'] ?? '') }}">
                            <small class="text-muted">
                                SMTP password or app password (stored securely)
                            </small>
                        </div>

                        {{-- From Email --}}
                        <div class="col-md-6">
                            <label class="form-label">From Email</label>
                            <input type="email"
                                name="email[from_address]"
                                class="form-control"
                                value="{{ old('email.from_address', $settings['email']['from_address'] ?? '') }}">
                            <small class="text-muted">
                                Email address shown as the sender (e.g. <code>support@yourdomain.com</code>)
                            </small>
                        </div>

                        {{-- From Name --}}
                        <div class="col-md-6">
                            <label class="form-label">From Name</label>
                            <input type="text"
                                name="email[from_name]"
                                class="form-control"
                                value="{{ old('email.from_name', $settings['email']['from_name'] ?? '') }}">
                            <small class="text-muted">
                                Sender name displayed in customer inbox (e.g. Your Store Name)
                            </small>
                        </div>

                    </div>
                </div>



                {{-- SEO --}}
                <div class="card mb-6">
                    <div class="card-header">
                        <div>
                            <h5 class="mb-0">SEO Settings</h5>
                            <small class="text-muted">
                                Improve search engine visibility and social sharing
                            </small>
                        </div>
                    </div>
                    <div class="card-body row g-4">

                        <div class="col-md-12">
                            <label class="form-label">Meta Title</label>
                            <input type="text"
                                   name="seo[meta_title]"
                                   class="form-control"
                                   value="{{ old('seo.meta_title', $settings['seo']['meta_title'] ?? '') }}">
                            <small class="text-muted">
                                Appears in browser tabs and search results
                            </small>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Meta Description</label>
                            <textarea name="seo[meta_description]"
                                      class="form-control"
                                      rows="3">{{ old('seo.meta_description', $settings['seo']['meta_description'] ?? '') }}</textarea>
                            <small class="text-muted">
                                Short summary shown in search engine results
                            </small>
                        </div>

                    </div>
                </div>

                {{-- Maintenance --}}
                <div class="card mb-6">
                    <div class="card-header">
                        <div>
                            <h5 class="mb-0">Maintenance Mode</h5>
                            <small class="text-muted">
                                Temporarily disable public access to the site
                            </small>
                        </div>
                    </div>
                    <div class="card-body">

                        <input type="hidden" name="general[maintenance]" value="0">
                        <div class="form-check form-switch">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="general[maintenance]"
                                   value="1"
                                   {{ old('general.maintenance', $settings['general']['maintenance'] ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label">
                                Enable Maintenance Mode
                            </label>
                        </div>
                        <small class="text-muted d-block mt-1">
                            Only administrators will be able to access the site
                        </small>

                    </div>
                </div>

            </div>
        </div>
    </form>
</div>
@endsection
