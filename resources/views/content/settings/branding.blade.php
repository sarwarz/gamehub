@extends('layouts.app')
@section('title', 'Branding')
@include('content.settings.partials.settings-layout')

@push('page-css')
<style>
/* Branding upload zones — premium drag-and-drop */
.branding-upload-zone {
    position: relative;
    border: 2px dashed #d9dee3;
    border-radius: 0.75rem;
    padding: 1.75rem 1.25rem;
    text-align: center;
    cursor: pointer;
    transition: border-color 0.2s ease, background-color 0.2s ease, box-shadow 0.2s ease;
    height: 180px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #fafbfc;
}
.branding-upload-zone:hover {
    border-color: #7367f0;
    background: rgba(115, 103, 240, 0.04);
    box-shadow: 0 2px 8px rgba(115, 103, 240, 0.08);
}
.branding-upload-zone.dragover {
    border-color: #7367f0;
    background: rgba(115, 103, 240, 0.08);
}
.branding-upload-zone input[type="file"] {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
}
.branding-upload-zone .upload-placeholder {
    pointer-events: none;
}
.branding-upload-zone .upload-placeholder .upload-icon {
    width: 48px;
    height: 48px;
    margin: 0 auto 0.75rem;
    border-radius: 0.75rem;
    background: rgba(115, 103, 240, 0.1);
    color: #7367f0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}
.branding-upload-zone .upload-placeholder .upload-text {
    font-size: 0.875rem;
    font-weight: 500;
    color: #566a7f;
    margin-bottom: 0.25rem;
}
.branding-upload-zone .upload-placeholder .upload-hint {
    font-size: 0.75rem;
    color: #a1acb8;
    line-height: 1.35;
}
.branding-upload-zone .upload-preview {
    position: relative;
    max-width: 100%;
}
.branding-upload-zone .upload-preview img {
    max-height: 100px;
    max-width: 100%;
    object-fit: contain;
    border-radius: 0.5rem;
}
.branding-upload-zone .btn-remove-preview {
    position: absolute;
    top: -0.5rem;
    right: -0.5rem;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #ff4d4f;
    color: #fff;
    border: 2px solid #fff;
    box-shadow: 0 2px 6px rgba(0,0,0,0.15);
    cursor: pointer;
    transition: transform 0.15s, background 0.15s;
}
.branding-upload-zone .btn-remove-preview:hover {
    background: #cf1322;
    transform: scale(1.05);
}

/* Color scheme — inline picker + hex */
.branding-color-row {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}
.branding-color-swatch-wrap {
    flex-shrink: 0;
    position: relative;
}
.branding-color-swatch-wrap input[type="color"] {
    width: 48px;
    height: 48px;
    padding: 4px;
    border: 2px solid #e7e7ed;
    border-radius: 0.5rem;
    cursor: pointer;
    background: #fff;
}
.branding-color-swatch-wrap input[type="color"]::-webkit-color-swatch-wrapper { padding: 2px; }
.branding-color-swatch-wrap input[type="color"]::-webkit-color-swatch { border-radius: 0.25rem; }
.branding-color-hex-wrap {
    flex: 1;
    min-width: 120px;
}
.branding-color-hex-wrap input {
    font-family: ui-monospace, monospace;
    font-size: 0.875rem;
}
.branding-color-desc {
    width: 100%;
    font-size: 0.75rem;
    color: #a1acb8;
    margin-top: 0.375rem;
}

/* Footer textarea */
.branding-footer-textarea {
    border-radius: 0.5rem;
    border: 1px solid #e7e7ed;
    resize: vertical;
    min-height: 100px;
}
.branding-footer-textarea:focus {
    border-color: #7367f0;
    box-shadow: 0 0 0 3px rgba(115, 103, 240, 0.15);
}
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-lg-3">
        @include('content.settings.partials.settings-nav')
    </div>
    <div class="col-lg-9">
        <div class="settings-header d-flex align-items-center gap-3">
            <div class="settings-header-icon"><i class="ti tabler-palette"></i></div>
            <div>
                <h4>Branding</h4>
                <p>Customize your platform's visual identity</p>
            </div>
        </div>

        <form id="settingsForm" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="card setting-card">
                <div class="card-header">
                    <h5><i class="ti tabler-photo text-primary me-2"></i>Logo & Favicon</h5>
                    <p class="mb-0">Upload your logo and favicon. Images will be used across the site and in browser tabs.</p>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        @php
                            $brandingImages = [
                                'logo'      => ['label' => 'Site Logo',  'hint' => 'PNG, SVG or JPG — Recommended: 200×60px', 'icon' => 'tabler-photo'],
                                'logo_dark' => ['label' => 'Dark Logo',  'hint' => 'Used on dark backgrounds — Same dimensions as logo', 'icon' => 'tabler-moon'],
                                'favicon'   => ['label' => 'Favicon',    'hint' => 'ICO or PNG — 32×32 or 64×64px', 'icon' => 'tabler-app-window'],
                            ];
                        @endphp
                        @foreach($brandingImages as $field => $meta)
                            @php $currentVal = $settings[$field] ?? ''; @endphp
                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-body mb-2">{{ $meta['label'] }}</label>
                                <div class="branding-upload-zone" id="zone-{{ $field }}" data-field="{{ $field }}">
                                    <input type="file" name="branding_file_{{ $field }}" id="input-{{ $field }}" accept="image/*">
                                    <div class="upload-preview {{ $currentVal ? '' : 'd-none' }}" id="preview-area-{{ $field }}">
                                        <img id="preview-img-{{ $field }}" src="{{ $currentVal ? asset($currentVal) : '' }}" alt="{{ $meta['label'] }}">
                                        <button type="button" class="btn-remove-preview" aria-label="Remove" onclick="event.stopPropagation(); clearPreview('{{ $field }}')">
                                            <i class="ti tabler-x ti-xs"></i>
                                        </button>
                                    </div>
                                    <div class="upload-placeholder {{ $currentVal ? 'd-none' : '' }}" id="placeholder-{{ $field }}">
                                        <div class="upload-icon"><i class="ti {{ $meta['icon'] }}"></i></div>
                                        <div class="upload-text">Drop image or click to upload</div>
                                        <div class="upload-hint">{{ $meta['hint'] }}</div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card setting-card">
                <div class="card-header">
                    <h5><i class="ti tabler-droplet text-primary me-2"></i>Color Scheme</h5>
                    <p class="mb-0">Set primary and secondary colors used for buttons, links, and UI elements.</p>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        @php
                            $colors = [
                                'primary_color'   => ['label' => 'Primary Color',   'default' => '#7367f0', 'desc' => 'Buttons, links, active states'],
                                'secondary_color' => ['label' => 'Secondary Color', 'default' => '#a8aaae', 'desc' => 'Muted elements, borders'],
                            ];
                        @endphp
                        @foreach($colors as $colorKey => $colorMeta)
                            @php $colorVal = $settings[$colorKey] ?? $colorMeta['default']; @endphp
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-body mb-2">{{ $colorMeta['label'] }}</label>
                                <div class="branding-color-row">
                                    <div class="branding-color-swatch-wrap">
                                        <input type="color" class="color-swatch" id="swatch-{{ $colorKey }}" value="{{ $colorVal }}" data-target="hex-{{ $colorKey }}" aria-label="{{ $colorMeta['label'] }}">
                                    </div>
                                    <div class="branding-color-hex-wrap">
                                        <input type="text" class="form-control color-hex-input" id="hex-{{ $colorKey }}" name="branding[{{ $colorKey }}]" value="{{ $colorVal }}" maxlength="7" data-target="swatch-{{ $colorKey }}" placeholder="#000000">
                                        <div class="branding-color-desc">{{ $colorMeta['desc'] }}</div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card setting-card">
                <div class="card-header">
                    <h5><i class="ti tabler-text-caption text-primary me-2"></i>Footer</h5>
                    <p class="mb-0">Text shown at the bottom of every page (e.g. copyright, legal notice).</p>
                </div>
                <div class="card-body">
                    <label class="form-label fw-semibold text-body">Footer Text</label>
                    <textarea name="branding[footer_text]" class="form-control branding-footer-textarea" rows="3" placeholder="© 2026 YourStore. All rights reserved.">{{ $settings['footer_text'] ?? '' }}</textarea>
                    <div class="form-label-description mt-1">Displayed at the bottom of every page</div>
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
saveSettings('settingsForm', '{{ route("settings.branding.update") }}');

['logo', 'logo_dark', 'favicon'].forEach(name => {
    const input = document.getElementById('input-' + name);
    const zone = document.getElementById('zone-' + name);
    if (!input || !zone) return;

    zone.addEventListener('click', function(e) {
        if (!e.target.closest('.btn-remove-preview')) input.click();
    });

    input.addEventListener('change', function() {
        if (this.files && this.files[0]) showPreview(name, this.files[0]);
    });

    ['dragenter', 'dragover'].forEach(evt => {
        zone.addEventListener(evt, e => { e.preventDefault(); e.stopPropagation(); zone.classList.add('dragover'); });
    });
    ['dragleave', 'drop'].forEach(evt => {
        zone.addEventListener(evt, e => {
            e.preventDefault();
            e.stopPropagation();
            zone.classList.remove('dragover');
            if (evt === 'drop' && e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0]) {
                input.files = e.dataTransfer.files;
                showPreview(name, e.dataTransfer.files[0]);
            }
        });
    });
});

