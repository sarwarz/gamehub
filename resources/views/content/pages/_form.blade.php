@php $isEdit = isset($page); @endphp

<div class="row">

    {{-- LEFT COLUMN --}}
    <div class="col-xl-8 col-lg-7">

        {{-- Page Content --}}
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="icon-base ti tabler-file-text me-2"></i>Page Content</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label" for="page-title">Page Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" id="page-title"
                        class="form-control @error('title') is-invalid @enderror"
                        value="{{ old('title', $page->title ?? '') }}" required
                        placeholder="Enter page title">
                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="page-slug">
                        Slug
                        <small class="text-muted">(auto-generated from title if empty)</small>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="icon-base ti tabler-link"></i></span>
                        <input type="text" name="slug" id="page-slug"
                            class="form-control @error('slug') is-invalid @enderror"
                            value="{{ old('slug', $page->slug ?? '') }}"
                            placeholder="custom-url-slug">
                    </div>
                    @error('slug') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    <small class="text-muted mt-1 d-block" id="slug-preview">
                        @if($isEdit)
                            URL: {{ url('/page/' . ($page->slug ?? '')) }}
                        @endif
                    </small>
                </div>

                <div class="mb-0">
                    <label class="form-label">Content</label>
                    <input type="hidden" name="content" id="page-content-input"
                        value="{{ old('content', $page->content ?? '') }}">
                    <div id="page-content-toolbar">
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
                            <button class="ql-blockquote"></button>
                            <button class="ql-code-block"></button>
                        </span>
                        <span class="ql-formats">
                            <button class="ql-link"></button>
                            <button class="ql-image"></button>
                        </span>
                        <span class="ql-formats">
                            <select class="ql-align"></select>
                        </span>
                        <span class="ql-formats">
                            <button class="ql-clean"></button>
                        </span>
                    </div>
                    <div id="page-content-editor" style="min-height:250px">{!! old('content', $page->content ?? '') !!}</div>
                    @error('content') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        {{-- SEO Settings --}}
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center">
                <h5 class="card-title mb-0"><i class="icon-base ti tabler-search me-2"></i>SEO Settings</h5>
                <span class="badge bg-label-secondary ms-auto">Optional</span>
            </div>
            <div class="card-body">
                {{-- SEO Preview --}}
                <div class="border rounded p-3 mb-4 bg-light">
                    <small class="text-muted d-block mb-1">Search Preview</small>
                    <div id="seo-preview">
                        <h6 class="text-primary mb-0" id="seo-preview-title">{{ old('meta_title', $page->meta_title ?? 'Page Title') }}</h6>
                        <small class="text-success d-block">{{ url('/page/') }}/<span id="seo-preview-slug">{{ old('slug', $page->slug ?? 'page-slug') }}</span></small>
                        <small class="text-muted" id="seo-preview-desc">{{ old('meta_description', $page->meta_description ?? 'Page description will appear here...') }}</small>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <label class="form-label">Meta Title</label>
                        <small class="text-muted"><span id="meta-title-count">{{ strlen(old('meta_title', $page->meta_title ?? '')) }}</span>/60</small>
                    </div>
                    <input type="text" name="meta_title" id="meta-title"
                        class="form-control @error('meta_title') is-invalid @enderror"
                        value="{{ old('meta_title', $page->meta_title ?? '') }}"
                        maxlength="255" placeholder="SEO optimized title">
                    @error('meta_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <label class="form-label">Meta Description</label>
                        <small class="text-muted"><span id="meta-desc-count">{{ strlen(old('meta_description', $page->meta_description ?? '')) }}</span>/160</small>
                    </div>
                    <textarea name="meta_description" id="meta-description" rows="3"
                        class="form-control @error('meta_description') is-invalid @enderror"
                        maxlength="255" placeholder="Brief description for search engines">{{ old('meta_description', $page->meta_description ?? '') }}</textarea>
                    @error('meta_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-0">
                    <label class="form-label">Meta Keywords</label>
                    <input type="text" name="meta_keywords"
                        class="form-control @error('meta_keywords') is-invalid @enderror"
                        value="{{ old('meta_keywords', $page->meta_keywords ?? '') }}"
                        placeholder="keyword1, keyword2, keyword3">
                    @error('meta_keywords') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- RIGHT COLUMN --}}
    <div class="col-xl-4 col-lg-5">

        {{-- Publish Settings --}}
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="icon-base ti tabler-settings me-2"></i>Publish</h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                    <div>
                        <h6 class="mb-0">Status</h6>
                        <small class="text-muted">Page is visible to visitors</small>
                    </div>
                    <div class="form-check form-switch mb-0">
                        <input type="checkbox" name="is_active" value="1"
                            class="form-check-input" style="width:3rem;height:1.5rem;"
                            id="switch-active"
                            @checked(old('is_active', $page->is_active ?? true))>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                    <div>
                        <h6 class="mb-0">Header Menu</h6>
                        <small class="text-muted">Show link in navigation</small>
                    </div>
                    <div class="form-check form-switch mb-0">
                        <input type="checkbox" name="show_in_header" value="1"
                            class="form-check-input" style="width:3rem;height:1.5rem;"
                            @checked(old('show_in_header', $page->show_in_header ?? false))>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="mb-0">Footer Menu</h6>
                        <small class="text-muted">Show link in footer</small>
                    </div>
                    <div class="form-check form-switch mb-0">
                        <input type="checkbox" name="show_in_footer" value="1"
                            class="form-check-input" style="width:3rem;height:1.5rem;"
                            @checked(old('show_in_footer', $page->show_in_footer ?? false))>
                    </div>
                </div>
            </div>
        </div>

        {{-- Featured Image --}}
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="icon-base ti tabler-photo me-2"></i>Featured Image</h5>
            </div>
            <div class="card-body">
                <div class="border rounded p-3 text-center position-relative" id="image-drop-zone" style="cursor:pointer; min-height:160px;">
                    @if($isEdit && $page->featured_image)
                        <img src="{{ asset('storage/'.$page->featured_image) }}" id="image-preview"
                            class="img-fluid rounded" style="max-height:200px;">
                        <div class="mt-2">
                            <small class="text-muted">Click or drag to replace</small>
                        </div>
                    @else
                        <div id="image-placeholder" class="py-4">
                            <i class="icon-base ti tabler-cloud-upload icon-xl text-muted mb-2 d-block"></i>
                            <span class="text-muted">Click or drag image here</span>
                            <br><small class="text-muted">Max 2MB — JPG, PNG, WebP</small>
                        </div>
                        <img src="" id="image-preview" class="img-fluid rounded d-none" style="max-height:200px;">
                    @endif
                    <input type="file" name="featured_image" id="featured-image-input"
                        class="position-absolute top-0 start-0 w-100 h-100 opacity-0" style="cursor:pointer;"
                        accept="image/*">
                </div>
                @error('featured_image') <div class="invalid-feedback d-block mt-1">{{ $message }}</div> @enderror
            </div>
        </div>

        {{-- Sort Order --}}
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="icon-base ti tabler-sort-ascending me-2"></i>Sort Order</h5>
            </div>
            <div class="card-body">
                <label class="form-label">Position</label>
                <input type="number" name="position"
                    class="form-control @error('position') is-invalid @enderror"
                    value="{{ old('position', $page->position ?? 0) }}" min="0"
                    placeholder="0 = default">
                <small class="text-muted">Lower numbers appear first in menus</small>
                @error('position') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        @if($isEdit)
        {{-- Page Info --}}
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="icon-base ti tabler-info-circle me-2"></i>Page Info</h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Created</span>
                        <span class="fw-medium">{{ $page->created_at->format('M d, Y') }}</span>
                    </li>
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Last Updated</span>
                        <span class="fw-medium">{{ $page->updated_at->diffForHumans() }}</span>
                    </li>
                    <li class="d-flex justify-content-between py-2">
                        <span class="text-muted">Status</span>
                        @if($page->is_active)
                            <span class="badge bg-label-success">Published</span>
                        @else
                            <span class="badge bg-label-warning">Draft</span>
                        @endif
                    </li>
                </ul>
            </div>
        </div>
        @endif
    </div>
</div>
