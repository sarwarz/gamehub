@extends('layouts.app')
@section('title', 'Contact Us Page')

@push('page-css')
<style>
.section-card { border: 1px solid #e7e7e8; border-radius: 10px; margin-bottom: 1.5rem; }
.section-card .card-header { background: transparent; border-bottom: 1px solid #f0f0f0; padding: 1rem 1.5rem; }
.section-card .card-header h6 { margin: 0; font-weight: 600; display: flex; align-items: center; gap: 8px; }
.section-card .card-body { padding: 1.25rem 1.5rem; }
.form-hint { font-size: .8rem; color: #a1acb8; margin-top: 4px; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="ti tabler-address-book me-2"></i>Contact Us Page</h4>
        <p class="text-muted mb-0">Manage the Contact Us page content and settings</p>
    </div>
</div>

<form action="{{ route('static-pages.update', 'contact') }}" method="POST" enctype="multipart/form-data">
@csrf @method('PUT')

<div class="row">
    <div class="col-lg-8">
        <div class="card section-card">
            <div class="card-header"><h6><i class="ti tabler-map-pin text-primary"></i> Contact Information</h6></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Hero Title</label>
                        <input type="text" name="hero_title" class="form-control" value="{{ $settings['hero_title'] ?? '' }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Hero Subtitle</label>
                        <input type="text" name="hero_subtitle" class="form-control" value="{{ $settings['hero_subtitle'] ?? '' }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Address</label>
                        <input type="text" name="address" class="form-control" value="{{ $settings['address'] ?? '' }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" value="{{ $settings['phone'] ?? '' }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ $settings['email'] ?? '' }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Working Hours</label>
                        <input type="text" name="working_hours" class="form-control" value="{{ $settings['working_hours'] ?? '' }}">
                    </div>
                    <div class="col-md-6">
                        <div class="form-check form-switch mt-4">
                            <input class="form-check-input" type="checkbox" name="form_enabled" value="1"
                                   {{ ($settings['form_enabled'] ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label">Enable Contact Form</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Google Maps Embed URL</label>
                        <input type="text" name="map_embed" class="form-control" value="{{ $settings['map_embed'] ?? '' }}"
                               placeholder="https://www.google.com/maps/embed?pb=...">
                        <div class="form-hint">Paste the iframe src URL from Google Maps embed code</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card section-card">
            <div class="card-header"><h6><i class="ti tabler-seo text-primary"></i> SEO</h6></div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Page Title</label>
                    <input type="text" name="title" class="form-control" value="{{ $settings['title'] ?? 'Contact Us' }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Meta Title</label>
                    <input type="text" name="meta_title" class="form-control" value="{{ $settings['meta_title'] ?? '' }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Meta Description</label>
                    <textarea name="meta_description" class="form-control" rows="3">{{ $settings['meta_description'] ?? '' }}</textarea>
                </div>
            </div>
        </div>

        <div class="card section-card">
            <div class="card-header"><h6><i class="ti tabler-mail text-primary"></i> Contact Messages</h6></div>
            <div class="card-body text-center">
                <a href="{{ route('contact-messages.index') }}" class="btn btn-label-primary">
                    <i class="ti tabler-mail me-1"></i> View Messages
                </a>
                <div class="form-hint mt-2">Manage incoming contact form submissions</div>
            </div>
        </div>
    </div>
</div>

<div class="text-end mb-4">
    <button type="submit" class="btn btn-primary px-4"><i class="ti tabler-device-floppy me-1"></i> Save Changes</button>
</div>
</form>
@endsection