function showPreview(name, file) {
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('preview-img-' + name).src = e.target.result;
        document.getElementById('preview-area-' + name).classList.remove('d-none');
        document.getElementById('placeholder-' + name).classList.add('d-none');
    };
    reader.readAsDataURL(file);
}

document.querySelectorAll('.color-swatch').forEach(swatch => {
    swatch.addEventListener('input', function() {
        const hex = document.getElementById(this.dataset.target);
        if (hex) hex.value = this.value;
    });
});

document.querySelectorAll('.color-hex-input').forEach(input => {
    input.addEventListener('input', function() {
        let val = this.value;
        if (val && !val.startsWith('#')) val = '#' + val;
        const swatch = document.getElementById(this.dataset.target);
        if (swatch && /^#[0-9A-Fa-f]{6}$/.test(val)) swatch.value = val;
    });
    input.addEventListener('blur', function() {
        let val = (this.value || '').replace(/[^0-9A-Fa-f#]/g, '');
        if (val && !val.startsWith('#')) val = '#' + val;
        if (!/^#[0-9A-Fa-f]{6}$/.test(val)) {
            const swatch = document.getElementById(this.dataset.target);
            if (swatch) val = swatch.value;
        }
        this.value = val || '#7367f0';
    });
});

window.clearPreview = function(name) {
    document.getElementById('input-' + name).value = '';
    document.getElementById('preview-img-' + name).src = '';
    document.getElementById('preview-area-' + name).classList.add('d-none');
    document.getElementById('placeholder-' + name).classList.remove('d-none');
};
</script>
@endpush
