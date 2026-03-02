@extends('layouts.app')
@section('title', 'AI Configuration')
@include('content.settings.partials.settings-layout')

@section('content')
<div class="row">
    <div class="col-lg-3">
        @include('content.settings.partials.settings-nav')
    </div>
    <div class="col-lg-9">
        <div class="settings-header d-flex align-items-center gap-3">
            <div class="settings-header-icon"><i class="ti tabler-robot"></i></div>
            <div>
                <h4>AI Configuration</h4>
                <p>Configure AI-powered content generation</p>
            </div>
        </div>

        <form id="settingsForm">
            @csrf
            @method('PUT')

            <div class="card setting-card">
                <div class="card-header">
                    <h5><i class="ti tabler-power text-primary me-2"></i>AI Status</h5>
                </div>
                <div class="card-body">
                    <div class="setting-toggle">
                        <div class="setting-toggle-info">
                            <h6>Enable AI Features</h6>
                            <p>Allow AI-powered content generation across the platform</p>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="ai[enabled]" value="1" {{ ($settings['enabled'] ?? false) ? 'checked' : '' }}>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card setting-card">
                <div class="card-header">
                    <h5><i class="ti tabler-settings-cog text-primary me-2"></i>Provider Configuration</h5>
                    <p>Set up your AI provider credentials</p>
                </div>
                <div class="card-body row g-4">
                    <div class="col-md-6">
                        <label class="form-label">Provider</label>
                        <select name="ai[provider]" class="form-select">
                            <option value="openai" {{ ($settings['provider'] ?? '') === 'openai' ? 'selected' : '' }}>OpenAI</option>
                            <option value="anthropic" {{ ($settings['provider'] ?? '') === 'anthropic' ? 'selected' : '' }}>Anthropic</option>
                            <option value="google" {{ ($settings['provider'] ?? '') === 'google' ? 'selected' : '' }}>Google</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Model</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-brain"></i></span>
                            <input type="text" name="ai[model]" class="form-control" value="{{ $settings['model'] ?? '' }}" placeholder="gpt-4o-mini">
                        </div>
                        <div class="form-label-description">The model identifier used for content generation</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">API Key</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-key"></i></span>
                            <input type="password" name="ai[api_key]" class="form-control" value="{{ $settings['api_key'] ?? '' }}" placeholder="sk-••••••••••••••••">
                        </div>
                        <div class="form-label-description">Your secret API key from the selected provider</div>
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
saveSettings('settingsForm', '{{ route("settings.ai.update") }}');
</script>
@endpush
