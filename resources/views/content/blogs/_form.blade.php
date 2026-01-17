<div class="row">

    <!-- LEFT COLUMN -->
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Blog Content</h5>
            </div>

            <div class="card-body">

                <!-- Category -->
                <div class="mb-3">
                    <label class="form-label">Category</label>
                    <select name="blog_category_id"
                            class="form-select"
                            required>
                        <option value="">Select Category</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}"
                                @selected(old('blog_category_id', $blog->blog_category_id ?? '') == $cat->id)>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">
                        Choose which category this blog post belongs to
                    </small>
                </div>

                <!-- Title -->
                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text"
                           name="title"
                           class="form-control"
                           value="{{ old('title', $blog->title ?? '') }}"
                           placeholder="Enter blog title"
                           required>
                    <small class="text-muted">
                        This will be displayed as the main heading of the blog
                    </small>
                </div>

                <!-- Content -->
                <div class="mb-3">
                    <label class="form-label">Content</label>
                    <textarea name="content"
                              class="form-control"
                              rows="8"
                              placeholder="Write your blog content here..."
                              required>{{ old('content', $blog->content ?? '') }}</textarea>
                    <small class="text-muted">
                        You can use HTML or a WYSIWYG editor here
                    </small>
                </div>

            </div>
        </div>
    </div>

    <!-- RIGHT COLUMN -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Settings</h5>
            </div>

            <div class="card-body">

                <!-- Featured Image -->
                <div class="mb-3">
                    <label class="form-label">Featured Image</label>
                    <input type="file"
                           name="featured_image"
                           class="form-control">
                    <small class="text-muted">
                        Recommended size: 1200×630px
                    </small>

                    @isset($blog)
                        @if($blog->featured_image)
                            <img src="{{ asset('storage/'.$blog->featured_image) }}"
                                 class="img-fluid rounded mt-2"
                                 alt="Featured Image">
                        @endif
                    @endisset
                </div>

                <hr>

                <!-- Publish Status -->
                <div class="form-check form-switch">
                    <input class="form-check-input"
                           type="checkbox"
                           name="is_published"
                           value="1"
                           @checked(old('is_published', $blog->is_published ?? true))>
                    <label class="form-check-label fw-semibold">
                        Publish this blog
                    </label>
                    <small class="text-muted d-block">
                        Unpublished blogs will not be visible on the website
                    </small>
                </div>

            </div>
        </div>
    </div>

</div>

<!-- ACTION BUTTONS -->
<div class="d-flex gap-2">
    <button type="submit" class="btn btn-primary">
        Save Blog
    </button>
    <a href="{{ route('blogs.index') }}" class="btn btn-label-secondary">
        Cancel
    </a>
</div>
