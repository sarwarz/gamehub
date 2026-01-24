@extends('layouts.app')

@section('title', 'Media Library')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}" />
<style>
/* ===============================
   Media Card
================================ */
.media-card {
    border: 1px solid #eee;
    border-radius: 8px;
    background: #fff;
    transition: all .2s ease;
}
.media-card:hover {
    box-shadow: 0 4px 18px rgba(0,0,0,.08);
    transform: translateY(-2px);
}
.media-thumb {
    height: 160px;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
}
.media-thumb img {
    max-width: 100%;
    max-height: 100%;
    object-fit: cover;
}
.media-meta {
    padding: 10px 12px;
    font-size: 13px;
}

/* ===============================
   Media Preview Modal (Pro)
================================ */
.media-preview-container {
    display: flex;
    height: 520px;
    background: #fff;
}
.media-preview-left {
    flex: 1;
    background: linear-gradient(135deg, #f8f9fa, #eef1f5);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 30px;
}
.media-preview-left img {
    max-width: 100%;
    max-height: 100%;
    border-radius: 10px;
    box-shadow: 0 10px 30px rgba(0,0,0,.15);
}
.media-preview-right {
    width: 340px;
    padding: 24px;
    border-left: 1px solid #eee;
}
.media-preview-info li {
    font-size: 14px;
    margin-bottom: 10px;
    color: #555;
}
.media-preview-actions {
    margin-top: 20px;
    display: flex;
    gap: 10px;
}
.modal-title {
    font-weight: 600;
}

/* ===== SweetAlert Copy Toast (Professional) ===== */
.media-copy-toast {
    border-radius: 12px !important;
    padding: 16px 20px !important;
    box-shadow: 0 20px 40px rgba(0,0,0,.35) !important;
    font-size: 15px;
    min-width: 260px;
}
/* ===== Media Preview Modal (Image-Style Layout) ===== */
.media-preview-container {
    display: flex;
    height: 520px;
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
}

.media-preview-left {
    flex: 1;
    background: #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 28px;
}

.media-preview-left img {
    max-width: 100%;
    max-height: 100%;
    border-radius: 14px;
    box-shadow: 0 12px 30px rgba(0,0,0,.18);
}

.media-preview-right {
    width: 360px;
    padding: 26px;
    background: #ffffff;
    border-left: 1px solid #e5e7eb;
}

.media-preview-info li {
    font-size: 14px;
    margin-bottom: 10px;
    color: #374151;
}

.media-preview-actions {
    margin-top: 24px;
    display: flex;
    gap: 12px;
}

</style>
@endpush

@section('content')
<div class="app-ecommerce">

    {{-- Success Message --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4">
            <i class="bx bx-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-6">
        <div>
            <h4 class="mb-1">Media Library</h4>
            <p class="mb-0 text-muted">
                Manage uploaded images, documents, videos, and assets
            </p>
        </div>

        @if(auth()->user()->hasPermission('media.create'))
            <a href="{{ route('media.create') }}" class="btn btn-primary">
                <i class="ti tabler-upload me-1"></i> Upload Media
            </a>
        @endif
    </div>

    {{-- Filters --}}
    <div class="card mb-6">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select">
                        <option value="">All Types</option>
                        @foreach (['image','video','audio','document','other'] as $type)
                            <option value="{{ $type }}" {{ request('type') === $type ? 'selected' : '' }}>
                                {{ ucfirst($type) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <button class="btn btn-secondary">
                        <i class="ti tabler-filter"></i> Filter
                    </button>
                    <a href="{{ route('media.index') }}" class="btn btn-light ms-2">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Media Grid --}}
    <div class="row g-4">
        @forelse ($media as $item)
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                <div class="media-card">

                    <div class="media-thumb">
                        @if($item->type === 'image')
                            <img src="{{ $item->url }}" alt="{{ $item->original_name }}">
                        @else
                            <i class="ti tabler-file text-muted" style="font-size:48px"></i>
                        @endif
                    </div>

                    <div class="media-meta">
                        <div class="fw-semibold text-truncate">
                            {{ $item->original_name }}
                        </div>
                        <div class="text-muted">
                            {{ strtoupper($item->extension) }} · {{ number_format($item->size / 1024, 1) }} KB
                        </div>
                    </div>

                    <div class="d-flex justify-content-between px-3 pb-3">
                        <button
                            class="btn btn-sm btn-light btn-media-preview"
                            data-bs-toggle="modal"
                            data-bs-target="#modalToggle"

                            data-url="{{ $item->url }}"
                            data-name="{{ $item->original_name }}"
                            data-type="{{ $item->type }}"
                            data-ext="{{ strtoupper($item->extension) }}"
                            data-size="{{ number_format($item->size / 1024, 1) }} KB"

                            data-mime="{{ $item->mime_type }}"
                            data-uploaded="{{ $item->created_at->format('F d, Y') }}"
                            data-uploader="{{ optional($item->mediable)->name ?? 'System' }}"
                            data-dimensions="{{ $item->meta['width'] ?? null }} x {{ $item->meta['height'] ?? null }}"
                        >
                            <i class="ti tabler-eye"></i>
                        </button>



                        @if(auth()->user()->hasPermission('media.delete'))
                            <form method="POST" action="{{ route('media.destroy', $item) }}">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger">
                                    <i class="ti tabler-trash"></i>
                                </button>
                            </form>
                        @endif
                    </div>

                </div>
            </div>
        @empty
            <div class="col-12 text-center py-6 text-muted">
                <i class="ti tabler-photo-off mb-2" style="font-size:48px"></i>
                <p>No media found</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if ($media->hasPages())
        <div class="mt-6">
            {{ $media->links() }}
        </div>
    @endif

</div>

{{-- Media Preview Modal --}}
<div class="modal fade" id="modalToggle" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">

            {{-- Header --}}
            <div class="modal-header">
                <h5 class="modal-title text-truncate" id="mediaPreviewName">
                    Media Preview
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            {{-- Body --}}
            <div class="modal-body p-0">
                <div class="media-preview-container">

                    {{-- LEFT: Preview --}}
                    <div class="media-preview-left" id="mediaPreviewContent">
                        {{-- Image injected here --}}
                    </div>

                    {{-- RIGHT: Info --}}
                    <div class="media-preview-right">

                        <ul class="list-unstyled media-preview-info">
                            <li><strong>Uploaded on:</strong> <span id="mediaUploadedAt"></span></li>
                            <li><strong>Uploaded by:</strong> <span id="mediaUploadedBy"></span></li>
                            <li><strong>Uploaded to:</strong> <span id="mediaUploadedTo"></span></li>

                            <hr class="my-2">

                            <li><strong>File name:</strong> <span id="mediaFileName"></span></li>
                            <li><strong>File type:</strong> <span id="mediaMimeType"></span></li>
                            <li><strong>File size:</strong> <span id="mediaFileSize"></span></li>
                            <li><strong>Dimensions:</strong> <span id="mediaDimensions"></span></li>
                        </ul>

                        <div class="media-preview-actions">
                            <button class="btn btn-outline-primary btn-sm" id="copyMediaUrl">
                                <i class="ti tabler-copy"></i> Copy URL
                            </button>
                            <a href="#" target="_blank" id="downloadMedia" class="btn btn-outline-secondary btn-sm">
                                <i class="ti tabler-download"></i> Download
                            </a>
                        </div>
                    </div>


                </div>
            </div>

        </div>
    </div>
</div>
{{-- Copy URL Toast --}}
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100;">
    <div id="copyToast" class="toast align-items-center text-bg-dark border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                <i class="ti tabler-check me-2 text-success"></i>
                Media URL copied
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>


@endsection

@push('page-js')
<script>
$(function () {

    let mediaUrl = '';

    $('.btn-media-preview').on('click', function () {

        mediaUrl = $(this).data('url'); // IMPORTANT

        const data = {
            name: $(this).data('name'),
            uploaded: $(this).data('uploaded'),
            uploader: $(this).data('uploader'),
            uploadedTo: $(this).data('uploaded-to') || '—',
            mime: $(this).data('mime'),
            size: $(this).data('size'),
            dimensions: $(this).data('dimensions') || '—',
            type: $(this).data('type')
        };

        $('#mediaPreviewName').text(data.name);
        $('#mediaUploadedAt').text(data.uploaded);
        $('#mediaUploadedBy').text(data.uploader);
        $('#mediaUploadedTo').text(data.uploadedTo);

        $('#mediaFileName').text(data.name);
        $('#mediaMimeType').text(data.mime);
        $('#mediaFileSize').text(data.size);
        $('#mediaDimensions').text(data.dimensions);

        $('#downloadMedia').attr('href', mediaUrl);

        if (data.type === 'image') {
            $('#mediaPreviewContent').html(`<img src="${mediaUrl}" alt="">`);
        } else {
            $('#mediaPreviewContent').html(
                `<i class="ti tabler-file" style="font-size:72px;color:#9ca3af"></i>`
            );
        }
    });

    /* Copy URL (Bootstrap Toast) */
    $('#copyMediaUrl').on('click', function () {

        if (!mediaUrl) return;

        navigator.clipboard.writeText(mediaUrl);

        const toastEl = document.getElementById('copyToast');
        const toast = new bootstrap.Toast(toastEl, { delay: 2000 });
        toast.show();
    });

});
</script>

@endpush
