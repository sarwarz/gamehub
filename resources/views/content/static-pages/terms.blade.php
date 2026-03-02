@extends('layouts.app')
@section('title', 'Terms & Conditions')

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
        <h4 class="mb-1"><i class="ti tabler-file-certificate me-2"></i>Terms & Conditions</h4>
        <p class="text-muted mb-0">Manage the Terms & Conditions page content</p>
    </div>
</div>

<form action="{{ route('static-pages.update', 'terms') }}" method="POST">
@csrf @method('PUT')

<div class="row">
    <div class="col-lg-8">
        <div class="card section-card">
            <div class="card-header"><h6><i class="ti tabler-file-text text-primary"></i> Content</h6></div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Terms & Conditions Content</label>
                    <textarea name="content" class="form-control" rows="20">{{ $settings['content'] ?? '' }}</textarea>
                    <div class="form-hint">Supports HTML content. Use headings (h2, h3) to structure sections.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Last Updated Date</label>
                    <input type="date" name="last_updated" class="form-control" value="{{ $settings['last_updated'] ?? date('Y-m-d') }}">
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
                    <input type="text" name="title" class="form-control" value="{{ $settings['title'] ?? 'Terms & Conditions' }}">
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
    </div>
</div>

<div class="text-end mb-4">
    <button type="submit" class="btn btn-primary px-4"><i class="ti tabler-device-floppy me-1"></i> Save Changes</button>
</div>
</form>
@endsection
