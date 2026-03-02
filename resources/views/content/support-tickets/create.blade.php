@extends('layouts.app')

@section('title', 'Create Support Ticket')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-support-tickets.css') }}">
<style>
    .select2-container--default .select2-selection--single { height: 38px !important; border-color: #dbdade !important; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 38px !important; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 38px !important; }
    .char-count { font-size: .75rem; color: #a1acb8; }
    .attachment-preview { display: flex; flex-wrap: wrap; gap: .5rem; margin-top: .5rem; }
    .attachment-preview .att-item { position: relative; background: #f5f5f9; border-radius: 6px; padding: 6px 30px 6px 10px; font-size: .8rem; }
    .attachment-preview .att-item .att-remove { position: absolute; right: 6px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #ea5455; font-size: 1rem; }
</style>
@endpush

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1"><i class="ti tabler-plus me-2"></i>Create Support Ticket</h4>
        <p class="text-muted mb-0">Create a new ticket on behalf of a customer</p>
    </div>
    <a href="{{ route('support-tickets.index') }}" class="btn btn-label-secondary">
        <i class="ti tabler-arrow-left me-1"></i> Back to Tickets
    </a>
</div>

<form action="{{ route('support-tickets.store') }}" method="POST" enctype="multipart/form-data" id="create-ticket-form">
    @csrf

    <div class="row">
        {{-- Left Column: Main Info --}}
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="ti tabler-info-circle me-2"></i>Ticket Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <label for="user_id" class="form-label">Customer <span class="text-danger">*</span></label>
                        <select name="user_id" id="user_id" class="form-select select2 @error('user_id') is-invalid @enderror" required>
                            <option value="">Select Customer</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}" {{ old('user_id') == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }} ({{ $c->email }})
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                        <input type="text" name="subject" id="subject" class="form-control @error('subject') is-invalid @enderror" value="{{ old('subject') }}" placeholder="Brief description of the issue" maxlength="255" required>
                        <div class="d-flex justify-content-between mt-1">
                            @error('subject')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <span class="char-count ms-auto"><span id="subject-count">0</span>/255</span>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="department" class="form-label">Department <span class="text-danger">*</span></label>
                            <select name="department" id="department" class="form-select @error('department') is-invalid @enderror" required>
                                <option value="">Select Department</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->slug }}" {{ old('department') == $dept->slug ? 'selected' : '' }}>
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('department')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="priority" class="form-label">Priority <span class="text-danger">*</span></label>
                            <select name="priority" id="priority" class="form-select @error('priority') is-invalid @enderror" required>
                                @foreach(\App\Models\SupportTicket::PRIORITIES as $p)
                                    <option value="{{ $p }}" {{ old('priority', 'medium') == $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                                @endforeach
                            </select>
                            @error('priority')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="message" class="form-label">Initial Message <span class="text-danger">*</span></label>
                        <textarea name="message" id="message" rows="8" class="form-control @error('message') is-invalid @enderror" placeholder="Describe the issue in detail..." required>{{ old('message') }}</textarea>
                        <div class="d-flex justify-content-between mt-1">
                            @error('message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <span class="char-count ms-auto"><span id="msg-count">0</span>/10,000</span>
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label">Attachments</label>
                        <div class="border rounded p-3 text-center" id="drop-zone" style="border-style:dashed !important; cursor:pointer;">
                            <i class="ti tabler-cloud-upload fs-2 text-muted"></i>
                            <p class="mb-0 text-muted small">Drag & drop files here or <a href="javascript:void(0);" id="browse-link">browse</a></p>
                            <p class="mb-0 text-muted" style="font-size:.7rem;">Max 10MB per file. JPG, PNG, GIF, PDF, DOC, ZIP, TXT</p>
                            <input type="file" name="attachments[]" id="file-input" class="d-none" multiple accept=".jpg,.jpeg,.png,.gif,.webp,.bmp,.pdf,.doc,.docx,.zip,.rar,.txt">
                        </div>
                        <div class="attachment-preview" id="attachment-preview"></div>
                        @error('attachments.*')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: Assignment & Options --}}
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="ti tabler-settings me-2"></i>Options</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <label for="status" class="form-label">Initial Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="open" {{ old('status', 'open') == 'open' ? 'selected' : '' }}>Open</option>
                            <option value="awaiting_customer" {{ old('status') == 'awaiting_customer' ? 'selected' : '' }}>Awaiting Customer</option>
                            <option value="awaiting_seller" {{ old('status') == 'awaiting_seller' ? 'selected' : '' }}>Awaiting Seller</option>
                            <option value="awaiting_admin" {{ old('status') == 'awaiting_admin' ? 'selected' : '' }}>Awaiting Admin</option>
                            <option value="on_hold" {{ old('status') == 'on_hold' ? 'selected' : '' }}>On Hold</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="assigned_admin_id" class="form-label">Assign to Admin</label>
                        <select name="assigned_admin_id" id="assigned_admin_id" class="form-select">
                            <option value="">Unassigned</option>
                            @foreach($admins as $admin)
                                <option value="{{ $admin->id }}" {{ old('assigned_admin_id') == $admin->id ? 'selected' : '' }}>
                                    {{ $admin->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="seller_id" class="form-label">Related Seller</label>
                        <select name="seller_id" id="seller_id" class="form-select">
                            <option value="">None</option>
                            @foreach($sellers as $seller)
                                <option value="{{ $seller->id }}" {{ old('seller_id') == $seller->id ? 'selected' : '' }}>
                                    {{ $seller->store_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-0">
                        <label for="order_id" class="form-label">Related Order ID</label>
                        <div class="input-group">
                            <span class="input-group-text">#</span>
                            <input type="number" name="order_id" id="order_id" class="form-control @error('order_id') is-invalid @enderror" value="{{ old('order_id') }}" placeholder="e.g. 1234" min="1">
                        </div>
                        @error('order_id')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary w-100 mb-3" id="btn-submit">
                        <i class="ti tabler-send me-1"></i> Create Ticket
                    </button>
                    <a href="{{ route('support-tickets.index') }}" class="btn btn-outline-secondary w-100">
                        <i class="ti tabler-x me-1"></i> Cancel
                    </a>
                </div>
            </div>

            <div class="card bg-label-info">
                <div class="card-body">
                    <h6 class="mb-2"><i class="ti tabler-info-circle me-1"></i> Quick Tips</h6>
                    <ul class="mb-0 ps-3" style="font-size:.82rem;">
                        <li class="mb-1">The customer will receive an email notification about this ticket.</li>
                        <li class="mb-1">You can assign the ticket to a specific admin or seller.</li>
                        <li class="mb-1">Link a related order if the issue is order-specific.</li>
                        <li>Attachments help resolve issues faster.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</form>

@endsection

@push('page-js')
<script>
$(function () {
    // Select2 for customer dropdown
    if ($.fn.select2) {
        $('#user_id').select2({
            placeholder: 'Search customer by name or email...',
            allowClear: true,
            width: '100%'
        });
    }

    // Character counters
    $('#subject').on('input', function () {
        $('#subject-count').text(this.value.length);
    }).trigger('input');

    $('#message').on('input', function () {
        $('#msg-count').text(this.value.length.toLocaleString());
    }).trigger('input');

    // File upload / drag-drop
    var dropZone = $('#drop-zone'),
        fileInput = $('#file-input'),
        preview = $('#attachment-preview'),
        dt = new DataTransfer();

    $('#browse-link').on('click', function () { fileInput.trigger('click'); });
    dropZone.on('click', function (e) {
        if (!$(e.target).is('a')) fileInput.trigger('click');
    });

    dropZone.on('dragover', function (e) {
        e.preventDefault();
        $(this).addClass('border-primary bg-light');
    }).on('dragleave drop', function (e) {
        e.preventDefault();
        $(this).removeClass('border-primary bg-light');
    }).on('drop', function (e) {
        var files = e.originalEvent.dataTransfer.files;
        for (var i = 0; i < files.length; i++) {
            dt.items.add(files[i]);
        }
        fileInput[0].files = dt.files;
        renderPreview();
    });

    fileInput.on('change', function () {
        for (var i = 0; i < this.files.length; i++) {
            dt.items.add(this.files[i]);
        }
        this.files = dt.files;
        renderPreview();
    });

    function renderPreview() {
        preview.empty();
        for (var i = 0; i < dt.files.length; i++) {
            var f = dt.files[i];
            var size = (f.size / 1024).toFixed(1) + ' KB';
            preview.append(
                '<div class="att-item">' +
                    '<i class="ti tabler-file me-1"></i>' +
                    '<span>' + f.name + '</span> <small class="text-muted">(' + size + ')</small>' +
                    '<span class="att-remove" data-idx="' + i + '">&times;</span>' +
                '</div>'
            );
        }
    }

    preview.on('click', '.att-remove', function () {
        var idx = $(this).data('idx');
        dt.items.remove(idx);
        fileInput[0].files = dt.files;
        renderPreview();
    });

    // Form submit with loading
    $('#create-ticket-form').on('submit', function () {
        var btn = $('#btn-submit');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Creating...');
    });
});
</script>
@endpush
