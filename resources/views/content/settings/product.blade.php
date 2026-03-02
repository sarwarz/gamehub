@extends('layouts.app')
@section('title', 'Product Settings')
@include('content.settings.partials.settings-layout')

@section('content')
<div class="row">
    <div class="col-lg-3">
        @include('content.settings.partials.settings-nav')
    </div>
    <div class="col-lg-9">
        <div class="settings-header d-flex align-items-center gap-3">
            <div class="settings-header-icon"><i class="ti tabler-package"></i></div>
            <div>
                <h4>Product Settings</h4>
                <p>Configure product listings, images, and approval workflow</p>
            </div>
        </div>

        <form id="settingsForm">
            @csrf
            @method('PUT')

            <div class="card setting-card">
                <div class="card-header">
                    <h5>Image Settings</h5>
                    <p>Control upload limits and accepted image formats</p>
                </div>
                <div class="card-body row g-4">
                    <div class="col-md-6">
                        <label class="form-label">Max Images Per Product</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-photo"></i></span>
                            <input type="number" name="product[max_images_per_product]" class="form-control" value="{{ $settings['max_images_per_product'] ?? 10 }}" min="1">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Max Image Size (MB)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-file-upload"></i></span>
                            <input type="number" step="0.5" name="product[max_image_size_mb]" class="form-control" value="{{ $settings['max_image_size_mb'] ?? 2 }}" min="0.5">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Allowed Image Types</label>
                        @php $allowedTypes = (array)($settings['allowed_image_types'] ?? ['jpg', 'jpeg', 'png', 'webp']); @endphp
                        <div class="d-flex flex-wrap gap-3 mt-1">
                            @foreach(['jpg', 'jpeg', 'png', 'webp'] as $type)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="product[allowed_image_types][]" value="{{ $type }}" id="img-type-{{ $type }}" {{ in_array($type, $allowedTypes) ? 'checked' : '' }}>
                                <label class="form-check-label" for="img-type-{{ $type }}">{{ strtoupper($type) }}</label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="card setting-card">
                <div class="card-header">
                    <h5>Product Limits</h5>
                    <p>Set maximum offers allowed per product listing</p>
                </div>
                <div class="card-body row g-4">
                    <div class="col-md-6">
                        <label class="form-label">Max Offers Per Product</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-tag"></i></span>
                            <input type="number" name="product[max_offers_per_product]" class="form-control" value="{{ $settings['max_offers_per_product'] ?? 20 }}" min="1">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card setting-card">
                <div class="card-header">
                    <h5>Content Requirements</h5>
                    <p>Enforce content quality standards for product listings</p>
                </div>
                <div class="card-body">
                    <div class="setting-toggle">
                        <div class="setting-toggle-info">
                            <h6>Require Product Description</h6>
                            <p>Sellers must provide a product description before publishing</p>
                        </div>
                        <div class="form-check form-switch">
                            <input type="hidden" name="product[require_product_description]" value="0">
                            <input class="form-check-input" type="checkbox" name="product[require_product_description]" value="1" {{ ($settings['require_product_description'] ?? false) ? 'checked' : '' }}>
                        </div>
                    </div>
                    <div class="row g-4 mt-1">
                        <div class="col-md-6">
                            <label class="form-label">Minimum Description Length</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="ti tabler-text-resize"></i></span>
                                <input type="number" name="product[min_description_length]" class="form-control" value="{{ $settings['min_description_length'] ?? 50 }}" min="0">
                                <span class="input-group-text">characters</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card setting-card">
                <div class="card-header">
                    <h5>Approval & Requests</h5>
                    <p>Control product approval workflow and user requests</p>
                </div>
                <div class="card-body">
                    <div class="setting-toggle">
                        <div class="setting-toggle-info">
                            <h6>Auto-approve Products</h6>
                            <p>Automatically approve new products without admin review</p>
                        </div>
                        <div class="form-check form-switch">
                            <input type="hidden" name="product[auto_approve_products]" value="0">
                            <input class="form-check-input" type="checkbox" name="product[auto_approve_products]" value="1" {{ ($settings['auto_approve_products'] ?? false) ? 'checked' : '' }}>
                        </div>
                    </div>
                    <div class="setting-toggle">
                        <div class="setting-toggle-info">
                            <h6>Allow Product Requests</h6>
                            <p>Users can request products that are not currently listed</p>
                        </div>
                        <div class="form-check form-switch">
                            <input type="hidden" name="product[allow_product_requests]" value="0">
                            <input class="form-check-input" type="checkbox" name="product[allow_product_requests]" value="1" {{ ($settings['allow_product_requests'] ?? false) ? 'checked' : '' }}>
                        </div>
                    </div>
                </div>
            </div>

            <div class="save-bar">
                <button type="button" class="btn btn-label-secondary" onclick="location.reload()">Discard</button>
                <button type="submit" class="btn btn-primary"><i class="ti tabler-device-floppy me-1"></i> Save Changes</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('page-js')
<script>
saveSettings('settingsForm', '{{ route("settings.products.update") }}');
</script>
@endpush
