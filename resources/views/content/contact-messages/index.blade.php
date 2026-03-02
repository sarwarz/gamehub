@extends('layouts.app')

@section('title', 'Contact Messages')

@push('page-css')
<style>
.message-stats .avatar {
    display: flex;
    align-items: center;
    justify-content: center;
}
.badge-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
}
.message-body {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1rem;
    white-space: pre-wrap;
    word-break: break-word;
}
.bulk-bar { background:#f0f2ff; border-radius:8px; animation:bulkSlide .3s ease; }
@keyframes bulkSlide { from { opacity:0; transform:translateY(-8px); } to { opacity:1; transform:translateY(0); } }
</style>
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

    {{-- Page Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1"><i class="ti tabler-message-circle me-2"></i>Contact Messages</h4>
            <p class="text-muted mb-0">Messages from your website's contact form</p>
        </div>
    </div>

    {{-- Stats --}}
    <div class="row mb-4 message-stats">
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3 bg-label-primary">
                            <i class="ti tabler-messages fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0" id="stat-total">{{ $stats['total'] }}</h5>
                            <small class="text-muted">Total Messages</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3 bg-label-warning">
                            <i class="ti tabler-mail-exclamation fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0" id="stat-new">{{ $stats['new'] }}</h5>
                            <small class="text-muted">New / Unread</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3 bg-label-success">
                            <i class="ti tabler-mail-check fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0" id="stat-replied">{{ $stats['replied'] }}</h5>
                            <small class="text-muted">Replied</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3 bg-label-secondary">
                            <i class="ti tabler-archive fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0" id="stat-archived">{{ $stats['archived'] }}</h5>
                            <small class="text-muted">Archived</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- DataTable Card --}}
    <div class="card">
        <div class="card-header pb-0">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <h5 class="mb-0"><i class="ti tabler-inbox me-2"></i>Inbox</h5>
                <div class="d-flex gap-2">
                    <button class="btn btn-label-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#filter-collapse">
                        <i class="ti tabler-filter me-1"></i> Filters
                    </button>
                </div>
            </div>

            {{-- Collapsible Filter Panel --}}
            <div class="collapse mt-3" id="filter-collapse">
                <div class="row g-3 pb-3 border-bottom">
                    <div class="col-md-4 col-sm-6">
                        <label class="form-label small text-muted">Status</label>
                        <select class="form-select form-select-sm" id="filter-status">
                            <option value="">All Status</option>
                            <option value="new">New</option>
                            <option value="read">Read</option>
                            <option value="replied">Replied</option>
                            <option value="archived">Archived</option>
                        </select>
                    </div>
                    <div class="col-md-4 col-sm-6">
                        <label class="form-label small text-muted">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-primary" id="applyFilters">
                                <i class="ti tabler-check me-1"></i> Apply
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger d-none" id="clearFilters">
                                <i class="ti tabler-x me-1"></i> Clear
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bulk Actions Bar --}}
        <div class="card-body py-0">
            <div class="bulk-bar d-none py-2 px-3 mt-3" id="bulk-bar">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary rounded-pill fs-6" id="bulk-count">0</span>
                        <span class="fw-medium" style="font-size:.85rem">messages selected</span>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-sm btn-label-secondary btn-bulk-archive">
                            <i class="ti tabler-archive me-1"></i> Archive
                        </button>
                        <button type="button" class="btn btn-sm btn-label-danger btn-bulk-delete">
                            <i class="ti tabler-trash me-1"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table id="messages-table" class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th width="40"><input type="checkbox" class="form-check-input" id="select-all"></th>
                        <th>Sender</th>
                        <th>Subject & Preview</th>
                        <th>Status</th>
                        <th>Received</th>
                        <th width="80">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    {{-- View Message Modal --}}
    <div class="modal fade" id="viewMessageModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ti tabler-mail-opened me-2"></i><span id="modal-subject"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    {{-- Sender Info --}}
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <small class="text-muted d-block">From</small>
                            <span class="fw-semibold" id="modal-name"></span>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Email</small>
                            <a href="#" id="modal-email"></a>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Phone</small>
                            <span id="modal-phone"></span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <small class="text-muted d-block">IP Address</small>
                            <span id="modal-ip"></span>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Received</small>
                            <span id="modal-date"></span>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Status</small>
                            <span id="modal-status"></span>
                        </div>
                    </div>
                    <hr>

                    {{-- Message Body --}}
                    <label class="form-label fw-semibold">Message</label>
                    <div class="message-body" id="modal-message"></div>

                    <hr>

                    {{-- Admin Notes --}}
                    <label class="form-label fw-semibold">Admin Notes</label>
                    <textarea class="form-control" id="modal-notes" rows="3" placeholder="Add internal notes..."></textarea>

                    <div class="mt-3">
                        <label class="form-label fw-semibold">Update Status</label>
                        <select class="form-select" id="modal-status-select">
                            <option value="read">Read</option>
                            <option value="replied">Replied</option>
                            <option value="archived">Archived</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="#" class="btn btn-outline-info" id="modal-reply-btn" target="_blank">
                        <i class="ti tabler-send me-1"></i> Reply via Email
                    </a>
                    <button type="button" class="btn btn-primary" id="modal-save-btn">
                        <i class="ti tabler-check me-1"></i> Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('page-js')
