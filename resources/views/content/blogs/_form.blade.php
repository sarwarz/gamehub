@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/tagify/tagify.css') }}" />
<style>
.blog-upload-zone {
    border: 2px dashed var(--bs-border-color);
    border-radius: 12px;
    padding: 2rem;
    text-align: center;
    cursor: pointer;
    transition: all .25s ease;
    background: var(--bs-body-bg);
    position: relative;
}
.blog-upload-zone:hover,
.blog-upload-zone.dragover {
    border-color: #696cff;
    background: rgba(105, 108, 255, .04);
}
.blog-upload-zone .upload-icon {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: rgba(105, 108, 255, .1);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: .75rem;
}
.blog-upload-zone .upload-icon i {
    font-size: 1.4rem;
    color: #696cff;
}
.blog-cover-preview {
    position: relative;
    display: inline-block;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 12px rgba(0,0,0,.1);
}
.blog-cover-preview img {
    max-height: 220px;
    border-radius: 10px;
    object-fit: cover;
}
.blog-cover-preview .remove-cover {
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
.blog-cover-preview .remove-cover:hover {
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
.sidebar-card .card-header { padding: .875rem 1.25rem; }
.sidebar-card .card-header h6 { font-size: .875rem; }
.sidebar-card .card-body { padding: 1rem 1.25rem; }
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
    $b = $blog ?? null;
    $isEdit = !is_null($b);
    $aiEnabled = \App\Services\AiContentService::isEnabled();
@endphp

{{-- Page Header --}}
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">
            <span class="text-muted fw-light">Blogs /</span>
            {{ $isEdit ? 'Edit Post' : 'New Post' }}
        </h4>
        <p class="text-muted mb-0">{{ $isEdit ? 'Update your blog post content and settings' : 'Create a new blog post for your site' }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('blogs.index') }}" class="btn btn-label-secondary">
            <i class="ti tabler-arrow-left ti-xs me-1"></i> Back
        </a>
        <button type="submit" class="btn btn-primary">
            <i class="ti tabler-device-floppy ti-xs me-1"></i> {{ $isEdit ? 'Update Post' : 'Publish Post' }}
        </button>
    </div>
</div>

<div class="row">
    {{-- ===== LEFT COLUMN ===== --}}
    <div class="col-12 col-lg-8">

        {{-- Blog Content --}}
        <div class="card mb-4">
            <div class="card-header pb-3">
                <h6 class="mb-0"><i class="ti tabler-article me-2 text-primary"></i>Blog Content</h6>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <label class="form-label" for="blog-title">Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" id="blog-title"
                           class="form-control @error('title') is-invalid @enderror"
                           value="{{ old('title', $b->title ?? '') }}"
                           placeholder="Enter blog post title" required>
                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label" for="blog-slug">Slug</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-link ti-xs"></i></span>
                            <input type="text" name="slug" id="blog-slug"
                                   class="form-control @error('slug') is-invalid @enderror"
                                   value="{{ old('slug', $b->slug ?? '') }}" placeholder="auto-generated-from-title">
                            @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="blog-category">Category <span class="text-danger">*</span></label>
                        <select name="blog_category_id" id="blog-category"
                                class="form-select select2 @error('blog_category_id') is-invalid @enderror" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" @selected(old('blog_category_id', $b->blog_category_id ?? '') == $cat->id)>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('blog_category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- Content Editor --}}
                <div class="mb-0">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label mb-0">Content <span class="text-danger">*</span></label>
                        @if($aiEnabled)
                        <button type="button" class="btn-ai" id="ai-blog-content" title="Write with AI">
                            <i class="ti tabler-sparkles ti-xs"></i> Write with AI
                        </button>
                        @endif
                    </div>
                    <input type="hidden" name="content" id="blog-content-input"
                           value="{{ old('content', $b->content ?? '') }}">
                    <div id="blog-content-toolbar">
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
                            <button class="ql-image"></button>
                            <button class="ql-blockquote"></button>
                            <button class="ql-code-block"></button>
                        </span>
                        <span class="ql-formats">
                            <button class="ql-clean"></button>
                        </span>
                    </div>
                    <div id="blog-content-editor" style="min-height: 300px;"></div>
                    @error('content') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        {{-- Featured Image --}}
        <div class="card mb-4">
            <div class="card-header pb-3">
                <h6 class="mb-0"><i class="ti tabler-photo me-2 text-primary"></i>Featured Image</h6>
            </div>
            <div class="card-body">
                <div id="cover-preview-area" class="mb-3" style="{{ ($isEdit && $b->featured_image) ? '' : 'display:none' }}">
                    <div class="blog-cover-preview">
                        <img id="cover-preview-img" src="{{ $isEdit && $b->featured_image ? asset($b->featured_image) : '' }}" alt="Cover">
                        <button type="button" class="remove-cover" id="remove-cover-btn" title="Remove">
                            <i class="ti tabler-x ti-xs"></i>
                        </button>
                    </div>
                </div>
                <div class="blog-upload-zone" id="cover-upload-zone" style="{{ ($isEdit && $b->featured_image) ? 'display:none' : '' }}">
                    <div class="upload-icon"><i class="ti tabler-cloud-upload"></i></div>
                    <p class="mb-1 fw-medium">Drop your featured image here or <span class="text-primary">browse</span></p>
                    <small class="text-muted">Recommended: 1200x630px. JPG, JPEG or PNG. Max 2MB</small>
                    <input type="file" name="featured_image" id="cover-image-input" class="d-none" accept="image/jpeg,image/png,image/jpg">
                </div>
                @error('featured_image') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>
        </div>

        {{-- SEO Settings --}}
        <div class="card mb-4">
            <div class="card-header pb-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="ti tabler-search me-2 text-primary"></i>SEO Settings</h6>
                    <div class="d-flex align-items-center gap-2">
                        @if($aiEnabled)
                        <button type="button" class="btn-ai" id="ai-blog-seo" title="Generate SEO with AI">
                            <i class="ti tabler-sparkles ti-xs"></i> Generate SEO
                        </button>
                        @endif
                        <span class="badge bg-label-secondary"><i class="ti tabler-robot ti-xs me-1"></i>Search Preview</span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="seo-preview mb-4" id="seo-preview-box">
                    <div class="seo-title" id="seo-preview-title">{{ $b->meta_title ?? $b->title ?? 'Blog Post Title' }}</div>
                    <div class="seo-url">{{ url('/') }}/blog/<span id="seo-preview-slug">{{ $b->slug ?? 'post-slug' }}</span></div>
                    <div class="seo-desc" id="seo-preview-desc">{{ $b->meta_description ?? 'Your blog post description will appear here in search results...' }}</div>
                </div>

                <div class="mb-4">
                    <label class="form-label" for="meta-title">Meta Title</label>
                    <input type="text" name="meta_title" id="meta-title"
                           class="form-control @error('meta_title') is-invalid @enderror"
                           value="{{ old('meta_title', $b->meta_title ?? '') }}"
                           placeholder="SEO title (recommended: 50-60 characters)" maxlength="60">
                    <span class="char-counter" id="meta-title-counter">0 / 60</span>
                    @error('meta_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label" for="meta-description">Meta Description</label>
                    <textarea name="meta_description" id="meta-description" rows="3"
                              class="form-control @error('meta_description') is-invalid @enderror"
                              placeholder="SEO description (recommended: 150-160 characters)" maxlength="160">{{ old('meta_description', $b->meta_description ?? '') }}</textarea>
                    <span class="char-counter" id="meta-desc-counter">0 / 160</span>
                    @error('meta_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="form-label" for="meta-keywords">Meta Keywords</label>
                    <input type="text" name="meta_keywords" id="meta-keywords"
                           class="form-control tagify-input @error('meta_keywords') is-invalid @enderror"
                           value="{{ old('meta_keywords', $b->meta_keywords ?? '') }}"
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
                    <label class="form-label">Status</label>
                    <div class="d-flex align-items-center gap-3 p-3 rounded-3" style="background: var(--bs-body-bg); border: 1px solid var(--bs-border-color);">
                        <input type="hidden" name="is_published" value="0">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" name="is_published" value="1"
                                   id="is-published" {{ old('is_published', $b->is_published ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="is-published">
                                <span class="publish-status-indicator {{ old('is_published', $b->is_published ?? true) ? 'active' : 'inactive' }}" id="status-dot"></span>
                                <span id="status-text">{{ old('is_published', $b->is_published ?? true) ? 'Published' : 'Draft' }}</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="published-at">Publish Date</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ti tabler-calendar ti-xs"></i></span>
                        <input type="datetime-local" name="published_at" id="published-at"
                               class="form-control"
                               value="{{ old('published_at', $isEdit && $b->published_at ? $b->published_at->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}">
                    </div>
                    <small class="text-muted">Schedule the post for a future date or set it to now.</small>
                </div>

                <div>
                    <label class="form-label" for="blog-position">Sort Order</label>
                    <input type="number" name="position" id="blog-position"
                           class="form-control" value="{{ old('position', $b->position ?? 0) }}" min="0">
                </div>
            </div>
        </div>

        {{-- Post Summary --}}
        @if($isEdit)
        <div class="card sidebar-card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="ti tabler-chart-bar me-2 text-primary"></i>Post Stats</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">Views</span>
                    <span class="fw-semibold">{{ number_format($b->views) }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">Comments</span>
                    <span class="fw-semibold">{{ $b->comments()->count() }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">Category</span>
                    <span class="badge bg-label-primary">{{ $b->category->name ?? '—' }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted">Created</span>
                    <span class="fw-semibold">{{ $b->created_at->format('M d, Y') }}</span>
                </div>
            </div>
        </div>
        @endif

        {{-- Quick Actions --}}
        <div class="card sidebar-card mb-4">
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti tabler-device-floppy ti-xs me-1"></i> {{ $isEdit ? 'Update Post' : 'Publish Post' }}
                    </button>
                    <a href="{{ route('blogs.index') }}" class="btn btn-label-secondary">
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

    // ============================
    // Quill Editor
    // ============================
    var contentEditor = new Quill('#blog-content-editor', {
        modules: { toolbar: '#blog-content-toolbar' },
        placeholder: 'Write your blog post content here...',
        theme: 'snow'
    });

    var contentInput = document.getElementById('blog-content-input');
    if (contentInput.value) {
        contentEditor.root.innerHTML = contentInput.value;
    }
    contentEditor.on('text-change', function() {
        contentInput.value = contentEditor.root.innerHTML;
    });

    // ============================
    // Auto Slug
    // ============================
    var titleInput = document.getElementById('blog-title');
    var slugInput = document.getElementById('blog-slug');
    var slugManuallyEdited = {{ $isEdit ? 'true' : 'false' }};

    slugInput.addEventListener('input', function() { slugManuallyEdited = true; });
    titleInput.addEventListener('input', function() {
        if (!slugManuallyEdited) {
            slugInput.value = this.value.toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .trim();
            document.getElementById('seo-preview-slug').textContent = slugInput.value || 'post-slug';
        }
    });

    // ============================
    // Character Counters
    // ============================
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

    // ============================
    // SEO Live Preview
    // ============================
    document.getElementById('meta-title').addEventListener('input', function() {
        document.getElementById('seo-preview-title').textContent = this.value || titleInput.value || 'Blog Post Title';
    });
    document.getElementById('meta-description').addEventListener('input', function() {
        document.getElementById('seo-preview-desc').textContent = this.value || 'Your blog post description will appear here in search results...';
    });
    titleInput.addEventListener('input', function() {
        var metaTitle = document.getElementById('meta-title');
        if (!metaTitle.value) {
            document.getElementById('seo-preview-title').textContent = this.value || 'Blog Post Title';
        }
    });

    // ============================
    // Tagify for Keywords
    // ============================
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

    // ============================
    // Cover Image Upload
    // ============================
    var coverZone = document.getElementById('cover-upload-zone');
    var coverInput = document.getElementById('cover-image-input');
    var coverPreview = document.getElementById('cover-preview-area');
    var coverImg = document.getElementById('cover-preview-img');
    var removeCoverBtn = document.getElementById('remove-cover-btn');

    coverZone.addEventListener('click', function() { coverInput.click(); });
    coverZone.addEventListener('dragover', function(e) { e.preventDefault(); coverZone.classList.add('dragover'); });
    coverZone.addEventListener('dragleave', function() { coverZone.classList.remove('dragover'); });
    coverZone.addEventListener('drop', function(e) {
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
        var reader = new FileReader();
        reader.onload = function(e) {
            coverImg.src = e.target.result;
            coverPreview.style.display = '';
            coverZone.style.display = 'none';
        };
        reader.readAsDataURL(file);
    }

    removeCoverBtn.addEventListener('click', function() {
        coverInput.value = '';
        coverPreview.style.display = 'none';
        coverZone.style.display = '';
    });

    // ============================
    // Publish Status Toggle
    // ============================
    var publishChk = document.getElementById('is-published');
    publishChk.addEventListener('change', function() {
        document.getElementById('status-dot').className = 'publish-status-indicator ' + (this.checked ? 'active' : 'inactive');
        document.getElementById('status-text').textContent = this.checked ? 'Published' : 'Draft';
    });

    // ============================
    // Sync Quill on form submit
    // ============================
    document.querySelector('form').addEventListener('submit', function() {
        contentInput.value = contentEditor.root.innerHTML;
    });

    @if($aiEnabled)
    // ============================
    // AI Content Generation
    // ============================
    var aiUrl = '{{ route("ai.generate") }}';
    var csrfToken = '{{ csrf_token() }}';

    function getTitle() {
        return document.getElementById('blog-title').value.trim();
    }

    function aiCall(btn, type, extraData) {
        var title = getTitle();
        if (!title) {
            Swal.fire({ icon: 'warning', title: 'Title Required', text: 'Please enter a blog post title first.', timer: 2500, showConfirmButton: false });
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

    // Blog Content AI
    var aiBlogContentBtn = document.getElementById('ai-blog-content');
    if (aiBlogContentBtn) {
        aiBlogContentBtn.addEventListener('click', function() {
            aiCall(this, 'blog_content').then(function(res) {
                contentEditor.root.innerHTML = res.data.content;
                contentInput.value = res.data.content;
                Swal.fire({ icon: 'success', title: 'Done!', text: 'Blog content generated.', timer: 1500, showConfirmButton: false });
            });
        });
    }

    // Blog SEO AI
    var aiBlogSeoBtn = document.getElementById('ai-blog-seo');
    if (aiBlogSeoBtn) {
        aiBlogSeoBtn.addEventListener('click', function() {
            aiCall(this, 'blog_seo').then(function(res) {
                var data = res.data;
                var metaTitle = document.getElementById('meta-title');
                var metaDesc = document.getElementById('meta-description');

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
