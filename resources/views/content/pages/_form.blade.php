<div class="row">

    <!-- LEFT COLUMN -->
    <div class="col-lg-8">

        <!-- Page Content -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Page Content</h5>
            </div>

            <div class="card-body">

                <div class="mb-3">
                    <label class="form-label">Page Title</label>
                    <input type="text"
                           name="title"
                           class="form-control"
                           value="{{ old('title', $page->title ?? '') }}"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Slug</label>
                    <input type="text"
                           name="slug"
                           class="form-control"
                           value="{{ old('slug', $page->slug ?? '') }}"
                           placeholder="Auto-generated if empty">
                </div>

                <div class="mb-3">
                    <label class="form-label">Content</label>
                    <textarea name="content"
                              rows="8"
                              class="form-control">
                        {{ old('content', $page->content ?? '') }}
                    </textarea>
                </div>

            </div>
        </div>

        <!-- SEO -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">SEO Settings</h5>
            </div>

            <div class="card-body">

                <div class="mb-3">
                    <label class="form-label">Meta Title</label>
                    <input type="text"
                           name="meta_title"
                           class="form-control"
                           value="{{ old('meta_title', $page->meta_title ?? '') }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Meta Description</label>
                    <textarea name="meta_description"
                              rows="3"
                              class="form-control">{{ old('meta_description', $page->meta_description ?? '') }}</textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Meta Keywords</label>
                    <input type="text"
                           name="meta_keywords"
                           class="form-control"
                           value="{{ old('meta_keywords', $page->meta_keywords ?? '') }}">
                </div>

            </div>
        </div>
    </div>

    <!-- RIGHT COLUMN -->
    <div class="col-lg-4">

        <!-- Page Settings -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Page Settings</h5>
            </div>

            <div class="card-body">

                <div class="mb-3">
                    <label class="form-label">Featured Image</label>
                    <input type="file"
                           name="featured_image"
                           class="form-control">

                    @isset($page)
                        @if($page->featured_image)
                            <img src="{{ asset('storage/'.$page->featured_image) }}"
                                 class="img-fluid rounded mt-2">
                        @endif
                    @endisset
                </div>

                <div class="mb-3">
                    <label class="form-label">Position</label>
                    <input type="number"
                           name="position"
                           class="form-control"
                           value="{{ old('position', $page->position ?? 0) }}">
                </div>

                <hr>

                <div class="form-check form-switch mb-2">
                    <input class="form-check-input"
                           type="checkbox"
                           name="is_active"
                           value="1"
                           @checked(old('is_active', $page->is_active ?? true))>
                    <label class="form-check-label fw-semibold">
                        Page is active
                    </label>
                </div>

                <div class="form-check form-switch mb-2">
                    <input class="form-check-input"
                           type="checkbox"
                           name="show_in_header"
                           value="1"
                           @checked(old('show_in_header', $page->show_in_header ?? false))>
                    <label class="form-check-label">
                        Show in header menu
                    </label>
                </div>

                <div class="form-check form-switch">
                    <input class="form-check-input"
                           type="checkbox"
                           name="show_in_footer"
                           value="1"
                           @checked(old('show_in_footer', $page->show_in_footer ?? false))>
                    <label class="form-check-label">
                        Show in footer menu
                    </label>
                </div>

            </div>
        </div>
    </div>

</div>
