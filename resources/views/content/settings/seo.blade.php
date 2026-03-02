@extends('layouts.app')
@section('title', 'SEO Settings')
@include('content.settings.partials.settings-layout')

@section('content')
<div class="row">
    <div class="col-lg-3">
        @include('content.settings.partials.settings-nav')
    </div>
    <div class="col-lg-9">
        <div class="settings-header d-flex align-items-center gap-3">
            <div class="settings-header-icon"><i class="ti tabler-search"></i></div>
            <div>
                <h4>SEO Settings</h4>
                <p>Configure search engine optimization and analytics</p>
            </div>
        </div>

        <form id="settingsForm">
            @csrf
            @method('PUT')

            <div class="card setting-card">
                <div class="card-header">
                    <h5><i class="ti tabler-tag text-primary me-2"></i>Meta Tags</h5>
                    <p>Default meta tags applied across your site</p>
                </div>
                <div class="card-body row g-4">
                    <div class="col-12">
                        <label class="form-label">Meta Title</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-heading"></i></span>
                            <input type="text" name="seo[meta_title]" class="form-control" value="{{ $settings['meta_title'] ?? '' }}" placeholder="Your Site — Best Digital Game Keys">
                        </div>
                        <div class="form-label-description">Displayed in browser tabs and search engine results</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Meta Description</label>
                        <textarea name="seo[meta_description]" class="form-control" rows="3" placeholder="A brief description of your website for search engines...">{{ $settings['meta_description'] ?? '' }}</textarea>
                        <div class="form-label-description">Recommended length: 150–160 characters</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Meta Keywords</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-tags"></i></span>
                            <input type="text" name="seo[meta_keywords]" class="form-control" value="{{ $settings['meta_keywords'] ?? '' }}" placeholder="game keys, digital games, steam keys, pc games">
                        </div>
                        <div class="form-label-description">Comma-separated keywords relevant to your site</div>
                    </div>
                </div>
            </div>

            <div class="card setting-card">
                <div class="card-header">
                    <h5><i class="ti tabler-share text-primary me-2"></i>Open Graph</h5>
                    <p>Social media sharing preview settings</p>
                </div>
                <div class="card-body row g-4">
                    <div class="col-12">
                        <label class="form-label">OG Image URL</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-photo"></i></span>
                            <input type="text" name="seo[og_image]" class="form-control" value="{{ $settings['og_image'] ?? '' }}" placeholder="https://yoursite.com/images/og-image.jpg">
                        </div>
                        <div class="form-label-description">Image shown when your site is shared on social media (recommended: 1200×630 px)</div>
                    </div>
                </div>
            </div>

            <div class="card setting-card">
                <div class="card-header">
                    <h5><i class="ti tabler-chart-bar text-primary me-2"></i>Analytics & Scripts</h5>
                    <p>Tracking codes and custom head scripts</p>
                </div>
                <div class="card-body row g-4">
                    <div class="col-12">
                        <label class="form-label">Google Analytics ID</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-brand-google"></i></span>
                            <input type="text" name="seo[google_analytics]" class="form-control" value="{{ $settings['google_analytics'] ?? '' }}" placeholder="G-XXXXXXXXXX">
                        </div>
                        <div class="form-label-description">Your Google Analytics 4 measurement ID</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Custom Head Scripts</label>
                        <textarea name="seo[head_scripts]" class="form-control" rows="5" style="font-family: monospace; font-size: 0.8125rem;" placeholder="<!-- Paste tracking scripts, meta tags, or other code here -->">{{ $settings['head_scripts'] ?? '' }}</textarea>
                        <div class="form-label-description">Code injected before the closing &lt;/head&gt; tag on every page</div>
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
saveSettings('settingsForm', '{{ route("settings.seo.update") }}');
</script>
@endpush
