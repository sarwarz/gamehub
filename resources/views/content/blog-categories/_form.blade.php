@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/tagify/tagify.css') }}" />
<style>
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
.sidebar-card .card-header { padding: .875rem 1.25rem; }
.sidebar-card .card-header h6 { font-size: .875rem; }
.sidebar-card .card-body { padding: 1rem 1.25rem; }
.status-indicator {
    width: 10px; height: 10px;
    border-radius: 50%;
    display: inline-block;
    margin-right: 6px;
}
.status-indicator.active { background: #28c76f; }
.status-indicator.inactive { background: #ea5455; }
</style>
@endpush

@php
    $c = $blogCategory ?? null;
    $isEdit = !is_null($c);
@endphp

{{-- Page Header --}}
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">
            <span class="text-muted fw-light">Blog Categories /</span>
            {{ $isEdit ? 'Edit Category' : 'New Category' }}
        </h4>
        <p class="text-muted mb-0">{{ $isEdit ? 'Update category details and SEO settings' : 'Create a new category to organize blog posts' }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('blog-categories.index') }}" class="btn btn-label-secondary">
            <i class="ti tabler-arrow-left ti-xs me-1"></i> Back
        </a>
        <button type="submit" class="btn btn-primary">
            <i class="ti tabler-device-floppy ti-xs me-1"></i> {{ $isEdit ? 'Update' : 'Create' }}
        </button>
    </div>
</div>

<div class="row">
    {{-- ===== LEFT COLUMN ===== --}}
    <div class="col-12 col-lg-8">

        {{-- Category Information --}}
        <div class="card mb-4">
            <div class="card-header pb-3">
                <h6 class="mb-0"><i class="ti tabler-category me-2 text-primary"></i>Category Information</h6>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <label class="form-label" for="cat-name">Category Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="cat-name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $c->name ?? '') }}"
                           placeholder="Enter category name" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label" for="cat-slug">Slug</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ti tabler-link ti-xs"></i></span>
                        <input type="text" name="slug" id="cat-slug"
                               class="form-control @error('slug') is-invalid @enderror"
                               value="{{ old('slug', $c->slug ?? '') }}" placeholder="auto-generated-from-name">
                        @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mb-0">
                    <label class="form-label" for="cat-desc">Description</label>
                    <textarea name="description" id="cat-desc" rows="4"
                              class="form-control @error('description') is-invalid @enderror"
                              placeholder="Brief description of this category">{{ old('description', $c->description ?? '') }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        {{-- SEO Settings --}}
        <div class="card mb-4">
            <div class="card-header pb-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="ti tabler-search me-2 text-primary"></i>SEO Settings</h6>
                    <span class="badge bg-label-secondary"><i class="ti tabler-robot ti-xs me-1"></i>Search Preview</span>
                </div>
            </div>
            <div class="card-body">
                <div class="seo-preview mb-4" id="seo-preview-box">
                    <div class="seo-title" id="seo-preview-title">{{ $c->meta_title ?? $c->name ?? 'Category Name' }}</div>
                    <div class="seo-url">{{ url('/') }}/blog/category/<span id="seo-preview-slug">{{ $c->slug ?? 'category-slug' }}</span></div>
                    <div class="seo-desc" id="seo-preview-desc">{{ $c->meta_description ?? 'Category description will appear here in search results...' }}</div>
                </div>

                <div class="mb-4">
                    <label class="form-label" for="meta-title">Meta Title</label>
                    <input type="text" name="meta_title" id="meta-title"
                           class="form-control @error('meta_title') is-invalid @enderror"
                           value="{{ old('meta_title', $c->meta_title ?? '') }}"
                           placeholder="SEO title (recommended: 50-60 characters)" maxlength="60">
                    <span class="char-counter" id="meta-title-counter">0 / 60</span>
                    @error('meta_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label" for="meta-description">Meta Description</label>
                    <textarea name="meta_description" id="meta-description" rows="3"
                              class="form-control @error('meta_description') is-invalid @enderror"
                              placeholder="SEO description (recommended: 150-160 characters)" maxlength="160">{{ old('meta_description', $c->meta_description ?? '') }}</textarea>
                    <span class="char-counter" id="meta-desc-counter">0 / 160</span>
                    @error('meta_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="form-label" for="meta-keywords">Meta Keywords</label>
                    <input type="text" name="meta_keywords" id="meta-keywords"
                           class="form-control tagify-input @error('meta_keywords') is-invalid @enderror"
                           value="{{ old('meta_keywords', $c->meta_keywords ?? '') }}"
                           placeholder="Type and press Enter to add keywords">
                    @error('meta_keywords') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- ===== RIGHT COLUMN ===== --}}
    <div class="col-12 col-lg-4">

        {{-- Status --}}
        <div class="card sidebar-card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="ti tabler-settings me-2 text-primary"></i>Settings</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <div class="d-flex align-items-center gap-3 p-3 rounded-3" style="background: var(--bs-body-bg); border: 1px solid var(--bs-border-color);">
                        <input type="hidden" name="is_active" value="0">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                   id="is-active" {{ old('is_active', $c->is_active ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="is-active">
                                <span class="status-indicator {{ old('is_active', $c->is_active ?? true) ? 'active' : 'inactive' }}" id="status-dot"></span>
                                <span id="status-text">{{ old('is_active', $c->is_active ?? true) ? 'Active' : 'Inactive' }}</span>
                            </label>
                        </div>
                    </div>
                    <small class="text-muted mt-1 d-block">Inactive categories won't be visible on the site.</small>
                </div>

                <div>
                    <label class="form-label" for="cat-position">Sort Order</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ti tabler-sort-ascending-numbers ti-xs"></i></span>
                        <input type="number" name="position" id="cat-position"
                               class="form-control" value="{{ old('position', $c->position ?? 0) }}" min="0">
                    </div>
                    <small class="text-muted">Lower numbers appear first.</small>
                </div>
            </div>
        </div>

        @if($isEdit)
        {{-- Category Stats --}}
        <div class="card sidebar-card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="ti tabler-chart-bar me-2 text-primary"></i>Stats</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">Blog Posts</span>
                    <span class="badge bg-label-primary">{{ \App\Models\Blog::where('blog_category_id', $c->id)->count() }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted">Created</span>
                    <span class="fw-semibold">{{ $c->created_at->format('M d, Y') }}</span>
                </div>
            </div>
        </div>
        @endif

        {{-- Quick Actions --}}
        <div class="card sidebar-card mb-4">
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti tabler-device-floppy ti-xs me-1"></i> {{ $isEdit ? 'Update Category' : 'Create Category' }}
                    </button>
                    <a href="{{ route('blog-categories.index') }}" class="btn btn-label-secondary">
                        <i class="ti tabler-x ti-xs me-1"></i> Cancel
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('page-js')
<script src="{{ asset('assets/vendor/libs/tagify/tagify.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {

    // Auto Slug
    var nameInput = document.getElementById('cat-name');
    var slugInput = document.getElementById('cat-slug');
    var slugManuallyEdited = {{ $isEdit ? 'true' : 'false' }};

    slugInput.addEventListener('input', function() { slugManuallyEdited = true; });
    nameInput.addEventListener('input', function() {
        if (!slugManuallyEdited) {
            slugInput.value = this.value.toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .trim();
            document.getElementById('seo-preview-slug').textContent = slugInput.value || 'category-slug';
        }
    });

    // Character Counters
    function setupCounter(inputId, counterId, max) {
        var inp = document.getElementById(inputId);
        var ctr = document.getElementById(counterId);
        function update() {
            var len = inp.value.length;
            ctr.textContent = len + ' / ' + max;
            ctr.className = 'char-counter' + (len > max ? ' text-danger' : len > max * 0.85 ? ' text-warning' : ' text-muted');
        }
        inp.addEventListener('input', update);
        update();
    }
    setupCounter('meta-title', 'meta-title-counter', 60);
    setupCounter('meta-description', 'meta-desc-counter', 160);

    // SEO Live Preview
    document.getElementById('meta-title').addEventListener('input', function() {
        document.getElementById('seo-preview-title').textContent = this.value || nameInput.value || 'Category Name';
    });
    document.getElementById('meta-description').addEventListener('input', function() {
        document.getElementById('seo-preview-desc').textContent = this.value || 'Category description will appear here in search results...';
    });
    nameInput.addEventListener('input', function() {
        if (!document.getElementById('meta-title').value) {
            document.getElementById('seo-preview-title').textContent = this.value || 'Category Name';
        }
    });

    // Tagify
    var tagInput = document.getElementById('meta-keywords');
    var tagify = new Tagify(tagInput, {
        delimiters: ',',
        originalInputValueFormat: function(vals) { return vals.map(function(v) { return v.value; }).join(','); }
    });
    if (tagInput.value && !tagInput.value.startsWith('[')) {
        var existing = tagInput.value.split(',').map(function(v) { return { value: v.trim() }; }).filter(function(v) { return v.value; });
        tagify.addTags(existing);
    }
    document.querySelector('form').addEventListener('submit', function() {
        tagInput.value = tagify.value.map(function(v) { return v.value; }).join(',');
    });

    // Status Toggle
    var activeChk = document.getElementById('is-active');
    activeChk.addEventListener('change', function() {
        document.getElementById('status-dot').className = 'status-indicator ' + (this.checked ? 'active' : 'inactive');
        document.getElementById('status-text').textContent = this.checked ? 'Active' : 'Inactive';
    });
});
</script>
@endpush
