@extends('layouts.app')
@section('title', 'Create Product')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/dropzone/dropzone.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/flatpickr/flatpickr.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/tagify/tagify.css') }}" />
@endpush

@section('content')
<div class="app-ecommerce">
    <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data">
        @csrf

        <!-- Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-6 row-gap-4">
            <div class="d-flex flex-column justify-content-center">
                <h4 class="mb-1">Add a new Product</h4>
                <p class="mb-0">Fill in the details to create a product</p>
            </div>
            <div class="d-flex align-content-center flex-wrap gap-4">
                <div class="d-flex gap-4">
                    <a href="{{ route('products.index') }}" class="btn btn-label-secondary">Discard</a>
                    <button type="submit" name="status" value="draft" class="btn btn-label-primary">Save draft</button>
                </div>
                <button type="submit" name="status" value="active" class="btn btn-primary">Publish product</button>
            </div>
        </div>

        <div class="row">
            <!-- Left Column -->
            <div class="col-12 col-lg-8">
                <!-- Product Information -->
                <div class="card mb-6">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Product Information</h5>
                    </div>
                    <div class="card-body">
                        <!-- Title -->
                        <div class="mb-6">
                            <label class="form-label">Name</label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                   value="{{ old('title') }}" placeholder="Product title" required>
                            @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <!-- SKU & Slug -->
                        <div class="row mb-6">
                            <div class="col">
                                <label class="form-label">SKU</label>
                                <input type="text" name="sku" class="form-control @error('sku') is-invalid @enderror"
                                       value="{{ old('sku') }}" placeholder="SKU">
                                @error('sku') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col">
                                <label class="form-label">Slug</label>
                                <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror"
                                       value="{{ old('slug') }}" placeholder="product-slug">
                                @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-6">
                            <label class="mb-1">Description</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                      rows="5" placeholder="Enter product description">{{ old('description') }}</textarea>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <!-- Media -->
                <div class="card mb-6">
                    <div class="card-header d-flex justify-content-between align-items-center"><h5 class="mb-0 card-title">Product Image</h5></div>
                    <div class="card-body">
                        <label>Cover Image</label> <input type="file" name="cover_image" class="form-control mb-3 @error('cover_image') is-invalid @enderror" /> @error('cover_image')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror <label>Gallery (Multiple images)</label> <input type="file" name="gallery[]" class="form-control @error('gallery') is-invalid @enderror" multiple /> @error('gallery')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="card mb-6">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">System Requirements</h5>

                        <!-- Copy Checkbox -->
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="copy-min-to-rec">
                            <label class="form-check-label" for="copy-min-to-rec">
                                Use minimum requirements as recommended
                            </label>
                        </div>
                    </div>

                    <div class="card-body">

                        <!-- Minimum Requirements -->
                        <h6 class="mb-3">Minimum Requirements</h6>
                        <div class="form-repeater" id="minimum-req">
                            <div data-repeater-list="system_requirements[minimum]">
                                <div data-repeater-item class="row mb-3 align-items-center">
                                    <div class="col-md-4">
                                        <input type="text" name="key" class="form-control" value="os" readonly>
                                    </div>
                                    <div class="col-md-7">
                                        <input type="text" name="value" class="form-control" placeholder="Windows 10 or later (64-bit)">
                                    </div>
                                    <div class="col-md-1">
                                        <button data-repeater-delete type="button" class="btn btn-sm btn-danger">X</button>
                                    </div>
                                </div>

                                <div data-repeater-item class="row mb-3 align-items-center">
                                    <div class="col-md-4"><input type="text" name="key" value="processor" class="form-control" readonly></div>
                                    <div class="col-md-7"><input type="text" name="value" class="form-control" placeholder="i5-3570K 3.4 GHz 4 Core"></div>
                                    <div class="col-md-1"><button data-repeater-delete type="button" class="btn btn-sm btn-danger">X</button></div>
                                </div>

                                <div data-repeater-item class="row mb-3 align-items-center">
                                    <div class="col-md-4"><input type="text" name="key" value="memory" class="form-control" readonly></div>
                                    <div class="col-md-7"><input type="text" name="value" class="form-control" placeholder="16 GB RAM"></div>
                                    <div class="col-md-1"><button data-repeater-delete type="button" class="btn btn-sm btn-danger">X</button></div>
                                </div>

                                <div data-repeater-item class="row mb-3 align-items-center">
                                    <div class="col-md-4"><input type="text" name="key" value="graphics" class="form-control" readonly></div>
                                    <div class="col-md-7"><input type="text" name="value" class="form-control" placeholder="GeForce GTX 1050 (2GB)"></div>
                                    <div class="col-md-1"><button data-repeater-delete type="button" class="btn btn-sm btn-danger">X</button></div>
                                </div>

                                <div data-repeater-item class="row mb-3 align-items-center">
                                    <div class="col-md-4"><input type="text" name="key" value="storage" class="form-control" readonly></div>
                                    <div class="col-md-7"><input type="text" name="value" class="form-control" placeholder="40 GB available space"></div>
                                    <div class="col-md-1"><button data-repeater-delete type="button" class="btn btn-sm btn-danger">X</button></div>
                                </div>
                            </div>
                        </div>

                        <!-- Recommended Requirements -->
                        <h6 class="mt-4 mb-3">Recommended Requirements</h6>
                        <div class="form-repeater" id="recommended-req">
                            <div data-repeater-list="system_requirements[recommended]">
                                <div data-repeater-item class="row mb-3 align-items-center">
                                    <div class="col-md-4"><input type="text" name="key" value="os" class="form-control" readonly></div>
                                    <div class="col-md-7"><input type="text" name="value" class="form-control" placeholder="Windows 10 or later (64-bit)"></div>
                                    <div class="col-md-1"><button data-repeater-delete type="button" class="btn btn-sm btn-danger">X</button></div>
                                </div>

                                <div data-repeater-item class="row mb-3 align-items-center">
                                    <div class="col-md-4"><input type="text" name="key" value="processor" class="form-control" readonly></div>
                                    <div class="col-md-7"><input type="text" name="value" class="form-control" placeholder="i9-9900K 3.6 GHz 8 Core"></div>
                                    <div class="col-md-1"><button data-repeater-delete type="button" class="btn btn-sm btn-danger">X</button></div>
                                </div>

                                <div data-repeater-item class="row mb-3 align-items-center">
                                    <div class="col-md-4"><input type="text" name="key" value="memory" class="form-control" readonly></div>
                                    <div class="col-md-7"><input type="text" name="value" class="form-control" placeholder="32 GB RAM"></div>
                                    <div class="col-md-1"><button data-repeater-delete type="button" class="btn btn-sm btn-danger">X</button></div>
                                </div>

                                <div data-repeater-item class="row mb-3 align-items-center">
                                    <div class="col-md-4"><input type="text" name="key" value="graphics" class="form-control" readonly></div>
                                    <div class="col-md-7"><input type="text" name="value" class="form-control" placeholder="GeForce RTX 2070"></div>
                                    <div class="col-md-1"><button data-repeater-delete type="button" class="btn btn-sm btn-danger">X</button></div>
                                </div>

                                <div data-repeater-item class="row mb-3 align-items-center">
                                    <div class="col-md-4"><input type="text" name="key" value="storage" class="form-control" readonly></div>
                                    <div class="col-md-7"><input type="text" name="value" class="form-control" placeholder="40 GB available space"></div>
                                    <div class="col-md-1"><button data-repeater-delete type="button" class="btn btn-sm btn-danger">X</button></div>
                                </div>
                            </div>
                        </div>
                        <h6 class="mt-4 mb-3">Extra Requirements</h6>
                        <div class="form-repeater">
                            <div data-repeater-list="system_requirements[extra]">
                                <div data-repeater-item class="row mb-3 align-items-center">
                                    <div class="col-md-4">
                                        <input type="text" name="key" class="form-control" placeholder="e.g. DirectX">
                                    </div>
                                    <div class="col-md-7">
                                        <input type="text" name="value" class="form-control" placeholder="DirectX 12">
                                    </div>
                                    <div class="col-md-1">
                                        <button data-repeater-delete type="button" class="btn btn-sm btn-danger">X</button>
                                    </div>
                                </div>
                            </div>
                            <button data-repeater-create type="button" class="btn btn-sm btn-primary mt-2">+ Add Extra Requirement</button>
                        </div>
                    </div>
                </div>



                <!-- Extra Attributes -->
                <div class="card mb-6">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Extra Attributes</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-repeater">
                            <div data-repeater-list="attributes">
                                @if(old('attributes'))
                                    @foreach(old('attributes') as $attr)
                                        <div data-repeater-item class="row mb-3">
                                            <div class="col-md-5">
                                                <input type="text" name="key" value="{{ $attr['key'] ?? '' }}"
                                                    class="form-control @error('attributes.*.key') is-invalid @enderror"
                                                    placeholder="Attribute Key">
                                                @error('attributes.*.key') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                            <div class="col-md-5">
                                                <input type="text" name="value" value="{{ $attr['value'] ?? '' }}"
                                                    class="form-control @error('attributes.*.value') is-invalid @enderror"
                                                    placeholder="Attribute Value">
                                                @error('attributes.*.value') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                            <div class="col-md-2 d-flex align-items-center">
                                                <button type="button" data-repeater-delete class="btn btn-danger btn-sm">Remove</button>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div data-repeater-item class="row mb-3">
                                        <div class="col-md-5">
                                            <input type="text" name="key" class="form-control" placeholder="Attribute Key">
                                        </div>
                                        <div class="col-md-5">
                                            <input type="text" name="value" class="form-control" placeholder="Attribute Value">
                                        </div>
                                        <div class="col-md-2 d-flex align-items-center">
                                            <button type="button" data-repeater-delete class="btn btn-danger btn-sm">Remove</button>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <button type="button" data-repeater-create class="btn btn-sm btn-primary mt-2">
                                + Add Attribute
                            </button>
                        </div>
                    </div>
                </div>

                <!-- SEO Section -->
                <div class="card mb-6">
                    <div class="card-header">
                        <h5 class="card-title mb-0">SEO Settings</h5>
                    </div>
                    <div class="card-body">
                        <!-- Meta Title -->
                        <div class="mb-4">
                            <label class="form-label">Meta Title</label>
                            <input type="text" name="meta_title" 
                                class="form-control @error('meta_title') is-invalid @enderror"
                                value="{{ old('meta_title') }}" 
                                placeholder="Enter SEO title (max 60 characters)">
                            @error('meta_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <!-- Meta Description -->
                        <div class="mb-4">
                            <label class="form-label">Meta Description</label>
                            <textarea name="meta_description" rows="3"
                                    class="form-control @error('meta_description') is-invalid @enderror"
                                    placeholder="Enter SEO description (max 160 characters)">{{ old('meta_description') }}</textarea>
                            @error('meta_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <!-- Meta Keywords -->
                        <div class="mb-4">
                            <label class="form-label">Meta Keywords</label>
                            <input type="text" name="meta_keywords"
                                class="form-control tagify @error('meta_keywords') is-invalid @enderror"
                                value="{{ old('meta_keywords') }}"
                                placeholder="Enter keywords, separated by commas">
                            @error('meta_keywords') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <small class="text-muted">Example: gaming, windows 11, steam key</small>
                        </div>
                    </div>
                </div>


            </div>

            <!-- Right Column -->
            <div class="col-12 col-lg-4">

                <!-- Category Card -->
                <div class="card mb-6">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Category</h5>
                    </div>
                    <div class="card-body">
                        <select name="category_ids[]" class="form-select select2 @error('category_ids') is-invalid @enderror" multiple>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}"
                                    {{ in_array($cat->id, old('category_ids', isset($product) ? $product->categories->pluck('id')->toArray() : [])) ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_ids') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <!-- Platform Card -->
                <div class="card mb-6">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Platform</h5>
                    </div>
                    <div class="card-body">
                        <select name="platform_ids[]" class="form-select select2 @error('platform_ids') is-invalid @enderror" multiple>
                            @foreach($platforms as $p)
                                <option value="{{ $p->id }}"
                                    {{ in_array($p->id, old('platform_ids', isset($product) ? $product->platforms->pluck('id')->toArray() : [])) ? 'selected' : '' }}>
                                    {{ $p->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('platform_ids') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <!-- Product Type Card -->
                <div class="card mb-6">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Product Type</h5>
                    </div>
                    <div class="card-body">
                        <select name="type_ids[]" class="form-select select2 @error('type_ids') is-invalid @enderror" multiple>
                            @foreach($types as $t)
                                <option value="{{ $t->id }}"
                                    {{ in_array($t->id, old('type_ids', isset($product) ? $product->types->pluck('id')->toArray() : [])) ? 'selected' : '' }}>
                                    {{ $t->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('type_ids') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <!-- Region Card -->
                <div class="card mb-6">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Region</h5>
                    </div>
                    <div class="card-body">
                        <select name="region_ids[]" class="form-select select2 @error('region_ids') is-invalid @enderror" multiple>
                            @foreach($regions as $r)
                                <option value="{{ $r->id }}"
                                    {{ in_array($r->id, old('region_ids', isset($product) ? $product->regions->pluck('id')->toArray() : [])) ? 'selected' : '' }}>
                                    {{ $r->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('region_ids') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <!-- Language Card -->
                <div class="card mb-6">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Language</h5>
                    </div>
                    <div class="card-body">
                        <select name="language_ids[]" class="form-select select2 @error('language_ids') is-invalid @enderror" multiple>
                            @foreach($languages as $lang)
                                <option value="{{ $lang->id }}"
                                    {{ in_array($lang->id, old('language_ids', isset($product) ? $product->languages->pluck('id')->toArray() : [])) ? 'selected' : '' }}>
                                    {{ $lang->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('language_ids') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <!-- Works On Card -->
                <div class="card mb-6">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Works On</h5>
                    </div>
                    <div class="card-body">
                        <select name="works_on_ids[]" class="form-select select2 @error('works_on_ids') is-invalid @enderror" multiple>
                            @foreach($workson as $w)
                                <option value="{{ $w->id }}"
                                    {{ in_array($w->id, old('works_on_ids', isset($product) ? $product->worksOn->pluck('id')->toArray() : [])) ? 'selected' : '' }}>
                                    {{ $w->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('works_on_ids') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>


                <!-- Developer Card -->
                <div class="card mb-6">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Developer</h5>
                    </div>
                    <div class="card-body">
                        <select name="developer_id" class="form-select select2 @error('developer_id') is-invalid @enderror">
                            <option value="">-- Select Developer --</option>
                            @foreach($developers as $d)
                                <option value="{{ $d->id }}" {{ old('developer_id') == $d->id ? 'selected' : '' }}>
                                    {{ $d->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('developer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <!-- Publisher Card -->
                <div class="card mb-6">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Publisher</h5>
                    </div>
                    <div class="card-body">
                        <select name="publisher_id" class="form-select select2 @error('publisher_id') is-invalid @enderror">
                            <option value="">-- Select Publisher --</option>
                            @foreach($publishers as $pub)
                                <option value="{{ $pub->id }}" {{ old('publisher_id') == $pub->id ? 'selected' : '' }}>
                                    {{ $pub->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('publisher_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <!-- Delivery Type Card -->
                <div class="card mb-6">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Delivery Type</h5>
                    </div>
                    <div class="card-body">
                        <select name="delivery_type" class="form-select select2 @error('delivery_type') is-invalid @enderror">
                            <option value="instant" {{ old('delivery_type', 'instant') == 'instant' ? 'selected' : '' }}>Instant</option>
                            <option value="manual" {{ old('delivery_type') == 'manual' ? 'selected' : '' }}>Manual</option>
                            <option value="email" {{ old('delivery_type') == 'email' ? 'selected' : '' }}>Email</option>
                            <option value="link" {{ old('delivery_type') == 'link' ? 'selected' : '' }}>External Link</option>
                        </select>
                        @error('delivery_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <!-- Status & Featured Card -->
                <div class="card mb-6">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Status & Featured</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select select2 @error('status') is-invalid @enderror">
                                <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="archived" {{ old('status') == 'archived' ? 'selected' : '' }}>Archived</option>
                            </select>
                            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="is_featured"
                                {{ old('is_featured') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_featured">Featured Product</label>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </form>
</div>
@endsection

@push('page-js')
<script src="{{ asset('assets/vendor/libs/dropzone/dropzone.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/jquery-repeater/jquery-repeater.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/tagify/tagify.js') }}"></script>
<script>
    $(document).ready(function() {
        let input = document.querySelector('input[name=meta_keywords]');
        let tagify = new Tagify(input);

        // Before form submit, convert JSON to comma-separated string
        $('form').on('submit', function () {
            try {
                let tags = JSON.parse(input.value);
                if (Array.isArray(tags)) {
                    input.value = tags.map(item => item.value).join(',');
                }
            } catch (e) {
                // already string, do nothing
            }
        });
    });


    $(document).ready(function () {
        $('.form-repeater').repeater({
            initEmpty: false,
            defaultValues: { 'key': '', 'value': '' },
            show: function () {
                $(this).slideDown();
            },
            hide: function (deleteElement) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $(this).slideUp(deleteElement);
                        Swal.fire('Deleted!', 'The attribute has been removed.', 'success');
                    }
                });
            }
        });

        // Fix for duplicate row issue (ensures only one row per click)
        $(document).on('click', '[data-repeater-create]', function(e) {
            e.preventDefault();
            $(this).closest('.form-repeater').find('[data-repeater-list]').repeaterVal();
        });
    });
</script>



<script>
$(function () {
  const $chk = $('#copy-min-to-rec');
  const $min = $('#minimum-req');
  const $rec = $('#recommended-req');

  function snapshotMinimum() {
    const map = {};
    $min.find('[data-repeater-item]').each(function () {
      const key = ($(this).find('input[name$="[key]"], input[name="key"]').val() || '')
                    .trim().toLowerCase();
      const val = $(this).find('input[name$="[value]"], input[name="value"]').val() || '';
      if (key) map[key] = val;
    });
    return map;
  }

  function applyToRecommended(map) {
    $rec.find('[data-repeater-item]').each(function () {
      const key = ($(this).find('input[name$="[key]"], input[name="key"]').val() || '')
                    .trim().toLowerCase();
      if (key && Object.prototype.hasOwnProperty.call(map, key)) {
        $(this).find('input[name$="[value]"], input[name="value"]').val(map[key]);
      }
    });
  }

  // Copy once when checkbox is toggled on; clear when off
  $chk.on('change', function () {
    if (this.checked) {
      applyToRecommended(snapshotMinimum());
      // Optional: lock recommended while synced
      $rec.find('input[name$="[value]"], input[name="value"]').prop('readonly', true);
    } else {
      $rec.find('input[name$="[value]"], input[name="value"]').val('').prop('readonly', false);
    }
  });

  // Live sync while typing in minimum (only when checkbox is checked)
  $min.on('input', 'input[name$="[value]"], input[name="value"]', function () {
    if ($chk.is(':checked')) {
      applyToRecommended(snapshotMinimum());
    }
  });
});
</script>




@endpush
