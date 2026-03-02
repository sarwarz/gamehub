@extends('layouts.app')
@section('title', 'Currency & Localization')
@include('content.settings.partials.settings-layout')

@section('content')
<div class="row">
    <div class="col-lg-3">
        @include('content.settings.partials.settings-nav')
    </div>
    <div class="col-lg-9">
        <div class="settings-header d-flex align-items-center gap-3">
            <div class="settings-header-icon"><i class="ti tabler-currency-dollar"></i></div>
            <div>
                <h4>Currency & Localization</h4>
                <p>Configure number formatting, currency display, and language options</p>
            </div>
        </div>

        <form id="settingsForm">
            @csrf
            @method('PUT')

            <div class="card setting-card">
                <div class="card-header">
                    <h5><i class="ti tabler-123 text-primary me-2"></i>Number Formatting</h5>
                    <p>Define how numbers and decimals are displayed across the platform</p>
                </div>
                <div class="card-body row g-4">
                    <div class="col-md-6">
                        <label class="form-label">Number Format</label>
                        <select name="currency_locale[number_format]" class="form-select">
                            @foreach([
                                'en-US' => 'English (US) — 1,234.56',
                                'de-DE' => 'German — 1.234,56',
                                'fr-FR' => 'French — 1 234,56',
                                'ja-JP' => 'Japanese — 1,234.56',
                                'ar-SA' => 'Arabic (SA) — ١٬٢٣٤٫٥٦',
                                'hi-IN' => 'Hindi (IN) — 1,23,456.78',
                                'pt-BR' => 'Portuguese (BR) — 1.234,56',
                                'zh-CN' => 'Chinese (CN) — 1,234.56',
                            ] as $code => $label)
                                <option value="{{ $code }}" {{ ($settings['number_format'] ?? 'en-US') === $code ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Decimal Places</label>
                        <input type="number" name="currency_locale[decimal_places]" class="form-control" value="{{ $settings['decimal_places'] ?? 2 }}" min="0" max="4">
                        <div class="form-label-description">Number of digits after the decimal point (0–4)</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Decimal Separator</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-point"></i></span>
                            <input type="text" name="currency_locale[decimal_separator]" class="form-control" value="{{ $settings['decimal_separator'] ?? '.' }}" maxlength="1" placeholder=".">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Thousands Separator</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-separator"></i></span>
                            <input type="text" name="currency_locale[thousands_separator]" class="form-control" value="{{ $settings['thousands_separator'] ?? ',' }}" maxlength="1" placeholder=",">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card setting-card">
                <div class="card-header">
                    <h5><i class="ti tabler-currency-dollar text-primary me-2"></i>Currency Display</h5>
                    <p>Configure how currency symbols are positioned relative to amounts</p>
                </div>
                <div class="card-body row g-4">
                    <div class="col-md-6">
                        <label class="form-label">Currency Symbol Position</label>
                        <select name="currency_locale[currency_position]" class="form-select">
                            <option value="before" {{ ($settings['currency_position'] ?? 'before') === 'before' ? 'selected' : '' }}>Before amount — $100.00</option>
                            <option value="after" {{ ($settings['currency_position'] ?? 'before') === 'after' ? 'selected' : '' }}>After amount — 100.00$</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="card setting-card">
                <div class="card-header">
                    <h5><i class="ti tabler-language text-primary me-2"></i>Language & Direction</h5>
                    <p>Configure language and text direction preferences</p>
                </div>
                <div class="card-body">
                    <div class="setting-toggle">
                        <div class="setting-toggle-info">
                            <h6>RTL Layout</h6>
                            <p>Enable right-to-left text direction for Arabic, Hebrew, and similar languages</p>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="currency_locale[rtl_enabled]" value="1" {{ ($settings['rtl_enabled'] ?? false) ? 'checked' : '' }}>
                        </div>
                    </div>
                    <div class="setting-toggle mb-3">
                        <div class="setting-toggle-info">
                            <h6>Multi-Language Support</h6>
                            <p>Allow the platform to serve content in multiple languages</p>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="currency_locale[multi_language_enabled]" value="1" {{ ($settings['multi_language_enabled'] ?? false) ? 'checked' : '' }}>
                        </div>
                    </div>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label">Default Language</label>
                            <select name="currency_locale[default_language]" class="form-select">
                                @foreach([
                                    'en' => 'English',
                                    'ar' => 'Arabic',
                                    'fr' => 'French',
                                    'de' => 'German',
                                    'es' => 'Spanish',
                                    'pt' => 'Portuguese',
                                    'zh' => 'Chinese',
                                    'ja' => 'Japanese',
                                    'ko' => 'Korean',
                                    'hi' => 'Hindi',
                                    'tr' => 'Turkish',
                                    'ru' => 'Russian',
                                ] as $code => $label)
                                    <option value="{{ $code }}" {{ ($settings['default_language'] ?? 'en') === $code ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
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
saveSettings('settingsForm', '{{ route("settings.currency.update") }}');
</script>
@endpush
