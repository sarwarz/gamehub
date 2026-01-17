<div class="row">

    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Category Information</h5>
            </div>

            <div class="card-body">

                <div class="mb-3">
                    <label class="form-label">Category Name</label>
                    <input type="text"
                           name="name"
                           class="form-control"
                           value="{{ old('name', $blogCategory->name ?? '') }}"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Slug</label>
                    <input type="text"
                           name="slug"
                           class="form-control"
                           value="{{ old('slug', $blogCategory->slug ?? '') }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description"
                              rows="3"
                              class="form-control">{{ old('description', $blogCategory->description ?? '') }}</textarea>
                </div>

            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Settings</h5>
            </div>

            <div class="card-body">

                <div class="mb-3">
                    <label class="form-label">Position</label>
                    <input type="number"
                           name="position"
                           class="form-control"
                           value="{{ old('position', $blogCategory->position ?? 0) }}">
                </div>

                <hr>

                <div class="form-check form-switch">
                    <input class="form-check-input"
                           type="checkbox"
                           name="is_active"
                           value="1"
                           @checked(old('is_active', $blogCategory->is_active ?? true))>
                    <label class="form-check-label fw-semibold">
                        Category is active
                    </label>
                </div>

            </div>
        </div>
    </div>

</div>
