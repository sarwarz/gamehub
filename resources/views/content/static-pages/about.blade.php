@extends('layouts.app')
@section('title', 'About Us Page')

@push('page-css')
<style>
.section-card { border: 1px solid #e7e7e8; border-radius: 10px; margin-bottom: 1.5rem; }
.section-card .card-header { background: transparent; border-bottom: 1px solid #f0f0f0; padding: 1rem 1.5rem; }
.section-card .card-header h6 { margin: 0; font-weight: 600; display: flex; align-items: center; gap: 8px; }
.section-card .card-body { padding: 1.25rem 1.5rem; }
.form-hint { font-size: .8rem; color: #a1acb8; margin-top: 4px; }
.stat-row { display: flex; gap: 8px; padding: 8px; background: #f8f7fa; border-radius: 8px; margin-bottom: 8px; align-items: center; }
.team-row { display: flex; gap: 8px; padding: 10px; background: #f8f7fa; border-radius: 8px; margin-bottom: 8px; align-items: end; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="ti tabler-info-circle me-2"></i>About Us Page</h4>
        <p class="text-muted mb-0">Manage the About Us page content</p>
    </div>
</div>

<form action="{{ route('static-pages.update', 'about') }}" method="POST" enctype="multipart/form-data" id="aboutForm">
@csrf @method('PUT')

<div class="row">
    <div class="col-lg-8">
        <div class="card section-card">
            <div class="card-header"><h6><i class="ti tabler-file-text text-primary"></i> Page Content</h6></div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Hero Title</label>
                    <input type="text" name="hero_title" class="form-control" value="{{ $settings['hero_title'] ?? '' }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Hero Subtitle</label>
                    <input type="text" name="hero_subtitle" class="form-control" value="{{ $settings['hero_subtitle'] ?? '' }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Content</label>
                    <textarea name="content" class="form-control" rows="12">{{ $settings['content'] ?? '' }}</textarea>
                    <div class="form-hint">Supports HTML content</div>
                </div>
            </div>
        </div>

        <div class="card section-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6><i class="ti tabler-chart-bar text-primary"></i> Stats</h6>
                <button type="button" class="btn btn-sm btn-label-primary" id="addStatBtn"><i class="ti tabler-plus me-1"></i> Add</button>
            </div>
            <div class="card-body">
                <div id="statsContainer">
                    @foreach(($settings['stats'] ?? []) as $stat)
                    <div class="stat-row">
                        <input type="text" class="form-control form-control-sm stat-value" placeholder="500+" value="{{ $stat['value'] ?? '' }}" style="max-width:100px;">
                        <input type="text" class="form-control form-control-sm stat-label flex-grow-1" placeholder="Label" value="{{ $stat['label'] ?? '' }}">
                        <button type="button" class="btn btn-icon btn-sm btn-label-danger remove-stat"><i class="ti tabler-x"></i></button>
                    </div>
                    @endforeach
                </div>
                <input type="hidden" name="stats" id="statsInput">
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card section-card">
            <div class="card-header"><h6><i class="ti tabler-seo text-primary"></i> SEO</h6></div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Page Title</label>
                    <input type="text" name="title" class="form-control" value="{{ $settings['title'] ?? 'About Us' }}">
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
            <div class="card-header"><h6><i class="ti tabler-photo text-primary"></i> Hero Image</h6></div>
            <div class="card-body text-center">
                @if(!empty($settings['hero_image']))
                    <img src="{{ $settings['hero_image'] }}" class="img-fluid rounded mb-2" style="max-height:120px;">
                @endif
                <input type="file" name="hero_image_file" class="form-control form-control-sm" accept="image/*">
            </div>
        </div>
    </div>
</div>

<div class="text-end mb-4">
    <button type="submit" class="btn btn-primary px-4"><i class="ti tabler-device-floppy me-1"></i> Save Changes</button>
</div>
</form>
@endsection

@push('page-js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sc = document.getElementById('statsContainer');
    document.getElementById('addStatBtn')?.addEventListener('click', () => {
        sc.insertAdjacentHTML('beforeend', `<div class="stat-row"><input type="text" class="form-control form-control-sm stat-value" placeholder="500+" style="max-width:100px;"><input type="text" class="form-control form-control-sm stat-label flex-grow-1" placeholder="Label"><button type="button" class="btn btn-icon btn-sm btn-label-danger remove-stat"><i class="ti tabler-x"></i></button></div>`);
    });
    sc?.addEventListener('click', e => { if (e.target.closest('.remove-stat')) e.target.closest('.stat-row').remove(); });

    document.getElementById('aboutForm').addEventListener('submit', function() {
        const stats = [];
        sc.querySelectorAll('.stat-row').forEach(r => {
            stats.push({ value: r.querySelector('.stat-value').value, label: r.querySelector('.stat-label').value });
        });
        document.getElementById('statsInput').value = JSON.stringify(stats);
    });
});
</script>
@endpush
