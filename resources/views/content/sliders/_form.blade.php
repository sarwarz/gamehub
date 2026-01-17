<div class="row">

    <!-- LEFT COLUMN -->
    <div class="col-lg-8">

        <!-- Slider Content -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Slider Content</h5>
            </div>

            <div class="card-body">

                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text"
                           name="title"
                           class="form-control"
                           value="{{ old('title', $slider->title ?? '') }}"
                           placeholder="Optional headline">
                </div>

                <div class="mb-3">
                    <label class="form-label">Subtitle</label>
                    <input type="text"
                           name="subtitle"
                           class="form-control"
                           value="{{ old('subtitle', $slider->subtitle ?? '') }}"
                           placeholder="Optional short description">
                </div>

                <div class="mb-3">
                    <label class="form-label">Linked Product (Optional)</label>
                    <select name="product_id" class="form-select">
                        <option value="">— No Product —</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}"
                                @selected(old('product_id', $slider->product_id ?? '') == $product->id)>
                                {{ $product->title }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">
                        If selected, product details will be used dynamically
                    </small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Button Text</label>
                    <input type="text"
                           name="button_text"
                           class="form-control"
                           value="{{ old('button_text', $slider->button_text ?? '') }}"
                           placeholder="View Product">
                </div>

                <div class="mb-3">
                    <label class="form-label">Button URL</label>
                    <input type="text"
                           name="button_url"
                           class="form-control"
                           value="{{ old('button_url', $slider->button_url ?? '') }}"
                           placeholder="Auto-filled from product if empty">
                </div>

            </div>
        </div>
    </div>

    <!-- RIGHT COLUMN -->
    <div class="col-lg-4">

        <!-- Slider Settings -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Slider Settings</h5>
            </div>

            <div class="card-body">

                <div class="mb-3">
                    <label class="form-label">Slider Image</label>
                    <input type="file"
                           name="image"
                           class="form-control"
                           {{ isset($slider) ? '' : 'required' }}>

                    @isset($slider)
                        <img src="{{ asset('storage/'.$slider->image) }}"
                             class="img-fluid rounded mt-2">
                    @endisset
                </div>

                <div class="mb-3">
                    <label class="form-label">Position</label>
                    <input type="number"
                           name="position"
                           class="form-control"
                           value="{{ old('position', $slider->position ?? 0) }}">
                </div>

                <hr>

                <div class="form-check form-switch">
                    <input class="form-check-input"
                           type="checkbox"
                           name="is_active"
                           value="1"
                           @checked(old('is_active', $slider->is_active ?? true))>
                    <label class="form-check-label fw-semibold">
                        Slider is active
                    </label>
                </div>

            </div>
        </div>
    </div>

</div>
