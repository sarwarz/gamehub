@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/tagify/tagify.css') }}" />
<style>
.product-upload-zone {
    border: 2px dashed var(--bs-border-color);
    border-radius: 12px;
    padding: 2rem;
    text-align: center;
    cursor: pointer;
    transition: all .25s ease;
    background: var(--bs-body-bg);
    position: relative;
}
.product-upload-zone:hover,
.product-upload-zone.dragover {
    border-color: #7367f0;
    background: rgba(115, 103, 240, .04);
}
.product-upload-zone .upload-icon {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: rgba(115, 103, 240, .1);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: .75rem;
}
.product-upload-zone .upload-icon i {
    font-size: 1.4rem;
    color: #7367f0;
}
.cover-preview-wrapper {
    position: relative;
    display: inline-block;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 12px rgba(0,0,0,.1);
}
.cover-preview-wrapper img {
    max-height: 200px;
    border-radius: 10px;
    object-fit: cover;
}
.cover-preview-wrapper .remove-cover {
    position: absolute;
    top: 6px;
    right: 6px;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: rgba(234, 84, 85, .9);
    color: #fff;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all .2s;
    font-size: .7rem;
}
.cover-preview-wrapper .remove-cover:hover {
    background: #ea5455;
    transform: scale(1.1);
}
.gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    gap: 10px;
}
.gallery-thumb {
    position: relative;
    border-radius: 8px;
    overflow: hidden;
    aspect-ratio: 1;
    box-shadow: 0 1px 6px rgba(0,0,0,.08);
}
.gallery-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.gallery-thumb .remove-gallery-img {
    position: absolute;
    top: 4px;
    right: 4px;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background: rgba(234, 84, 85, .9);
    color: #fff;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: .6rem;
    transition: all .2s;
}
.gallery-thumb .remove-gallery-img:hover {
    background: #ea5455;
    transform: scale(1.1);
}
.seo-preview {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 10px;
    padding: 1rem 1.25rem;
}
.seo-preview .seo-title {
    color: #1a0dab;
    font-size: 1.05rem;
    font-weight: 500;
    margin-bottom: 2px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.seo-preview .seo-url {
    color: #006621;
    font-size: .8rem;
    margin-bottom: 4px;
}
.seo-preview .seo-desc {
    color: #545454;
    font-size: .82rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.char-counter {
    font-size: .72rem;
    float: right;
    margin-top: 2px;
}
.char-counter.text-warning { color: #ff9f43 !important; }
.char-counter.text-danger { color: #ea5455 !important; }
.req-tab-content {
    padding-top: 1rem;
}
.req-row {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 12px;
    border-radius: 8px;
    background: var(--bs-body-bg);
    border: 1px solid var(--bs-border-color);
    margin-bottom: 8px;
    transition: all .2s;
}
.req-row:hover {
    border-color: rgba(115, 103, 240, .3);
}
.req-row .req-key {
    min-width: 120px;
    font-weight: 600;
    font-size: .85rem;
    color: var(--bs-heading-color);
    text-transform: capitalize;
}
.req-row input {
    flex: 1;
}
.req-row .btn-remove-req {
    flex-shrink: 0;
}
.attr-row {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 12px;
    border-radius: 8px;
    background: var(--bs-body-bg);
    border: 1px solid var(--bs-border-color);
    margin-bottom: 8px;
    transition: all .2s;
}
.attr-row:hover {
    border-color: rgba(115, 103, 240, .3);
}
.sidebar-card .card-header {
    padding: .875rem 1.25rem;
}
.sidebar-card .card-header h6 {
    font-size: .875rem;
}
.sidebar-card .card-body {
    padding: 1rem 1.25rem;
}
.publish-status-indicator {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    display: inline-block;
    margin-right: 6px;
}
.publish-status-indicator.active { background: #28c76f; }
.publish-status-indicator.inactive { background: #ea5455; }
.btn-ai {
    background: linear-gradient(135deg, #696cff 0%, #8592ff 100%);
    color: #fff;
    border: none;
    font-size: .78rem;
    font-weight: 600;
    padding: .35rem .85rem;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: all .2s;
    box-shadow: 0 2px 6px rgba(105,108,255,.3);
    cursor: pointer;
}
.btn-ai:hover {
    box-shadow: 0 4px 12px rgba(105,108,255,.45);
    transform: translateY(-1px);
    color: #fff;
}
.btn-ai:disabled {
    opacity: .6;
    cursor: not-allowed;
    transform: none;
}
.btn-ai .spinner-border {
    width: 14px;
    height: 14px;
    border-width: 2px;
}
</style>
@endpush

@php
    $p = $product ?? null;
    $isEdit = !is_null($p);
    $aiEnabled = \App\Services\AiContentService::isEnabled();
@endphp

<div class="row">
    {{-- ===== LEFT COLUMN ===== --}}
    <div class="col-12 col-lg-8">

        {{-- Product Information --}}
        <div class="card mb-4">
            <div class="card-header pb-3">
                <h6 class="mb-0"><i class="ti tabler-package me-2 text-primary"></i>Product Information</h6>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <label class="form-label" for="product-title">Product Name <span class="text-danger">*</span></label>
                    <input type="text" name="title" id="product-title"
                           class="form-control @error('title') is-invalid @enderror"
                           value="{{ old('title', $p->title ?? '') }}"
                           placeholder="Enter product name" required>
                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label" for="product-sku">SKU</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-barcode ti-xs"></i></span>
                            <input type="text" name="sku" id="product-sku"
                                   class="form-control @error('sku') is-invalid @enderror"
                                   value="{{ old('sku', $p->sku ?? '') }}" placeholder="e.g. GH-001">
                            @error('sku') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="product-slug">Slug</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-link ti-xs"></i></span>
                            <input type="text" name="slug" id="product-slug"
                                   class="form-control @error('slug') is-invalid @enderror"
                                   value="{{ old('slug', $p->slug ?? '') }}" placeholder="auto-generated-from-title">
                            @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label mb-0" for="product-short-desc">Short Description</label>
                        @if($aiEnabled)
                        <button type="button" class="btn-ai" id="ai-short-desc" title="Write with AI">
                            <i class="ti tabler-sparkles ti-xs"></i> Write with AI
                        </button>
                        @endif
                    </div>
                    <textarea name="short_description" id="product-short-desc" rows="2"
                              class="form-control @error('short_description') is-invalid @enderror"
                              placeholder="Brief summary shown in listings (max 300 chars)" maxlength="300">{{ old('short_description', $p->short_description ?? '') }}</textarea>
                    <div class="d-flex justify-content-end">
                        <span class="char-counter" id="short-desc-counter">0 / 300</span>
                    </div>
                    @error('short_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-0">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label mb-0">Description</label>
                        @if($aiEnabled)
                        <button type="button" class="btn-ai" id="ai-description" title="Write with AI">
                            <i class="ti tabler-sparkles ti-xs"></i> Write with AI
                        </button>
                        @endif
                    </div>
                    <input type="hidden" name="description" id="product-description-input"
                           value="{{ old('description', $p->description ?? '') }}">
                    <div id="product-description-toolbar">
                        <span class="ql-formats">
                            <select class="ql-header">
                                <option value="1">Heading 1</option>
                                <option value="2">Heading 2</option>
                                <option value="3">Heading 3</option>
                                <option selected>Normal</option>
                            </select>
                        </span>
                        <span class="ql-formats">
                            <button class="ql-bold"></button>
                            <button class="ql-italic"></button>
                            <button class="ql-underline"></button>
                            <button class="ql-strike"></button>
                        </span>
                        <span class="ql-formats">
                            <button class="ql-list" value="ordered"></button>
                            <button class="ql-list" value="bullet"></button>
                        </span>
                        <span class="ql-formats">
                            <button class="ql-link"></button>
                            <button class="ql-blockquote"></button>
                            <button class="ql-code-block"></button>
                        </span>
                        <span class="ql-formats">
                            <button class="ql-clean"></button>
                        </span>
                    </div>
                    <div id="product-description-editor" style="min-height: 200px;"></div>
                    @error('description') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        {{-- Media --}}
        <div class="card mb-4">
            <div class="card-header pb-3">
                <h6 class="mb-0"><i class="ti tabler-photo me-2 text-primary"></i>Media</h6>
            </div>
            <div class="card-body">
                {{-- Cover Image --}}
                <div class="mb-4">
                    <label class="form-label fw-semibold">Cover Image</label>
                    <div id="cover-preview-area" class="mb-3" style="{{ ($isEdit && $p->image) ? '' : 'display:none' }}">
                        <div class="cover-preview-wrapper">
                            <img id="cover-preview-img" src="{{ $isEdit && $p->image ? asset($p->image) : '' }}" alt="Cover">
                            <button type="button" class="remove-cover" id="remove-cover-btn" title="Remove">
                                <i class="ti tabler-x ti-xs"></i>
                            </button>
                        </div>
                    </div>
                    <div class="product-upload-zone" id="cover-upload-zone" style="{{ ($isEdit && $p->image) ? 'display:none' : '' }}">
                        <div class="upload-icon"><i class="ti tabler-cloud-upload"></i></div>
                        <p class="mb-1 fw-medium">Drop your cover image here or <span class="text-primary">browse</span></p>
                        <small class="text-muted">JPG, JPEG or PNG. Max 2MB</small>
                        <input type="file" name="cover_image" id="cover-image-input" class="d-none"
                               accept="image/jpeg,image/png,image/jpg">
                    </div>
                    @error('cover_image') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                {{-- Gallery Images --}}
                <div>
                    <label class="form-label fw-semibold">Gallery Images</label>
                    @if($isEdit && !empty($p->gallery) && count($p->gallery))
                    <div class="gallery-grid mb-3" id="existing-gallery">
                        @foreach($p->gallery as $idx => $img)
                        <div class="gallery-thumb" data-index="{{ $idx }}">
                            <img src="{{ asset($img) }}" alt="Gallery {{ $idx + 1 }}">
                        </div>
                        @endforeach
                    </div>
                    @endif
                    <div class="product-upload-zone" id="gallery-upload-zone">
                        <div class="upload-icon"><i class="ti tabler-photo-plus"></i></div>
                        <p class="mb-1 fw-medium">Drop gallery images here or <span class="text-primary">browse</span></p>
                        <small class="text-muted">{{ $isEdit ? 'Upload replaces all existing gallery images' : 'Multiple files allowed. JPG, JPEG or PNG' }}</small>
                        <input type="file" name="gallery[]" id="gallery-input" class="d-none"
                               accept="image/jpeg,image/png,image/jpg" multiple>
                    </div>
                    <div class="gallery-grid mt-3" id="gallery-preview"></div>
                    @error('gallery') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        {{-- System Requirements --}}
        <div class="card mb-4">
            <div class="card-header pb-0">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="ti tabler-device-desktop me-2 text-primary"></i>System Requirements</h6>
                    <div class="d-flex align-items-center gap-3">
                        @if($aiEnabled)
                        <button type="button" class="btn-ai" id="ai-sysreq" title="Auto-fill with AI">
                            <i class="ti tabler-sparkles ti-xs"></i> Fill with AI
                        </button>
                        @endif
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="copy-min-to-rec">
                        <label class="form-check-label small" for="copy-min-to-rec">Copy min to recommended</label>
                    </div>
                    </div>
                </div>
                <ul class="nav nav-tabs mt-3 border-0" role="tablist">
                    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#req-minimum">Minimum</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#req-recommended">Recommended</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#req-extra">Extra</a></li>
                </ul>
            </div>
            <div class="card-body pt-0">
                <div class="tab-content req-tab-content">
                    {{-- Minimum --}}
                    <div class="tab-pane fade show active" id="req-minimum">
                        <div id="min-req-list">
                            @php
                                $defaultKeys = ['os', 'processor', 'memory', 'graphics', 'storage'];
                                $minReqs = old('system_requirements.minimum', $isEdit ? ($p->system_requirements['minimum'] ?? []) : []);
                                if (empty($minReqs)) {
                                    $minReqs = array_map(fn($k) => ['key' => $k, 'value' => ''], $defaultKeys);
                                }
                            @endphp
                            @foreach($minReqs as $i => $req)
                            <div class="req-row">
                                <span class="req-key">{{ $req['key'] ?? '' }}</span>
                                <input type="hidden" name="system_requirements[minimum][{{ $i }}][key]" value="{{ $req['key'] ?? '' }}">
                                <input type="text" name="system_requirements[minimum][{{ $i }}][value]"
                                       class="form-control form-control-sm min-req-value"
                                       value="{{ $req['value'] ?? '' }}"
                                       placeholder="Enter {{ $req['key'] ?? '' }} specification">
                                <button type="button" class="btn btn-icon btn-sm btn-label-danger btn-remove-req" title="Remove">
                                    <i class="ti tabler-x ti-xs"></i>
                                </button>
                            </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-sm btn-label-primary mt-2" id="add-min-req">
                            <i class="ti tabler-plus ti-xs me-1"></i> Add Requirement
                        </button>
                    </div>

                    {{-- Recommended --}}
                    <div class="tab-pane fade" id="req-recommended">
                        <div id="rec-req-list">
                            @php
                                $recReqs = old('system_requirements.recommended', $isEdit ? ($p->system_requirements['recommended'] ?? []) : []);
                                if (empty($recReqs)) {
                                    $recReqs = array_map(fn($k) => ['key' => $k, 'value' => ''], $defaultKeys);
                                }
                            @endphp
                            @foreach($recReqs as $i => $req)
                            <div class="req-row">
                                <span class="req-key">{{ $req['key'] ?? '' }}</span>
                                <input type="hidden" name="system_requirements[recommended][{{ $i }}][key]" value="{{ $req['key'] ?? '' }}">
                                <input type="text" name="system_requirements[recommended][{{ $i }}][value]"
                                       class="form-control form-control-sm rec-req-value"
                                       value="{{ $req['value'] ?? '' }}"
                                       placeholder="Enter {{ $req['key'] ?? '' }} specification">
                                <button type="button" class="btn btn-icon btn-sm btn-label-danger btn-remove-req" title="Remove">
                                    <i class="ti tabler-x ti-xs"></i>
                                </button>
                            </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-sm btn-label-primary mt-2" id="add-rec-req">
                            <i class="ti tabler-plus ti-xs me-1"></i> Add Requirement
                        </button>
                    </div>

                    {{-- Extra --}}
                    <div class="tab-pane fade" id="req-extra">
                        <div id="extra-req-list">
                            @php
                                $extraReqs = old('system_requirements.extra', $isEdit ? ($p->system_requirements['extra'] ?? []) : []);
                            @endphp
                            @forelse($extraReqs as $i => $req)
                            <div class="req-row">
                                <input type="text" name="system_requirements[extra][{{ $i }}][key]"
                                       class="form-control form-control-sm" style="max-width: 150px"
                                       value="{{ $req['key'] ?? '' }}" placeholder="e.g. DirectX">
                                <input type="text" name="system_requirements[extra][{{ $i }}][value]"
                                       class="form-control form-control-sm"
                                       value="{{ $req['value'] ?? '' }}" placeholder="e.g. Version 12">
                                <button type="button" class="btn btn-icon btn-sm btn-label-danger btn-remove-req" title="Remove">
                                    <i class="ti tabler-x ti-xs"></i>
                                </button>
                            </div>
                            @empty
                            <div class="text-center text-muted py-3">
                                <i class="ti tabler-info-circle d-block mb-1" style="font-size: 1.5rem"></i>
                                <small>No extra requirements yet</small>
                            </div>
                            @endforelse
                        </div>
                        <button type="button" class="btn btn-sm btn-label-primary mt-2" id="add-extra-req">
                            <i class="ti tabler-plus ti-xs me-1"></i> Add Extra Requirement
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Extra Attributes --}}
        <div class="card mb-4">
            <div class="card-header pb-3">
                <h6 class="mb-0"><i class="ti tabler-list-details me-2 text-primary"></i>Extra Attributes</h6>
            </div>
            <div class="card-body">
                <div id="attributes-list">
                    @php
                        $attrs = old('attributes', $isEdit ? ($p->attributes ?? []) : []);
                    @endphp
                    @forelse($attrs as $i => $attr)
                    <div class="attr-row">
                        <input type="text" name="attributes[{{ $i }}][key]"
                               class="form-control form-control-sm" style="max-width: 200px"
                               value="{{ $attr['key'] ?? '' }}" placeholder="Attribute name">
                        <input type="text" name="attributes[{{ $i }}][value]"
                               class="form-control form-control-sm"
                               value="{{ $attr['value'] ?? '' }}" placeholder="Attribute value">
                        <button type="button" class="btn btn-icon btn-sm btn-label-danger btn-remove-attr" title="Remove">
                            <i class="ti tabler-x ti-xs"></i>
                        </button>
                    </div>
                    @empty
                    <div class="text-center text-muted py-3" id="no-attrs-msg">
                        <i class="ti tabler-info-circle d-block mb-1" style="font-size: 1.5rem"></i>
                        <small>No extra attributes yet</small>
                    </div>
                    @endforelse
                </div>
                <button type="button" class="btn btn-sm btn-label-primary mt-2" id="add-attribute">
                    <i class="ti tabler-plus ti-xs me-1"></i> Add Attribute
                </button>
            </div>
        </div>

        {{-- SEO Settings --}}
        <div class="card mb-4">
            <div class="card-header pb-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="ti tabler-search me-2 text-primary"></i>SEO Settings</h6>
                    <div class="d-flex align-items-center gap-2">
                        @if($aiEnabled)
                        <button type="button" class="btn-ai" id="ai-seo" title="Generate SEO with AI">
                            <i class="ti tabler-sparkles ti-xs"></i> Generate SEO
                        </button>
                        @endif
                        <span class="badge bg-label-secondary"><i class="ti tabler-robot ti-xs me-1"></i>Search Preview</span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="seo-preview mb-4" id="seo-preview-box">
                    <div class="seo-title" id="seo-preview-title">{{ $p->meta_title ?? $p->title ?? 'Product Title' }}</div>
                    <div class="seo-url">{{ url('/') }}/products/<span id="seo-preview-slug">{{ $p->slug ?? 'product-slug' }}</span></div>
                    <div class="seo-desc" id="seo-preview-desc">{{ $p->meta_description ?? 'Product description will appear here...' }}</div>
                </div>

                <div class="mb-4">
                    <label class="form-label" for="meta-title">Meta Title</label>
                    <input type="text" name="meta_title" id="meta-title"
                           class="form-control @error('meta_title') is-invalid @enderror"
                           value="{{ old('meta_title', $p->meta_title ?? '') }}"
                           placeholder="SEO title (recommended: 50-60 characters)" maxlength="60">
                    <span class="char-counter" id="meta-title-counter">0 / 60</span>
                    @error('meta_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label" for="meta-description">Meta Description</label>
                    <textarea name="meta_description" id="meta-description" rows="3"
                              class="form-control @error('meta_description') is-invalid @enderror"
                              placeholder="SEO description (recommended: 150-160 characters)" maxlength="160">{{ old('meta_description', $p->meta_description ?? '') }}</textarea>
                    <span class="char-counter" id="meta-desc-counter">0 / 160</span>
                    @error('meta_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="form-label" for="meta-keywords">Meta Keywords</label>
                    <input type="text" name="meta_keywords" id="meta-keywords"
                           class="form-control tagify-input @error('meta_keywords') is-invalid @enderror"
                           value="{{ old('meta_keywords', $p->meta_keywords ?? '') }}"
                           placeholder="Type and press Enter to add keywords">
                    @error('meta_keywords') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- ===== RIGHT COLUMN ===== --}}
    <div class="col-12 col-lg-4">

        {{-- Publish --}}
        <div class="card sidebar-card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="ti tabler-settings me-2 text-primary"></i>Publish</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label" for="product-status">Status</label>
                    @php $status = old('status', $p->status ?? 'active'); @endphp
                    <select name="status" id="product-status" class="form-select select2">
                        <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @error('status') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="product-sort">Sort Order</label>
                    <input type="number" name="sort_order" id="product-sort"
                           class="form-control" value="{{ old('sort_order', $p->sort_order ?? 0) }}" min="0">
                </div>

                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="is_featured" value="1"
                           id="is-featured" {{ old('is_featured', $p->is_featured ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is-featured">
                        <i class="ti tabler-star ti-xs me-1 text-warning"></i> Featured Product
                    </label>
                </div>
            </div>
        </div>

        {{-- Product Organization --}}
        <div class="card sidebar-card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="ti tabler-category me-2 text-primary"></i>Organization</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Categories</label>
                    <select name="category_ids[]" class="form-select select2 @error('category_ids') is-invalid @enderror" multiple
                            data-placeholder="Select categories">
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}"
                            {{ in_array($cat->id, old('category_ids', $isEdit ? $p->categories->pluck('id')->toArray() : [])) ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('category_ids') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Platforms</label>
                    <select name="platform_ids[]" class="form-select select2 @error('platform_ids') is-invalid @enderror" multiple
                            data-placeholder="Select platforms">
                        @foreach($platforms as $pl)
                        <option value="{{ $pl->id }}"
                            {{ in_array($pl->id, old('platform_ids', $isEdit ? $p->platforms->pluck('id')->toArray() : [])) ? 'selected' : '' }}>
                            {{ $pl->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('platform_ids') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="form-label">Product Types</label>
                    <select name="type_ids[]" class="form-select select2 @error('type_ids') is-invalid @enderror" multiple
                            data-placeholder="Select product types">
                        @foreach($types as $t)
                        <option value="{{ $t->id }}"
                            {{ in_array($t->id, old('type_ids', $isEdit ? $p->types->pluck('id')->toArray() : [])) ? 'selected' : '' }}>
                            {{ $t->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('type_ids') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        {{-- Classification --}}
        <div class="card sidebar-card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="ti tabler-world me-2 text-primary"></i>Classification</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Regions</label>
                    <select name="region_ids[]" class="form-select select2 @error('region_ids') is-invalid @enderror" multiple
                            data-placeholder="Select regions">
                        @foreach($regions as $r)
                        <option value="{{ $r->id }}"
                            {{ in_array($r->id, old('region_ids', $isEdit ? $p->regions->pluck('id')->toArray() : [])) ? 'selected' : '' }}>
                            {{ $r->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('region_ids') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Languages</label>
                    <select name="language_ids[]" class="form-select select2 @error('language_ids') is-invalid @enderror" multiple
                            data-placeholder="Select languages">
                        @foreach($languages as $lang)
                        <option value="{{ $lang->id }}"
                            {{ in_array($lang->id, old('language_ids', $isEdit ? $p->languages->pluck('id')->toArray() : [])) ? 'selected' : '' }}>
                            {{ $lang->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('language_ids') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="form-label">Works On</label>
                    <select name="works_on_ids[]" class="form-select select2 @error('works_on_ids') is-invalid @enderror" multiple
                            data-placeholder="Select compatibility">
                        @foreach($workson as $w)
                        <option value="{{ $w->id }}"
                            {{ in_array($w->id, old('works_on_ids', $isEdit ? $p->worksOn->pluck('id')->toArray() : [])) ? 'selected' : '' }}>
                            {{ $w->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('works_on_ids') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        {{-- Details --}}
        <div class="card sidebar-card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="ti tabler-info-circle me-2 text-primary"></i>Details</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Developer</label>
                    <select name="developer_id" class="form-select select2 @error('developer_id') is-invalid @enderror"
                            data-placeholder="Select developer">
                        <option value=""></option>
                        @foreach($developers as $d)
                        <option value="{{ $d->id }}"
                            {{ old('developer_id', $p->developer_id ?? '') == $d->id ? 'selected' : '' }}>
                            {{ $d->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('developer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Publisher</label>
                    <select name="publisher_id" class="form-select select2 @error('publisher_id') is-invalid @enderror"
                            data-placeholder="Select publisher">
                        <option value=""></option>
                        @foreach($publishers as $pub)
                        <option value="{{ $pub->id }}"
                            {{ old('publisher_id', $p->publisher_id ?? '') == $pub->id ? 'selected' : '' }}>
                            {{ $pub->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('publisher_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Product Label</label>
                    <select name="label_id" class="form-select select2 @error('label_id') is-invalid @enderror"
                            data-placeholder="Select label">
                        <option value=""></option>
                        @foreach($labels as $label)
                        <option value="{{ $label->id }}"
                            {{ old('label_id', $p->label_id ?? '') == $label->id ? 'selected' : '' }}>
                            {{ $label->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('label_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="form-label">Delivery Type</label>
                    @php $delivery = old('delivery_type', $p->delivery_type ?? 'instant'); @endphp
                    <select name="delivery_type" class="form-select select2 @error('delivery_type') is-invalid @enderror">
                        <option value="instant" {{ $delivery === 'instant' ? 'selected' : '' }}>
                            Instant Delivery
                        </option>
                        <option value="manual" {{ $delivery === 'manual' ? 'selected' : '' }}>
                            Manual Delivery
                        </option>
                        <option value="email" {{ $delivery === 'email' ? 'selected' : '' }}>
                            Email Delivery
                        </option>
                        <option value="link" {{ $delivery === 'link' ? 'selected' : '' }}>
                            External Link
                        </option>
                    </select>
                    @error('delivery_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

    </div>
</div>

@push('page-js')
<script src="{{ asset('assets/vendor/libs/tagify/tagify.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {

    // ============================
    // Quill Editor
    // ============================
    const descEditor = new Quill('#product-description-editor', {
        modules: { toolbar: '#product-description-toolbar' },
        placeholder: 'Write a detailed product description...',
        theme: 'snow'
    });

    const descInput = document.getElementById('product-description-input');
    if (descInput.value) {
        descEditor.root.innerHTML = descInput.value;
    }
    descEditor.on('text-change', function() {
        descInput.value = descEditor.root.innerHTML;
    });

    // ============================
    // Auto Slug
    // ============================
    const titleInput = document.getElementById('product-title');
    const slugInput = document.getElementById('product-slug');
    let slugManuallyEdited = {{ $isEdit ? 'true' : 'false' }};

    slugInput.addEventListener('input', () => { slugManuallyEdited = true; });
    titleInput.addEventListener('input', function() {
        if (!slugManuallyEdited) {
            slugInput.value = this.value.toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .trim();
            document.getElementById('seo-preview-slug').textContent = slugInput.value || 'product-slug';
        }
    });

    // ============================
    // Character Counters
    // ============================
    function setupCounter(inputId, counterId, max) {
        const inp = document.getElementById(inputId);
        const ctr = document.getElementById(counterId);
        function update() {
            const len = inp.value.length;
            ctr.textContent = len + ' / ' + max;
            ctr.className = 'char-counter' + (len > max ? ' text-danger' : len > max * 0.85 ? ' text-warning' : ' text-muted');
        }
        inp.addEventListener('input', update);
        update();
    }
    setupCounter('product-short-desc', 'short-desc-counter', 300);
    setupCounter('meta-title', 'meta-title-counter', 60);
    setupCounter('meta-description', 'meta-desc-counter', 160);

    // ============================
    // SEO Live Preview
    // ============================
    document.getElementById('meta-title').addEventListener('input', function() {
        document.getElementById('seo-preview-title').textContent = this.value || titleInput.value || 'Product Title';
    });
    document.getElementById('meta-description').addEventListener('input', function() {
        document.getElementById('seo-preview-desc').textContent = this.value || 'Product description will appear here...';
    });
    titleInput.addEventListener('input', function() {
        const metaTitle = document.getElementById('meta-title');
        if (!metaTitle.value) {
            document.getElementById('seo-preview-title').textContent = this.value || 'Product Title';
        }
    });

    // ============================
    // Tagify for Keywords
    // ============================
    const tagInput = document.getElementById('meta-keywords');
    const tagify = new Tagify(tagInput, {
        delimiters: ',',
        originalInputValueFormat: vals => vals.map(v => v.value).join(',')
    });
    if (tagInput.value && !tagInput.value.startsWith('[')) {
        const existing = tagInput.value.split(',').map(v => ({ value: v.trim() })).filter(v => v.value);
        tagify.addTags(existing);
    }
    document.querySelector('form').addEventListener('submit', function() {
        tagInput.value = tagify.value.map(v => v.value).join(',');
    });

    // ============================
    // Cover Image Upload
    // ============================
    const coverZone = document.getElementById('cover-upload-zone');
    const coverInput = document.getElementById('cover-image-input');
    const coverPreview = document.getElementById('cover-preview-area');
    const coverImg = document.getElementById('cover-preview-img');
    const removeCoverBtn = document.getElementById('remove-cover-btn');

    coverZone.addEventListener('click', () => coverInput.click());
    coverZone.addEventListener('dragover', e => { e.preventDefault(); coverZone.classList.add('dragover'); });
    coverZone.addEventListener('dragleave', () => coverZone.classList.remove('dragover'));
    coverZone.addEventListener('drop', e => {
        e.preventDefault();
        coverZone.classList.remove('dragover');
        if (e.dataTransfer.files.length) {
            coverInput.files = e.dataTransfer.files;
            showCoverPreview(e.dataTransfer.files[0]);
        }
    });
    coverInput.addEventListener('change', function() {
        if (this.files.length) showCoverPreview(this.files[0]);
    });

    function showCoverPreview(file) {
        const reader = new FileReader();
        reader.onload = e => {
            coverImg.src = e.target.result;
            coverPreview.style.display = '';
            coverZone.style.display = 'none';
        };
        reader.readAsDataURL(file);
    }

    removeCoverBtn.addEventListener('click', () => {
        coverInput.value = '';
        coverPreview.style.display = 'none';
        coverZone.style.display = '';
    });

    // ============================
    // Gallery Upload
    // ============================
    const galleryZone = document.getElementById('gallery-upload-zone');
    const galleryInput = document.getElementById('gallery-input');
    const galleryPreview = document.getElementById('gallery-preview');

    galleryZone.addEventListener('click', () => galleryInput.click());
    galleryZone.addEventListener('dragover', e => { e.preventDefault(); galleryZone.classList.add('dragover'); });
    galleryZone.addEventListener('dragleave', () => galleryZone.classList.remove('dragover'));
    galleryZone.addEventListener('drop', e => {
        e.preventDefault();
        galleryZone.classList.remove('dragover');
        if (e.dataTransfer.files.length) {
            galleryInput.files = e.dataTransfer.files;
            showGalleryPreview(e.dataTransfer.files);
        }
    });
    galleryInput.addEventListener('change', function() {
        if (this.files.length) showGalleryPreview(this.files);
    });

    function showGalleryPreview(files) {
        galleryPreview.innerHTML = '';
        Array.from(files).forEach(file => {
            const reader = new FileReader();
            reader.onload = e => {
                galleryPreview.insertAdjacentHTML('beforeend',
                    '<div class="gallery-thumb"><img src="' + e.target.result + '" alt="Gallery"></div>'
                );
            };
            reader.readAsDataURL(file);
        });
    }

    // ============================
    // Dynamic System Requirements
    // ============================
    let minIdx = {{ count($minReqs) }};
    let recIdx = {{ count($recReqs) }};
    let extraIdx = {{ count($extraReqs) }};

    function swalAddReq(group, listId, idxRef) {
        var presets = ['os', 'processor', 'memory', 'graphics', 'storage', 'network', 'directx', 'sound_card', 'additional_notes'];
        var existing = [];
        document.querySelectorAll('#' + listId + ' .req-row input[type="hidden"]').forEach(function(h) {
            if (h.value) existing.push(h.value.toLowerCase());
        });
        var available = presets.filter(function(p) { return existing.indexOf(p) === -1; });

        var optionsHtml = available.map(function(p) {
            var label = p.replace(/_/g, ' ').replace(/\b\w/g, function(c) { return c.toUpperCase(); });
            return '<option value="' + p + '">' + label + '</option>';
        }).join('');

        Swal.fire({
            title: 'Add Requirement',
            html: '<div style="text-align:left;">' +
                (available.length ? '<label class="form-label mb-1" style="font-size:.85rem;font-weight:600;">Quick Select</label>' +
                '<select id="swal-req-preset" class="form-select form-select-sm mb-3"><option value="">-- Choose a preset --</option>' + optionsHtml + '</select>' +
                '<div class="text-center text-muted mb-2" style="font-size:.75rem;">or enter a custom key</div>' : '') +
                '<label class="form-label mb-1" style="font-size:.85rem;font-weight:600;">Requirement Key</label>' +
                '<input id="swal-req-key" class="form-control form-control-sm" placeholder="e.g. processor, network, vram">' +
                '</div>',
            showCancelButton: true,
            confirmButtonText: '<i class="ti tabler-plus ti-xs me-1"></i> Add',
            cancelButtonText: 'Cancel',
            customClass: {
                confirmButton: 'btn btn-primary btn-sm',
                cancelButton: 'btn btn-label-secondary btn-sm ms-2',
                popup: 'shadow-lg'
            },
            buttonsStyling: false,
            didOpen: function() {
                var preset = document.getElementById('swal-req-preset');
                var keyInput = document.getElementById('swal-req-key');
                if (preset) {
                    preset.addEventListener('change', function() { keyInput.value = this.value; });
                }
                keyInput.focus();
            },
            preConfirm: function() {
                var key = document.getElementById('swal-req-key').value.trim().toLowerCase().replace(/\s+/g, '_');
                if (!key) {
                    Swal.showValidationMessage('Please enter a requirement key');
                    return false;
                }
                if (existing.indexOf(key) !== -1) {
                    Swal.showValidationMessage('This requirement already exists');
                    return false;
                }
                return key;
            }
        }).then(function(result) {
            if (result.isConfirmed && result.value) {
                document.getElementById(listId).insertAdjacentHTML('beforeend', reqRow(group, idxRef.val++, result.value));
            }
        });
    }

    var minIdxRef = { val: minIdx };
    var recIdxRef = { val: recIdx };
    var extraIdxRef = { val: extraIdx };

    document.getElementById('add-min-req').addEventListener('click', function() {
        swalAddReq('minimum', 'min-req-list', minIdxRef);
    });

    document.getElementById('add-rec-req').addEventListener('click', function() {
        swalAddReq('recommended', 'rec-req-list', recIdxRef);
    });

    document.getElementById('add-extra-req').addEventListener('click', function() {
        var empty = document.querySelector('#extra-req-list .text-center');
        if (empty) empty.remove();

        Swal.fire({
            title: 'Add Extra Requirement',
            html: '<div style="text-align:left;">' +
                '<label class="form-label mb-1" style="font-size:.85rem;font-weight:600;">Key</label>' +
                '<input id="swal-extra-key" class="form-control form-control-sm mb-3" placeholder="e.g. DirectX, Additional Notes">' +
                '<label class="form-label mb-1" style="font-size:.85rem;font-weight:600;">Value</label>' +
                '<input id="swal-extra-val" class="form-control form-control-sm" placeholder="e.g. Version 12">' +
                '</div>',
            showCancelButton: true,
            confirmButtonText: '<i class="ti tabler-plus ti-xs me-1"></i> Add',
            cancelButtonText: 'Cancel',
            customClass: {
                confirmButton: 'btn btn-primary btn-sm',
                cancelButton: 'btn btn-label-secondary btn-sm ms-2',
                popup: 'shadow-lg'
            },
            buttonsStyling: false,
            didOpen: function() {
                document.getElementById('swal-extra-key').focus();
            },
            preConfirm: function() {
                var key = document.getElementById('swal-extra-key').value.trim();
                if (!key) {
                    Swal.showValidationMessage('Please enter a key');
                    return false;
                }
                return { key: key, value: document.getElementById('swal-extra-val').value.trim() };
            }
        }).then(function(result) {
            if (result.isConfirmed && result.value) {
                var idx = extraIdxRef.val++;
                document.getElementById('extra-req-list').insertAdjacentHTML('beforeend',
                    '<div class="req-row">' +
                    '<input type="text" name="system_requirements[extra][' + idx + '][key]" class="form-control form-control-sm" style="max-width:150px" value="' + result.value.key + '">' +
                    '<input type="text" name="system_requirements[extra][' + idx + '][value]" class="form-control form-control-sm" value="' + result.value.value + '">' +
                    '<button type="button" class="btn btn-icon btn-sm btn-label-danger btn-remove-req" title="Remove"><i class="ti tabler-x ti-xs"></i></button>' +
                    '</div>'
                );
            }
        });
    });

    function reqRow(group, idx, key) {
        return '<div class="req-row">' +
            '<span class="req-key">' + key + '</span>' +
            '<input type="hidden" name="system_requirements[' + group + '][' + idx + '][key]" value="' + key + '">' +
            '<input type="text" name="system_requirements[' + group + '][' + idx + '][value]" class="form-control form-control-sm ' + (group === 'minimum' ? 'min' : 'rec') + '-req-value" placeholder="Enter ' + key + ' specification">' +
            '<button type="button" class="btn btn-icon btn-sm btn-label-danger btn-remove-req" title="Remove"><i class="ti tabler-x ti-xs"></i></button>' +
            '</div>';
    }

    

    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-remove-req')) {
            e.target.closest('.req-row').remove();
        }
        if (e.target.closest('.btn-remove-attr')) {
            e.target.closest('.attr-row').remove();
        }
    });

    // ============================
    // Dynamic Attributes
    // ============================
    let attrIdx = {{ count($attrs) }};

    document.getElementById('add-attribute').addEventListener('click', function() {
        const noMsg = document.getElementById('no-attrs-msg');
        if (noMsg) noMsg.remove();
        document.getElementById('attributes-list').insertAdjacentHTML('beforeend',
            '<div class="attr-row">' +
            '<input type="text" name="attributes[' + attrIdx + '][key]" class="form-control form-control-sm" style="max-width:200px" placeholder="Attribute name">' +
            '<input type="text" name="attributes[' + attrIdx + '][value]" class="form-control form-control-sm" placeholder="Attribute value">' +
            '<button type="button" class="btn btn-icon btn-sm btn-label-danger btn-remove-attr" title="Remove"><i class="ti tabler-x ti-xs"></i></button>' +
            '</div>'
        );
        attrIdx++;
    });

    // ============================
    // Copy Min to Recommended
    // ============================
    const copyChk = document.getElementById('copy-min-to-rec');
    copyChk.addEventListener('change', function() {
        const minValues = {};
        document.querySelectorAll('#min-req-list .req-row').forEach(row => {
            const key = row.querySelector('input[type="hidden"]')?.value || '';
            const val = row.querySelector('input[type="text"]')?.value || '';
            if (key) minValues[key] = val;
        });

        document.querySelectorAll('#rec-req-list .req-row').forEach(row => {
            const key = row.querySelector('input[type="hidden"]')?.value || '';
            const inp = row.querySelector('.rec-req-value');
            if (key && minValues[key] !== undefined && inp) {
                inp.value = this.checked ? minValues[key] : '';
                inp.readOnly = this.checked;
            }
        });
    });

    document.getElementById('min-req-list').addEventListener('input', function() {
        if (copyChk.checked) copyChk.dispatchEvent(new Event('change'));
    });

    // ============================
    // Sync Quill on form submit
    // ============================
    document.querySelector('form').addEventListener('submit', function() {
        descInput.value = descEditor.root.innerHTML;
    });

    @if($aiEnabled)
    // ============================
    // AI Content Generation
    // ============================
    var aiUrl = '{{ route("ai.generate") }}';
    var csrfToken = '{{ csrf_token() }}';

    function getTitle() {
        return document.getElementById('product-title').value.trim();
    }

    function aiRequest(btn, type, extraData) {
        var title = getTitle();
        if (!title) {
            Swal.fire({ icon: 'warning', title: 'Title Required', text: 'Please enter a product title first.', timer: 2500, showConfirmButton: false });
            return;
        }

        var origHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Generating...';

        var body = Object.assign({ type: type, title: title, _token: csrfToken }, extraData || {});

        fetch(aiUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify(body)
        })
        .then(function(r) { return r.json().then(function(d) { return { ok: r.ok, data: d }; }); })
        .then(function(res) {
            btn.disabled = false;
            btn.innerHTML = origHtml;
            if (!res.ok) {
                Swal.fire({ icon: 'error', title: 'AI Error', text: res.data.error || 'Something went wrong.', confirmButtonText: 'OK', customClass: { confirmButton: 'btn btn-primary' }, buttonsStyling: false });
                return null;
            }
            return res.data;
        })
        .catch(function(err) {
            btn.disabled = false;
            btn.innerHTML = origHtml;
            Swal.fire({ icon: 'error', title: 'Network Error', text: err.message, confirmButtonText: 'OK', customClass: { confirmButton: 'btn btn-primary' }, buttonsStyling: false });
            return null;
        });

        return null;
    }

    function aiCall(btn, type, extraData) {
        var title = getTitle();
        if (!title) {
            Swal.fire({ icon: 'warning', title: 'Title Required', text: 'Please enter a product title first.', timer: 2500, showConfirmButton: false });
            return Promise.reject('no title');
        }

        var origHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Generating...';

        var body = Object.assign({ type: type, title: title, _token: csrfToken }, extraData || {});

        return fetch(aiUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify(body)
        })
        .then(function(r) { return r.json().then(function(d) { return { ok: r.ok, data: d }; }); })
        .then(function(res) {
            btn.disabled = false;
            btn.innerHTML = origHtml;
            if (!res.ok) {
                Swal.fire({ icon: 'error', title: 'AI Error', text: res.data.error || 'Something went wrong.', confirmButtonText: 'OK', customClass: { confirmButton: 'btn btn-primary' }, buttonsStyling: false });
                throw new Error(res.data.error);
            }
            return res.data;
        })
        .catch(function(err) {
            btn.disabled = false;
            btn.innerHTML = origHtml;
            if (err.message !== 'no title') {
                Swal.fire({ icon: 'error', title: 'Error', text: err.message, confirmButtonText: 'OK', customClass: { confirmButton: 'btn btn-primary' }, buttonsStyling: false });
            }
            throw err;
        });
    }

    // Short Description AI
    var aiShortBtn = document.getElementById('ai-short-desc');
    if (aiShortBtn) {
        aiShortBtn.addEventListener('click', function() {
            aiCall(this, 'short_description').then(function(res) {
                var ta = document.getElementById('product-short-desc');
                ta.value = res.data.content;
                ta.dispatchEvent(new Event('input'));
                Swal.fire({ icon: 'success', title: 'Done!', text: 'Short description generated.', timer: 1500, showConfirmButton: false });
            });
        });
    }

    // Description AI
    var aiDescBtn = document.getElementById('ai-description');
    if (aiDescBtn) {
        aiDescBtn.addEventListener('click', function() {
            var short = document.getElementById('product-short-desc').value.trim();
            aiCall(this, 'description', { short_description: short }).then(function(res) {
                descEditor.root.innerHTML = res.data.content;
                descInput.value = res.data.content;
                Swal.fire({ icon: 'success', title: 'Done!', text: 'Description generated.', timer: 1500, showConfirmButton: false });
            });
        });
    }

    // System Requirements AI
    var aiSysBtn = document.getElementById('ai-sysreq');
    if (aiSysBtn) {
        aiSysBtn.addEventListener('click', function() {
            aiCall(this, 'system_requirements').then(function(res) {
                var data = res.data;
                fillReqList('min-req-list', 'minimum', data.minimum || [], minIdxRef);
                fillReqList('rec-req-list', 'recommended', data.recommended || [], recIdxRef);
                fillExtraList(data.extra || [], extraIdxRef);
                Swal.fire({ icon: 'success', title: 'Done!', text: 'System requirements filled.', timer: 1500, showConfirmButton: false });
            });
        });
    }

    function fillReqList(listId, group, items, idxRef) {
        var list = document.getElementById(listId);
        list.innerHTML = '';
        items.forEach(function(item) {
            var idx = idxRef.val++;
            list.insertAdjacentHTML('beforeend',
                '<div class="req-row">' +
                '<span class="req-key">' + item.key + '</span>' +
                '<input type="hidden" name="system_requirements[' + group + '][' + idx + '][key]" value="' + item.key + '">' +
                '<input type="text" name="system_requirements[' + group + '][' + idx + '][value]" class="form-control form-control-sm ' + (group === 'minimum' ? 'min' : 'rec') + '-req-value" value="' + (item.value || '') + '" placeholder="Enter ' + item.key + ' specification">' +
                '<button type="button" class="btn btn-icon btn-sm btn-label-danger btn-remove-req" title="Remove"><i class="ti tabler-x ti-xs"></i></button>' +
                '</div>'
            );
        });
    }

    function fillExtraList(items, idxRef) {
        var list = document.getElementById('extra-req-list');
        list.innerHTML = '';
        if (!items.length) {
            list.innerHTML = '<div class="text-center text-muted py-3"><i class="ti tabler-info-circle d-block mb-1" style="font-size:1.5rem"></i><small>No extra requirements yet</small></div>';
            return;
        }
        items.forEach(function(item) {
            var idx = idxRef.val++;
            list.insertAdjacentHTML('beforeend',
                '<div class="req-row">' +
                '<input type="text" name="system_requirements[extra][' + idx + '][key]" class="form-control form-control-sm" style="max-width:150px" value="' + (item.key || '') + '">' +
                '<input type="text" name="system_requirements[extra][' + idx + '][value]" class="form-control form-control-sm" value="' + (item.value || '') + '">' +
                '<button type="button" class="btn btn-icon btn-sm btn-label-danger btn-remove-req" title="Remove"><i class="ti tabler-x ti-xs"></i></button>' +
                '</div>'
            );
        });
    }

    // SEO AI
    var aiSeoBtn = document.getElementById('ai-seo');
    if (aiSeoBtn) {
        aiSeoBtn.addEventListener('click', function() {
            var short = document.getElementById('product-short-desc').value.trim();
            aiCall(this, 'seo', { short_description: short }).then(function(res) {
                var data = res.data;
                var metaTitle = document.getElementById('meta-title');
                var metaDesc = document.getElementById('meta-description');
                var metaKeywords = document.getElementById('meta-keywords');

                if (data.meta_title) {
                    metaTitle.value = data.meta_title;
                    metaTitle.dispatchEvent(new Event('input'));
                }
                if (data.meta_description) {
                    metaDesc.value = data.meta_description;
                    metaDesc.dispatchEvent(new Event('input'));
                }
                if (data.meta_keywords && typeof tagify !== 'undefined') {
                    tagify.removeAllTags();
                    var kws = data.meta_keywords.split(',').map(function(k) { return k.trim(); }).filter(Boolean);
                    tagify.addTags(kws);
                }

                Swal.fire({ icon: 'success', title: 'Done!', text: 'SEO metadata generated.', timer: 1500, showConfirmButton: false });
            });
        });
    }
    @endif
});
</script>
@endpush
