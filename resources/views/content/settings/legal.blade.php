@extends('layouts.app')
@section('title', 'Legal Pages')
@include('content.settings.partials.settings-layout')

@section('content')
<div class="row">
    <div class="col-lg-3">
        @include('content.settings.partials.settings-nav')
    </div>
    <div class="col-lg-9">
        <div class="settings-header d-flex align-items-center gap-3">
            <div class="settings-header-icon"><i class="ti tabler-gavel"></i></div>
            <div>
                <h4>Legal Pages</h4>
                <p>Configure legal page URLs or content paths</p>
            </div>
        </div>

        <form id="settingsForm">
            @csrf
            @method('PUT')

            <div class="card setting-card">
                <div class="card-header">
                    <h5><i class="ti tabler-file-text text-primary me-2"></i>Legal Documents</h5>
                    <p>Set the URL or slug for each legal page displayed on your site</p>
                </div>
                <div class="card-body row g-4">
                    <div class="col-md-6">
                        <label class="form-label">Terms of Service</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-file-certificate"></i></span>
                            <input type="text" name="legal[terms_of_service]" class="form-control" value="{{ $settings['terms_of_service'] ?? '' }}" placeholder="/terms-of-service">
                        </div>
                        <div class="form-label-description">URL or slug for this page</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Privacy Policy</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-shield-check"></i></span>
                            <input type="text" name="legal[privacy_policy]" class="form-control" value="{{ $settings['privacy_policy'] ?? '' }}" placeholder="/privacy-policy">
                        </div>
                        <div class="form-label-description">URL or slug for this page</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Refund Policy</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-receipt-refund"></i></span>
                            <input type="text" name="legal[refund_policy]" class="form-control" value="{{ $settings['refund_policy'] ?? '' }}" placeholder="/refund-policy">
                        </div>
                        <div class="form-label-description">URL or slug for this page</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Cookie Policy</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-cookie"></i></span>
                            <input type="text" name="legal[cookie_policy]" class="form-control" value="{{ $settings['cookie_policy'] ?? '' }}" placeholder="/cookie-policy">
                        </div>
                        <div class="form-label-description">URL or slug for this page</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Seller Agreement</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-handshake"></i></span>
                            <input type="text" name="legal[seller_agreement]" class="form-control" value="{{ $settings['seller_agreement'] ?? '' }}" placeholder="/seller-agreement">
                        </div>
                        <div class="form-label-description">URL or slug for this page</div>
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
saveSettings('settingsForm', '{{ route("settings.legal.update") }}');
</script>
@endpush
