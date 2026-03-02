@extends('layouts.app')
@section('title', 'Reviews & Ratings Settings')
@include('content.settings.partials.settings-layout')

@section('content')
<div class="row">
    <div class="col-lg-3">
        @include('content.settings.partials.settings-nav')
    </div>
    <div class="col-lg-9">
        <div class="settings-header d-flex align-items-center gap-3">
            <div class="settings-header-icon"><i class="ti tabler-star"></i></div>
            <div>
                <h4>Reviews & Ratings</h4>
                <p>Configure product review system and moderation</p>
            </div>
        </div>

        <form id="settingsForm">
            @csrf
            @method('PUT')

            <div class="card setting-card">
                <div class="card-header">
                    <h5>Review System</h5>
                    <p>Enable reviews and configure moderation behavior</p>
                </div>
                <div class="card-body">
                    <div class="setting-toggle">
                        <div class="setting-toggle-info">
                            <h6>Enable Reviews</h6>
                            <p>Allow customers to leave reviews on purchased products</p>
                        </div>
                        <div class="form-check form-switch">
                            <input type="hidden" name="review[reviews_enabled]" value="0">
                            <input class="form-check-input" type="checkbox" name="review[reviews_enabled]" value="1" {{ ($settings['reviews_enabled'] ?? false) ? 'checked' : '' }}>
                        </div>
                    </div>
                    <div class="setting-toggle">
                        <div class="setting-toggle-info">
                            <h6>Auto-approve Reviews</h6>
                            <p>Publish reviews immediately without admin approval</p>
                        </div>
                        <div class="form-check form-switch">
                            <input type="hidden" name="review[auto_approve_reviews]" value="0">
                            <input class="form-check-input" type="checkbox" name="review[auto_approve_reviews]" value="1" {{ ($settings['auto_approve_reviews'] ?? false) ? 'checked' : '' }}>
                        </div>
                    </div>
                    <div class="setting-toggle">
                        <div class="setting-toggle-info">
                            <h6>Review Moderation</h6>
                            <p>Flag reviews containing specific keywords for manual review</p>
                        </div>
                        <div class="form-check form-switch">
                            <input type="hidden" name="review[review_moderation_enabled]" value="0">
                            <input class="form-check-input" type="checkbox" name="review[review_moderation_enabled]" value="1" {{ ($settings['review_moderation_enabled'] ?? false) ? 'checked' : '' }}>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card setting-card">
                <div class="card-header">
                    <h5>Review Requirements</h5>
                    <p>Set eligibility rules and content length constraints</p>
                </div>
                <div class="card-body">
                    <div class="setting-toggle">
                        <div class="setting-toggle-info">
                            <h6>Require Purchase for Review</h6>
                            <p>Only customers who purchased the product can leave a review</p>
                        </div>
                        <div class="form-check form-switch">
                            <input type="hidden" name="review[require_purchase_for_review]" value="0">
                            <input class="form-check-input" type="checkbox" name="review[require_purchase_for_review]" value="1" {{ ($settings['require_purchase_for_review'] ?? false) ? 'checked' : '' }}>
                        </div>
                    </div>
                    <div class="row g-4 mt-1">
                        <div class="col-md-6">
                            <label class="form-label">Minimum Review Length</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="ti tabler-text-resize"></i></span>
                                <input type="number" name="review[min_review_length]" class="form-control" value="{{ $settings['min_review_length'] ?? 10 }}" min="0">
                                <span class="input-group-text">characters</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Maximum Review Length</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="ti tabler-text-resize"></i></span>
                                <input type="number" name="review[max_review_length]" class="form-control" value="{{ $settings['max_review_length'] ?? 2000 }}" min="1">
                                <span class="input-group-text">characters</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card setting-card">
                <div class="card-header">
                    <h5>Review Features</h5>
                    <p>Enable additional capabilities for the review system</p>
                </div>
                <div class="card-body">
                    <div class="setting-toggle">
                        <div class="setting-toggle-info">
                            <h6>Allow Review Images</h6>
                            <p>Customers can upload images alongside their review</p>
                        </div>
                        <div class="form-check form-switch">
                            <input type="hidden" name="review[allow_review_images]" value="0">
                            <input class="form-check-input" type="checkbox" name="review[allow_review_images]" value="1" {{ ($settings['allow_review_images'] ?? false) ? 'checked' : '' }}>
                        </div>
                    </div>
                    <div class="setting-toggle">
                        <div class="setting-toggle-info">
                            <h6>Seller Can Reply</h6>
                            <p>Allow sellers to post public replies to customer reviews</p>
                        </div>
                        <div class="form-check form-switch">
                            <input type="hidden" name="review[seller_can_reply]" value="0">
                            <input class="form-check-input" type="checkbox" name="review[seller_can_reply]" value="1" {{ ($settings['seller_can_reply'] ?? false) ? 'checked' : '' }}>
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
saveSettings('settingsForm', '{{ route("settings.reviews.update") }}');
</script>
@endpush
