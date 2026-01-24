@extends('layouts.app')

@section('title', 'Upload Media')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}" />
<style>
/* ===============================
   Upload Box
================================ */
.media-upload-box {
    border: 2px dashed #d1d5db;
    border-radius: 12px;
    padding: 40px;
    text-align: center;
    background: #fafafa;
    transition: all .2s ease;
    cursor: pointer;
}
.media-upload-box:hover {
    background: #f3f4f6;
    border-color: #6366f1;
}
.media-upload-box.dragover {
    background: #eef2ff;
    border-color: #4f46e5;
}

.media-upload-icon {
    font-size: 48px;
    color: #6366f1;
}

/* ===============================
   Preview List
================================ */
.media-preview-list {
    margin-top: 24px;
}
.media-preview-item {
    display: flex;
    align-items: center;
    padding: 10px 14px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    margin-bottom: 10px;
    background: #fff;
}
.media-preview-thumb {
    width: 48px;
    height: 48px;
    background: #f3f4f6;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    margin-right: 12px;
}
.media-preview-thumb img {
    max-width: 100%;
    max-height: 100%;
}
.media-preview-meta {
    flex: 1;
    font-size: 14px;
}


.media-preview-remove {
    font-size: 18px;
    margin-left: 12px;
    color: #ef4444;
    cursor: pointer;
}
.media-preview-remove:hover {
    color: #dc2626;
}

</style>
@endpush

@section('content')
<div class="app-ecommerce">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-6">
        <div>
            <h4 class="mb-1">Upload Media</h4>
            <p class="mb-0 text-muted">
                Upload images, documents, videos, and other files
            </p>
        </div>

        <a href="{{ route('media.index') }}" class="btn btn-light">
            <i class="ti tabler-arrow-left me-1"></i> Back to Library
        </a>
    </div>

    {{-- Upload Card --}}
    <div class="card">
        <div class="card-body">

            <form id="mediaUploadForm" action="{{ route('media.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- Drop Zone --}}
                <label for="mediaFiles" class="media-upload-box w-100" id="mediaDropZone">
                    <div class="media-upload-icon">
                        <i class="ti tabler-cloud-upload"></i>
                    </div>
                    <h5 class="mt-3 mb-1">Drop files here or click to upload</h5>
                    <p class="text-muted mb-0">
                        You can upload multiple files at once (Max 50MB each)
                    </p>

                    <input type="file"
                           id="mediaFiles"
                           name="files[]"
                           multiple
                           hidden>
                </label>

                {{-- Validation Error --}}
                @error('files')
                    <div class="text-danger mt-2">{{ $message }}</div>
                @enderror

                {{-- Preview --}}
                <div class="media-preview-list" id="mediaPreviewList"></div>

                {{-- Actions --}}
                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti tabler-upload me-1"></i> Upload Files
                    </button>
                </div>

            </form>

        </div>
    </div>

</div>
@endsection

@push('page-js')
<script>
$(function () {

    const fileInput   = $('#mediaFiles');
    const previewList = $('#mediaPreviewList');
    const dropZone    = $('#mediaDropZone');

    // Global file store
    let fileStore = new DataTransfer();

    /* ===============================
       Drag & Drop UX
    ================================ */
    dropZone.on('dragover', function (e) {
        e.preventDefault();
        $(this).addClass('dragover');
    });

    dropZone.on('dragleave drop', function () {
        $(this).removeClass('dragover');
    });

    dropZone.on('drop', function (e) {
        e.preventDefault();
        $(this).removeClass('dragover');
        addFiles(e.originalEvent.dataTransfer.files);
    });

    fileInput.on('change', function () {
        addFiles(this.files);
    });

    /* ===============================
       Add Files (append)
    ================================ */
    function addFiles(files) {
        Array.from(files).forEach(file => {

            // Prevent duplicates
            const exists = Array.from(fileStore.files).some(f =>
                f.name === file.name &&
                f.size === file.size &&
                f.lastModified === file.lastModified
            );

            if (!exists) {
                fileStore.items.add(file);
            }
        });

        syncInput();
        renderPreview();
    }

    /* ===============================
       Remove File
    ================================ */
    previewList.on('click', '.media-preview-remove', function () {
        const index = $(this).data('index');

        // Rebuild DataTransfer without removed file
        const newStore = new DataTransfer();

        Array.from(fileStore.files).forEach((file, i) => {
            if (i !== index) {
                newStore.items.add(file);
            }
        });

        fileStore = newStore;
        syncInput();
        renderPreview();
    });

    /* ===============================
       Sync input files
    ================================ */
    function syncInput() {
        fileInput[0].files = fileStore.files;
    }

    /* ===============================
       Preview Renderer
    ================================ */
    function renderPreview() {
        previewList.html('');

        Array.from(fileStore.files).forEach((file, index) => {

            const item = $(`
                <div class="media-preview-item">
                    <div class="media-preview-thumb"></div>
                    <div class="media-preview-meta">
                        <div class="fw-semibold">${file.name}</div>
                        <div class="text-muted">${(file.size / 1024).toFixed(1)} KB</div>
                    </div>
                    <div class="media-preview-remove" title="Remove">
                        <i class="ti tabler-x"></i>
                    </div>
                </div>
            `);

            item.find('.media-preview-remove').attr('data-index', index);

            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = e => {
                    item.find('.media-preview-thumb').html(
                        `<img src="${e.target.result}">`
                    );
                };
                reader.readAsDataURL(file);
            } else {
                item.find('.media-preview-thumb').html(
                    `<i class="ti tabler-file text-muted"></i>`
                );
            }

            previewList.append(item);
        });
    }

    /* ===============================
       Submit Feedback
    ================================ */
    $('#mediaUploadForm').on('submit', function () {
        Swal.fire({
            title: 'Uploading...',
            text: 'Please wait while your files are uploaded',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });
    });

});
</script>

@endpush
