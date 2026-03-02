@extends('layouts.app')
@section('title', 'API & Integrations')
@include('content.settings.partials.settings-layout')

@section('content')
<div class="row">
    <div class="col-lg-3">
        @include('content.settings.partials.settings-nav')
    </div>
    <div class="col-lg-9">
        <div class="settings-header d-flex align-items-center gap-3">
            <div class="settings-header-icon"><i class="ti tabler-api"></i></div>
            <div>
                <h4>API & Integrations</h4>
                <p>Configure API limits, webhooks, and third-party integrations</p>
            </div>
        </div>

        <form id="settingsForm">
            @csrf
            @method('PUT')

            <div class="card setting-card">
                <div class="card-header">
                    <h5><i class="ti tabler-gauge text-primary me-2"></i>API Rate Limiting</h5>
                    <p>Control how many API requests are allowed</p>
                </div>
                <div class="card-body row g-4">
                    <div class="col-md-6">
                        <label class="form-label">Requests Per Minute</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-clock"></i></span>
                            <input type="number" name="api_integration[api_rate_limit_per_minute]" class="form-control" value="{{ $settings['api_rate_limit_per_minute'] ?? 60 }}" min="1">
                        </div>
                        <div class="form-label-description">Maximum number of API requests allowed per minute per user</div>
                    </div>
                </div>
            </div>

            <div class="card setting-card">
                <div class="card-header">
                    <h5><i class="ti tabler-webhook text-primary me-2"></i>Webhook Configuration</h5>
                    <p>Settings for outgoing webhook delivery</p>
                </div>
                <div class="card-body row g-4">
                    <div class="col-md-6">
                        <label class="form-label">Retry Count</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-repeat"></i></span>
                            <input type="number" name="api_integration[webhook_retry_count]" class="form-control" value="{{ $settings['webhook_retry_count'] ?? 3 }}" min="0" max="10">
                        </div>
                        <div class="form-label-description">Number of times to retry a failed webhook delivery</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Retry Delay (seconds)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-clock-pause"></i></span>
                            <input type="number" name="api_integration[webhook_retry_delay_seconds]" class="form-control" value="{{ $settings['webhook_retry_delay_seconds'] ?? 30 }}" min="1">
                        </div>
                        <div class="form-label-description">Seconds to wait between retry attempts</div>
                    </div>
                </div>
            </div>

            <div class="card setting-card">
                <div class="card-header">
                    <h5><i class="ti tabler-shield-check text-primary me-2"></i>CAPTCHA Protection</h5>
                    <p>Protect forms from spam and abuse using Google reCAPTCHA or Cloudflare Turnstile</p>
                </div>
                <div class="card-body">
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">CAPTCHA Provider</label>
                            <select name="api_integration[captcha_provider]" id="captcha_provider" class="form-select">
                                <option value="none" {{ ($settings['captcha_provider'] ?? 'none') === 'none' ? 'selected' : '' }}>Disabled</option>
                                <option value="recaptcha" {{ ($settings['captcha_provider'] ?? 'none') === 'recaptcha' ? 'selected' : '' }}>Google reCAPTCHA v3</option>
                                <option value="turnstile" {{ ($settings['captcha_provider'] ?? 'none') === 'turnstile' ? 'selected' : '' }}>Cloudflare Turnstile</option>
                            </select>
                            <div class="form-label-description">Select which CAPTCHA service to use for form protection</div>
                        </div>
                    </div>

                    {{-- Google reCAPTCHA Section --}}
                    <div id="recaptcha_settings" class="border rounded p-3 mb-4" style="display: none;">
                        <h6 class="mb-3"><i class="ti tabler-brand-google text-danger me-2"></i>Google reCAPTCHA v3</h6>
                        <input type="hidden" name="api_integration[google_recaptcha_enabled]" value="0">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label">Site Key</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ti tabler-key"></i></span>
                                    <input type="text" name="api_integration[google_recaptcha_site_key]" class="form-control" value="{{ $settings['google_recaptcha_site_key'] ?? '' }}" placeholder="6LeIxAcTAAAAAJcZV...">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Secret Key</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ti tabler-lock"></i></span>
                                    <input type="password" name="api_integration[google_recaptcha_secret_key]" class="form-control" value="{{ $settings['google_recaptcha_secret_key'] ?? '' }}" placeholder="6LeIxAcTAAAAAGG-v...">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Cloudflare Turnstile Section --}}
                    <div id="turnstile_settings" class="border rounded p-3 mb-4" style="display: none;">
                        <h6 class="mb-3"><i class="ti tabler-cloud-lock text-warning me-2"></i>Cloudflare Turnstile</h6>
                        <input type="hidden" name="api_integration[turnstile_enabled]" value="0">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label">Site Key</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ti tabler-key"></i></span>
                                    <input type="text" name="api_integration[turnstile_site_key]" class="form-control" value="{{ $settings['turnstile_site_key'] ?? '' }}" placeholder="0x4AAAAAAA...">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Secret Key</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ti tabler-lock"></i></span>
                                    <input type="password" name="api_integration[turnstile_secret_key]" class="form-control" value="{{ $settings['turnstile_secret_key'] ?? '' }}" placeholder="0x4AAAAAAA...">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card setting-card">
                <div class="card-header">
                    <h5><i class="ti tabler-chart-dots text-primary me-2"></i>Analytics & Tracking</h5>
                    <p>Third-party analytics and pixel tracking</p>
                </div>
                <div class="card-body row g-4">
                    <div class="col-md-6">
                        <label class="form-label">Google Analytics ID</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-brand-google"></i></span>
                            <input type="text" name="api_integration[google_analytics_id]" class="form-control" value="{{ $settings['google_analytics_id'] ?? '' }}" placeholder="G-XXXXXXXXXX">
                        </div>
                        <div class="form-label-description">Your Google Analytics 4 measurement ID</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Facebook Pixel ID</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-brand-facebook"></i></span>
                            <input type="text" name="api_integration[facebook_pixel_id]" class="form-control" value="{{ $settings['facebook_pixel_id'] ?? '' }}" placeholder="123456789012345">
                        </div>
                        <div class="form-label-description">Used for Facebook/Meta ad conversion tracking</div>
                    </div>
                </div>
            </div>

            <div class="card setting-card">
                <div class="card-header">
                    <h5><i class="ti tabler-code text-primary me-2"></i>Custom Scripts</h5>
                    <p>Inject custom code into your site</p>
                </div>
                <div class="card-body row g-4">
                    <div class="col-12">
                        <label class="form-label">Custom Head Scripts</label>
                        <textarea name="api_integration[custom_head_scripts]" class="form-control" rows="4" style="font-family: monospace; font-size: 0.8125rem;" placeholder="<!-- Scripts injected before </head> -->">{{ $settings['custom_head_scripts'] ?? '' }}</textarea>
                        <div class="form-label-description">Code injected before the closing &lt;/head&gt; tag</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Custom Body Scripts</label>
                        <textarea name="api_integration[custom_body_scripts]" class="form-control" rows="4" style="font-family: monospace; font-size: 0.8125rem;" placeholder="<!-- Scripts injected before </body> -->">{{ $settings['custom_body_scripts'] ?? '' }}</textarea>
                        <div class="form-label-description">Code injected before the closing &lt;/body&gt; tag</div>
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
(function() {
    const provider = document.getElementById('captcha_provider');
    const recaptchaBox = document.getElementById('recaptcha_settings');
    const turnstileBox = document.getElementById('turnstile_settings');

    function toggleCaptcha() {
        const val = provider.value;
        recaptchaBox.style.display = val === 'recaptcha' ? 'block' : 'none';
        turnstileBox.style.display = val === 'turnstile' ? 'block' : 'none';
    }

    provider.addEventListener('change', toggleCaptcha);
    toggleCaptcha();

    function syncCaptchaHiddenFields() {
        const val = provider.value;
        document.querySelector('input[name="api_integration[google_recaptcha_enabled]"]').value = val === 'recaptcha' ? '1' : '0';
        document.querySelector('input[name="api_integration[turnstile_enabled]"]').value = val === 'turnstile' ? '1' : '0';
    }
    provider.addEventListener('change', syncCaptchaHiddenFields);
    syncCaptchaHiddenFields();

    saveSettings('settingsForm', '{{ route("settings.api-integrations.update") }}');
})();
</script>
@endpush
