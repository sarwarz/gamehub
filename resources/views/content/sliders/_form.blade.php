@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/flatpickr/flatpickr.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}">
<style>
.slider-preview {
    position: relative; border-radius: 12px; overflow: hidden;
    height: 220px; background: #1a1a2e; transition: all .3s;
}
.slider-preview img { width: 100%; height: 100%; object-fit: cover; }
.slider-preview .overlay {
    position: absolute; inset: 0; display: flex; flex-direction: column; justify-content: center; padding: 1.5rem;
}
.slider-preview .overlay.pos-center { align-items: center; text-align: center; }
.slider-preview .overlay.pos-right { align-items: flex-end; text-align: right; }
.slider-preview .overlay.pos-left { align-items: flex-start; text-align: left; }
.slider-preview .preview-badge {
    font-size: .65rem; padding: 2px 8px; border-radius: 4px; color: #fff;
    display: inline-block; margin-bottom: .5rem; font-weight: 600;
}
.slider-preview .preview-title { font-size: 1.2rem; font-weight: 700; margin: 0; }
.slider-preview .preview-subtitle { font-size: .8rem; opacity: .85; margin: 4px 0 0; }
.slider-preview .preview-btn {
    display: inline-block; margin-top: .75rem; padding: 6px 18px;
    border-radius: 6px; font-size: .75rem; font-weight: 600; text-decoration: none;
}
.slider-preview .text-light-mode { color: #fff; }
.slider-preview .text-light-mode .preview-btn { background: #fff; color: #333; }
.slider-preview .text-dark-mode { color: #1a1a2e; }
.slider-preview .text-dark-mode .preview-btn { background: #1a1a2e; color: #fff; }
.upload-zone {
    border: 2px dashed var(--bs-border-color); border-radius: 12px;
    padding: 2rem; text-align: center; cursor: pointer; transition: all .2s;
    background: var(--bs-body-bg);
}
.upload-zone:hover, .upload-zone.dragover { border-color: #7367f0; background: rgba(115,103,240,.04); }
.upload-zone img { max-height: 140px; border-radius: 8px; object-fit: cover; }
.type-card { cursor: pointer; border: 2px solid transparent; border-radius: 8px; padding: .75rem; transition: all .2s; text-align: center; }
.type-card:hover { border-color: rgba(115,103,240,.3); }
.type-card.selected { border-color: #7367f0; background: rgba(115,103,240,.06); }
.type-card i { font-size: 1.5rem; display: block; margin-bottom: .25rem; }
.type-card small { font-size: .7rem; }
.color-swatch {
    width: 28px; height: 28px; border-radius: 6px; border: 2px solid transparent;
    cursor: pointer; transition: all .15s; display: inline-block;
}
.color-swatch:hover, .color-swatch.active { border-color: #333; transform: scale(1.15); }
</style>
@endpush

@php $s = $slider ?? null; @endphp

<div class="row">
    {{-- LEFT: Content --}}
    <div class="col-lg-8">

        {{-- Slider Type --}}
        <div class="card mb-4">
            <div class="card-header"><h6 class="mb-0"><i class="ti tabler-layout me-2"></i>Slider Type</h6></div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach(['hero' => ['tabler-crown', 'Hero', 'Full-width hero banner'], 'banner' => ['tabler-ad-2', 'Banner', 'Standard promotional'], 'promotional' => ['tabler-discount-2', 'Promo', 'Sale / discount'], 'product_spotlight' => ['tabler-sparkles', 'Spotlight', 'Feature a product']] as $type => [$icon, $label, $desc])
                    <div class="col-6 col-md-3">
                        <label class="type-card d-block {{ old('type', $s?->type ?? 'hero') === $type ? 'selected' : '' }}">
                            <input type="radio" name="type" value="{{ $type }}" class="d-none type-radio"
                                {{ old('type', $s?->type ?? 'hero') === $type ? 'checked' : '' }}>
                            <i class="ti {{ $icon }} text-primary"></i>
                            <strong class="d-block">{{ $label }}</strong>
                            <small class="text-muted">{{ $desc }}</small>
                        </label>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Content --}}
        <div class="card mb-4">
            <div class="card-header"><h6 class="mb-0"><i class="ti tabler-text-size me-2"></i>Content</h6></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" id="inp-title" class="form-control"
                               value="{{ old('title', $s?->title) }}" placeholder="Headline text">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Subtitle</label>
                        <input type="text" name="subtitle" id="inp-subtitle" class="form-control"
                               value="{{ old('subtitle', $s?->subtitle) }}" placeholder="Supporting text">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Badge Text</label>
                        <input type="text" name="badge_text" id="inp-badge" class="form-control"
                               value="{{ old('badge_text', $s?->badge_text) }}" placeholder="NEW, SALE..." maxlength="50">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Badge Color</label>
                        <div class="d-flex gap-2 align-items-center flex-wrap">
                            @foreach(['#7367f0','#28c76f','#ff9f43','#ea5455','#00cfe8','#1a1a2e'] as $c)
                            <span class="color-swatch badge-color-pick {{ old('badge_color', $s?->badge_color) === $c ? 'active' : '' }}"
                                  style="background:{{ $c }}" data-color="{{ $c }}"></span>
                            @endforeach
                            <input type="hidden" name="badge_color" id="inp-badge-color"
                                   value="{{ old('badge_color', $s?->badge_color ?? '#7367f0') }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Linked Product</label>
                        <select name="product_id" id="inp-product" class="form-select select2">
                            <option value="">— No Product —</option>
                            @foreach($products as $p)
                            <option value="{{ $p->id }}" {{ old('product_id', $s?->product_id) == $p->id ? 'selected' : '' }}>
                                {{ $p->title }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- CTA --}}
        <div class="card mb-4">
            <div class="card-header"><h6 class="mb-0"><i class="ti tabler-click me-2"></i>Call to Action</h6></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Button Text</label>
                        <input type="text" name="button_text" id="inp-btn-text" class="form-control"
                               value="{{ old('button_text', $s?->button_text) }}" placeholder="Shop Now" maxlength="50">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Button URL</label>
                        <input type="url" name="button_url" class="form-control"
                               value="{{ old('button_url', $s?->button_url) }}" placeholder="Auto-filled from product if empty">
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- RIGHT: Settings --}}
    <div class="col-lg-4">

        {{-- Live Preview --}}
        <div class="card mb-4">
            <div class="card-header"><h6 class="mb-0"><i class="ti tabler-eye me-2"></i>Live Preview</h6></div>
            <div class="card-body p-3">
                <div class="slider-preview" id="live-preview">
                    <img src="{{ $s ? $s->image_url : '' }}" id="preview-img" alt=""
                         style="{{ $s ? '' : 'display:none' }}">
                    <div class="overlay pos-{{ old('text_position', $s?->text_position ?? 'left') }}"
                         id="preview-overlay"
                         style="background:{{ old('overlay_color', $s?->overlay_color ?? 'rgba(0,0,0,0.4)') }}">
                        <div class="text-{{ old('text_color', $s?->text_color ?? 'light') }}-mode" id="preview-text-wrap">
                            <span class="preview-badge" id="preview-badge"
                                  style="background:{{ old('badge_color', $s?->badge_color ?? '#7367f0') }};{{ $s?->badge_text ? '' : 'display:none' }}">
                                {{ old('badge_text', $s?->badge_text) }}
                            </span>
                            <h4 class="preview-title" id="preview-title">{{ old('title', $s?->display_title ?? 'Slider Title') }}</h4>
                            <p class="preview-subtitle" id="preview-subtitle">{{ old('subtitle', $s?->display_subtitle ?? 'Subtitle text goes here') }}</p>
                            <span class="preview-btn" id="preview-btn">{{ old('button_text', $s?->button_text ?? 'Shop Now') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Image Upload --}}
        <div class="card mb-4">
            <div class="card-header"><h6 class="mb-0"><i class="ti tabler-photo me-2"></i>Slider Image</h6></div>
            <div class="card-body">
                <div class="upload-zone" id="upload-zone">
                    <i class="ti tabler-cloud-upload d-block mb-2" style="font-size:2rem;opacity:.5"></i>
                    <p class="mb-1 fw-medium">Drop image here or click to upload</p>
                    <small class="text-muted">Recommended: 1920x600px, Max 5MB</small>
                    <input type="file" name="image" id="inp-image" class="d-none" accept="image/*"
                           {{ $s ? '' : 'required' }}>
                </div>
            </div>
        </div>

        {{-- Appearance --}}
        <div class="card mb-4">
            <div class="card-header"><h6 class="mb-0"><i class="ti tabler-palette me-2"></i>Appearance</h6></div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Overlay Color</label>
                    <input type="text" name="overlay_color" id="inp-overlay" class="form-control"
                           value="{{ old('overlay_color', $s?->overlay_color ?? 'rgba(0,0,0,0.4)') }}"
                           placeholder="rgba(0,0,0,0.4)">
                </div>
                <div class="mb-3">
                    <label class="form-label">Text Color</label>
                    <div class="d-flex gap-2">
                        @foreach(['light' => 'Light (White)', 'dark' => 'Dark (Black)'] as $val => $label)
                        <label class="btn btn-sm {{ old('text_color', $s?->text_color ?? 'light') === $val ? 'btn-primary' : 'btn-outline-secondary' }} flex-fill text-center text-color-btn">
                            <input type="radio" name="text_color" value="{{ $val }}" class="d-none"
                                {{ old('text_color', $s?->text_color ?? 'light') === $val ? 'checked' : '' }}>
                            {{ $label }}
                        </label>
                        @endforeach
                    </div>
                </div>
                <div class="mb-0">
                    <label class="form-label">Text Position</label>
                    <div class="d-flex gap-2">
                        @foreach(['left' => 'Left', 'center' => 'Center', 'right' => 'Right'] as $val => $label)
                        <label class="btn btn-sm {{ old('text_position', $s?->text_position ?? 'left') === $val ? 'btn-primary' : 'btn-outline-secondary' }} flex-fill text-center pos-btn">
                            <input type="radio" name="text_position" value="{{ $val }}" class="d-none"
                                {{ old('text_position', $s?->text_position ?? 'left') === $val ? 'checked' : '' }}>
                            {{ $label }}
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Schedule & Settings --}}
        <div class="card mb-4">
            <div class="card-header"><h6 class="mb-0"><i class="ti tabler-settings me-2"></i>Settings</h6></div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Position / Order</label>
                    <input type="number" name="position" class="form-control" min="0"
                           value="{{ old('position', $s?->position ?? ($maxPos ?? 0) + 1) }}">
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label">Starts At</label>
                        <input type="text" name="starts_at" class="form-control flatpickr-datetime"
                               value="{{ old('starts_at', $s?->starts_at?->format('Y-m-d H:i')) }}" placeholder="Immediately">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Ends At</label>
                        <input type="text" name="ends_at" class="form-control flatpickr-datetime"
                               value="{{ old('ends_at', $s?->ends_at?->format('Y-m-d H:i')) }}" placeholder="Never">
                    </div>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="sw-active"
                        @checked(old('is_active', $s?->is_active ?? true))>
                    <label class="form-check-label fw-semibold" for="sw-active">Slider is active</label>
                </div>
            </div>
        </div>
    </div>