<script>
$(function() {
    const csrfToken = '{{ csrf_token() }}';
    let currentMessageId = null;

    const table = $('#messages-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("contact-messages.index") }}',
            data: function(d) {
                d.filter_status = $('#filter-status').val();
            }
        },
        columns: [
            { data: 'checkbox', orderable: false, searchable: false },
            { data: 'sender_info', name: 'name' },
            { data: 'subject_col', name: 'subject' },
            { data: 'status_badge', name: 'status' },
            { data: 'date_col', name: 'created_at' },
            { data: 'actions', orderable: false, searchable: false }
        ],
        order: [[4, 'desc']],
        pageLength: 15,
    });

    // Filter handlers
    $('#applyFilters').on('click', function() {
        table.ajax.reload();
        if ($('#filter-status').val()) {
            $('#clearFilters').removeClass('d-none');
        }
    });
    $('#clearFilters').on('click', function() {
        $('#filter-status').val('');
        $(this).addClass('d-none');
        table.ajax.reload();
    });

    // Select all / Bulk bar
    $('#select-all').on('change', function() {
        $('.row-checkbox').prop('checked', this.checked);
        updateBulk();
    });
    $(document).on('change', '.row-checkbox', updateBulk);
    function updateBulk() {
        const count = $('.row-checkbox:checked').length;
        $('#bulk-count').text(count);
        if (count > 0) {
            $('#bulk-bar').removeClass('d-none');
        } else {
            $('#bulk-bar').addClass('d-none');
        }
    }

    // View message
    $(document).on('click', '.btn-view', function() {
        const id = $(this).data('id');
        currentMessageId = id;
        $.get('{{ url("contact-messages") }}/' + id, function(res) {
            const m = res.data;
            $('#modal-subject').text(m.subject);
            $('#modal-name').text(m.name);
            $('#modal-email').text(m.email).attr('href', 'mailto:' + m.email);
            $('#modal-phone').text(m.phone || '—');
            $('#modal-ip').text(m.ip_address || '—');
            $('#modal-date').text(m.created_at ? new Date(m.created_at).toLocaleString() : '—');
            $('#modal-message').text(m.message);
            $('#modal-notes').val(m.admin_notes || '');
            $('#modal-status-select').val(m.status === 'new' ? 'read' : m.status);
            $('#modal-reply-btn').attr('href', 'mailto:' + m.email + '?subject=Re: ' + encodeURIComponent(m.subject));

            const statusMap = { 'new': '<span class="badge bg-label-primary">New</span>', 'read': '<span class="badge bg-label-info">Read</span>', 'replied': '<span class="badge bg-label-success">Replied</span>', 'archived': '<span class="badge bg-label-secondary">Archived</span>' };
            $('#modal-status').html(statusMap[m.status] || m.status);

            new bootstrap.Modal(document.getElementById('viewMessageModal')).show();
            table.ajax.reload(null, false);
        });
    });

    // Save changes from modal
    $('#modal-save-btn').on('click', function() {
        $.ajax({
            url: '{{ url("contact-messages") }}/' + currentMessageId,
            type: 'PUT',
            data: {
                status: $('#modal-status-select').val(),
                admin_notes: $('#modal-notes').val(),
                _token: csrfToken,
            },
            success: function(res) {
                bootstrap.Modal.getInstance(document.getElementById('viewMessageModal')).hide();
                table.ajax.reload(null, false);
                Swal.fire({ icon: 'success', title: res.message, timer: 1500, showConfirmButton: false });
            }
        });
    });

    // Update status from dropdown
    $(document).on('click', '.btn-status', function() {
        const id = $(this).data('id');
        const status = $(this).data('status');
        $.ajax({
            url: '{{ url("contact-messages") }}/' + id,
            type: 'PUT',
            data: { status, _token: csrfToken },
            success: function(res) {
                table.ajax.reload(null, false);
                Swal.fire({ icon: 'success', title: res.message, timer: 1500, showConfirmButton: false });
            }
        });
    });

    // Delete
    $(document).on('click', '.btn-delete', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Delete Message?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Delete',
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ url("contact-messages") }}/' + id,
                    type: 'DELETE',
                    data: { _token: csrfToken },
                    success: function(res) {
                        table.ajax.reload(null, false);
                        Swal.fire({ icon: 'success', title: res.message, timer: 1500, showConfirmButton: false });
                    }
                });
            }
        });
    });

    // Bulk delete
    $('.btn-bulk-delete').on('click', function() {
        const ids = $('.row-checkbox:checked').map(function() { return $(this).val(); }).get();
        Swal.fire({
            title: `Delete ${ids.length} messages?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Delete All',
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('{{ route("contact-messages.bulk-delete") }}', { ids, _token: csrfToken }, function(res) {
                    table.ajax.reload(null, false);
                    updateBulk();
                    $('#select-all').prop('checked', false);
                    Swal.fire({ icon: 'success', title: res.message, timer: 1500, showConfirmButton: false });
                });
            }
        });
    });

    // Bulk archive
    $('.btn-bulk-archive').on('click', function() {
        const ids = $('.row-checkbox:checked').map(function() { return $(this).val(); }).get();
        $.post('{{ route("contact-messages.bulk-status") }}', { ids, status: 'archived', _token: csrfToken }, function(res) {
            table.ajax.reload(null, false);
            updateBulk();
            $('#select-all').prop('checked', false);
            Swal.fire({ icon: 'success', title: res.message, timer: 1500, showConfirmButton: false });
        });
    });
});
</script>
@endpush