</div>

@push('page-js')
<script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
<script>
$(function () {
    $('.select2').select2({ placeholder: '— No Product —', allowClear: true });
    $('.flatpickr-datetime').flatpickr({ enableTime: true, dateFormat: 'Y-m-d H:i' });

    // Type cards
    $('.type-radio').on('change', function () {
        $('.type-card').removeClass('selected');
        $(this).closest('.type-card').addClass('selected');
    });

    // Badge color swatches
    $('.badge-color-pick').on('click', function () {
        $('.badge-color-pick').removeClass('active');
        $(this).addClass('active');
        $('#inp-badge-color').val($(this).data('color'));
        $('#preview-badge').css('background', $(this).data('color'));
    });

    // Text color buttons
    $('.text-color-btn').on('click', function () {
        $('.text-color-btn').removeClass('btn-primary').addClass('btn-outline-secondary');
        $(this).removeClass('btn-outline-secondary').addClass('btn-primary');
        const mode = $(this).find('input').val();
        $('#preview-text-wrap').removeClass('text-light-mode text-dark-mode').addClass('text-' + mode + '-mode');
    });

    // Position buttons
    $('.pos-btn').on('click', function () {
        $('.pos-btn').removeClass('btn-primary').addClass('btn-outline-secondary');
        $(this).removeClass('btn-outline-secondary').addClass('btn-primary');
        const pos = $(this).find('input').val();
        $('#preview-overlay').removeClass('pos-left pos-center pos-right').addClass('pos-' + pos);
    });

    // Image upload zone
    const $zone = $('#upload-zone'), $inp = $('#inp-image');
    $zone.on('click', () => $inp.trigger('click'));
    $zone.on('dragover', e => { e.preventDefault(); $zone.addClass('dragover'); });
    $zone.on('dragleave drop', () => $zone.removeClass('dragover'));
    $zone.on('drop', e => { e.preventDefault(); $inp[0].files = e.originalEvent.dataTransfer.files; $inp.trigger('change'); });
    $inp.on('change', function () {
        if (!this.files[0]) return;
        const reader = new FileReader();
        reader.onload = e => {
            $('#preview-img').attr('src', e.target.result).show();
            $zone.html('<img src="' + e.target.result + '" class="img-fluid rounded" style="max-height:160px">');
        };
        reader.readAsDataURL(this.files[0]);
    });

    // Live preview sync
    $('#inp-title').on('input', function () { $('#preview-title').text(this.value || 'Slider Title'); });
    $('#inp-subtitle').on('input', function () { $('#preview-subtitle').text(this.value || 'Subtitle text'); });
    $('#inp-badge').on('input', function () {
        const v = this.value;
        v ? $('#preview-badge').text(v).show() : $('#preview-badge').hide();
    });
    $('#inp-btn-text').on('input', function () { $('#preview-btn').text(this.value || 'Shop Now'); });
    $('#inp-overlay').on('input', function () { $('#preview-overlay').css('background', this.value); });
});
</script>
@endpush
