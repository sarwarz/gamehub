@extends('layouts.app')
@section('title', 'Notifications')

@section('content')

    @include('partials.alerts')

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1"><i class="icon-base ti tabler-bell me-2"></i>Notifications</h4>
            <p class="text-muted mb-0">View and manage all system notifications</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#sendModal">
            <i class="icon-base ti tabler-send me-1"></i> Send Notification
        </button>
    </div>

    {{-- Stats Cards --}}
    <div class="row mb-4">
        <div class="col-xl-4 col-sm-6 mb-xl-0 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3 bg-label-primary">
                            <i class="icon-base ti tabler-bell fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0" id="stat-total">{{ number_format($stats['total']) }}</h5>
                            <small class="text-muted">Total Notifications</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-sm-6 mb-xl-0 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3 bg-label-warning">
                            <i class="icon-base ti tabler-bell-ringing fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0" id="stat-unread">{{ number_format($stats['unread']) }}</h5>
                            <small class="text-muted">Unread</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-sm-6 mb-xl-0 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3 bg-label-success">
                            <i class="icon-base ti tabler-bell-check fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0" id="stat-read">{{ number_format($stats['read']) }}</h5>
                            <small class="text-muted">Read</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Table Card --}}
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
            <h5 class="mb-0">All Notifications</h5>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <button class="btn btn-danger btn-sm d-none" id="notif-bulk-delete">
                    <i class="icon-base ti tabler-trash me-1"></i>
                    Delete Selected (<span id="selected-count">0</span>)
                </button>
                <select id="filter-type" class="form-select form-select-sm" style="width:180px">
                    <option value="">All Types</option>
                    <option value="AdminBroadcastNotification">Broadcast</option>
                    <option value="OrderPlacedNotification">Order Placed</option>
                    <option value="OrderPaymentNotification">Order Payment</option>
                    <option value="OrderStatusNotification">Order Status</option>
                    <option value="TicketCreatedNotification">Ticket Created</option>
                    <option value="TicketReplyNotification">Ticket Reply</option>
                </select>
                <select id="filter-read" class="form-select form-select-sm" style="width:130px">
                    <option value="">All Status</option>
                    <option value="unread">Unread</option>
                    <option value="read">Read</option>
                </select>
            </div>
        </div>
        <div class="card-body">
            <table id="notification-table" class="table table-hover" style="width:100%">
                <thead>
                    <tr>
                        <th style="width:40px"><input type="checkbox" class="form-check-input" id="select-all"></th>
                        <th>Type</th>
                        <th>Recipient</th>
                        <th>Preview</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th style="width:90px">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    {{-- Send Notification Modal --}}
    <div class="modal fade" id="sendModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title"><i class="icon-base ti tabler-send me-2"></i>Send Notification</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs mb-3" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#tab-specific">
                                <i class="icon-base ti tabler-users me-1"></i>Specific Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#tab-broadcast">
                                <i class="icon-base ti tabler-speakerphone me-1"></i>Broadcast
                            </a>
                        </li>
                    </ul>
                    <div class="tab-content p-0">
                        {{-- Specific Users Tab --}}
                        <div class="tab-pane fade show active" id="tab-specific">
                            <div class="mb-3">
                                <label class="form-label">Select Users <span class="text-danger">*</span></label>
                                <select id="send-user-select" multiple="multiple" style="width:100%"></select>
                                <small class="text-muted">Search by name, email, or user ID</small>
                            </div>
                        </div>
                        {{-- Broadcast Tab --}}
                        <div class="tab-pane fade" id="tab-broadcast">
                            <div class="mb-3">
                                <label class="form-label">Send to</label>
                                <select id="send-role" class="form-select">
                                    <option value="all">All Users</option>
                                    @foreach ($roles as $id => $name)
                                        <option value="{{ $name }}">{{ ucfirst($name) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" id="send-title" class="form-control" placeholder="Notification title" maxlength="255">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message <span class="text-danger">*</span></label>
                        <textarea id="send-message" class="form-control" rows="4" placeholder="Write your message..." maxlength="2000"></textarea>
                        <small class="text-muted float-end"><span id="char-count">0</span>/2000</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select id="send-type" class="form-select">
                            <option value="info">Info</option>
                            <option value="success">Success</option>
                            <option value="warning">Warning</option>
                            <option value="danger">Danger</option>
                            <option value="announcement">Announcement</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="btn-send">
                        <i class="icon-base ti tabler-send me-1"></i> Send
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- View Notification Modal --}}
    <div class="modal fade" id="viewModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title"><i class="icon-base ti tabler-bell me-2"></i>Notification Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span id="view-type-badge" class="badge"></span>
                        <span id="view-status-badge" class="badge"></span>
                    </div>

                    <h6 class="fw-bold mb-1" id="view-title"></h6>
                    <p class="text-muted mb-3" id="view-message" style="white-space:pre-wrap"></p>

                    <div id="view-url-wrap" class="mb-3 d-none">
                        <label class="form-label text-muted mb-1" style="font-size:.75rem">Action URL</label>
                        <div>
                            <a href="#" id="view-url" target="_blank" class="text-primary text-break"></a>
                        </div>
                    </div>

                    <div id="view-extra-wrap" class="mb-3 d-none">
                        <label class="form-label text-muted mb-1" style="font-size:.75rem">Additional Data</label>
                        <pre id="view-extra" class="bg-light rounded p-2 mb-0" style="font-size:.8rem;max-height:150px;overflow:auto"></pre>
                    </div>

                    <hr class="my-3">

                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label text-muted mb-1" style="font-size:.75rem">Recipient</label>
                            <div class="fw-medium" id="view-recipient" style="font-size:.875rem"></div>
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted mb-1" style="font-size:.75rem">Created</label>
                            <div class="fw-medium" id="view-created" style="font-size:.875rem"></div>
                        </div>
                        <div class="col-6" id="view-read-wrap">
                            <label class="form-label text-muted mb-1" style="font-size:.75rem">Read At</label>
                            <div class="fw-medium" id="view-read-at" style="font-size:.875rem"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-danger btn-sm" id="view-delete-btn">
                        <i class="icon-base ti tabler-trash me-1"></i> Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('page-js')
<script>
$(function () {
    var CSRF = '{{ csrf_token() }}';
    var ajaxHeaders = { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' };

    function handleAjaxError(xhr) {
        var msg = 'Something went wrong.';
        try {
            var json = xhr.responseJSON || JSON.parse(xhr.responseText);
            if (json.message) msg = json.message;
            else if (json.errors) msg = json.errors[Object.keys(json.errors)[0]][0];
        } catch (e) {
            if (xhr.status === 419) msg = 'Session expired. Please refresh the page.';
            else if (xhr.status === 403) msg = 'You do not have permission to perform this action.';
            else if (xhr.status === 404) msg = 'Record not found.';
            else if (xhr.status === 500) msg = 'Server error. Please try again later.';
        }
        Swal.fire({ icon: 'error', title: 'Error', text: msg });
    }

    // ─── DataTable ───
    var table = $('#notification-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("notifications.index") }}',
            data: function (d) {
                d.read_status = $('#filter-read').val();
                d.type = $('#filter-type').val();
            }
        },
        columns: [
            { data: 'checkbox', orderable: false, searchable: false, className: 'text-center' },
            { data: 'type_label' },
            { data: 'recipient' },
            { data: 'preview' },
            { data: 'status_badge', className: 'text-center' },
            { data: 'created_at', render: function (d) {
                if (!d) return '';
                var dt = new Date(d);
                return dt.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
                    + '<br><small class="text-muted">' + dt.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }) + '</small>';
            }},
            { data: 'actions', orderable: false, searchable: false, className: 'text-center' },
        ],
        order: [[5, 'desc']],
        pageLength: 15,
        language: {
            emptyTable: 'No notifications found',
            zeroRecords: 'No matching notifications',
        },
    });

    // ─── Filters ───
    $('#filter-read, #filter-type').on('change', function () { table.ajax.reload(); });

    // ─── Stats Update ───
    function updateStats(stats) {
        if (!stats) return;
        $('#stat-total').text(Number(stats.total).toLocaleString());
        $('#stat-unread').text(Number(stats.unread).toLocaleString());
        $('#stat-read').text(Number(stats.read).toLocaleString());
    }

    // ─── Select All / Bulk Select ───
    $('#select-all').on('change', function () {
        $('#notification-table tbody .bulk-checkbox').prop('checked', this.checked);
        updateBulkUI();
    });

    $('#notification-table tbody').on('change', '.bulk-checkbox', function () {
        var total = $('#notification-table tbody .bulk-checkbox').length;
        var selected = $('#notification-table tbody .bulk-checkbox:checked').length;
        $('#select-all').prop('checked', total > 0 && total === selected);
        updateBulkUI();
    });

    function updateBulkUI() {
        var count = $('#notification-table tbody .bulk-checkbox:checked').length;
        $('#selected-count').text(count);
        $('#notif-bulk-delete').toggleClass('d-none', count === 0);
    }

    table.on('draw', function () {
        $('#select-all').prop('checked', false);
        updateBulkUI();
    });

    // ─── Single Delete (using fetch API) ───
    $(document).on('click', '.delete-btn', function (e) {
        e.preventDefault();
        e.stopPropagation();

        var id = $(this).attr('data-id');
        if (!id) return;

        Swal.fire({
            title: 'Delete notification?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Delete',
            confirmButtonColor: '#d33',
        }).then(function (result) {
            if (!result.isConfirmed) return;

            fetch('{{ url("dashboard/notifications") }}/' + encodeURIComponent(id), {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': CSRF,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(function (r) {
                if (!r.ok) return r.json().then(function (j) { throw j; });
                return r.json();
            })
            .then(function (data) {
                table.ajax.reload(null, false);
                updateStats(data.stats);
                Swal.fire({ icon: 'success', title: 'Deleted', text: data.message, timer: 1500, showConfirmButton: false });
            })
            .catch(function (err) {
                var msg = (err && err.message) ? err.message : 'Delete failed.';
                Swal.fire({ icon: 'error', title: 'Error', text: msg });
            });
        });
    });

    // ─── Bulk Delete (using fetch API) ───
    $('#notif-bulk-delete').on('click', function () {
        var ids = [];
        $('#notification-table tbody .bulk-checkbox:checked').each(function () {
            ids.push(String($(this).attr('value')));
        });
        if (!ids.length) return;

        Swal.fire({
            title: 'Delete ' + ids.length + ' notification(s)?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete',
            confirmButtonColor: '#d33',
        }).then(function (result) {
            if (!result.isConfirmed) return;

            fetch('/dashboard/notifications/bulk-delete', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': CSRF,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ ids: ids })
            })
            .then(function (r) {
                if (!r.ok) return r.json().then(function (j) { throw j; });
                return r.json();
            })
            .then(function (data) {
                table.ajax.reload(null, false);
                updateStats(data.stats);
                $('#select-all').prop('checked', false);
                updateBulkUI();
                Swal.fire({ icon: 'success', title: 'Deleted', text: data.message, timer: 1500, showConfirmButton: false });
            })
            .catch(function (err) {
                var msg = (err && err.message) ? err.message : 'Bulk delete failed.';
                Swal.fire({ icon: 'error', title: 'Error', text: msg });
            });
        });
    });

    // ─── View Notification Modal ───
    var typeColors = {
        'OrderPlacedNotification':    'bg-label-success',
        'OrderPaymentNotification':   'bg-label-warning',
        'OrderStatusNotification':    'bg-label-info',
        'AdminBroadcastNotification': 'bg-label-primary',
        'TicketCreatedNotification':  'bg-label-danger',
        'TicketReplyNotification':    'bg-label-secondary',
    };

    var viewModal = new bootstrap.Modal('#viewModal');
    var currentViewId = null;

    $(document).on('click', '.view-btn', function (e) {
        e.preventDefault();
        var raw = $(this).attr('data-notification');
        if (!raw) return;

        var n;
        try { n = JSON.parse(raw); } catch (err) { return; }
        currentViewId = n.id;

        var badgeColor = typeColors[n.type] || 'bg-label-secondary';
        $('#view-type-badge').attr('class', 'badge ' + badgeColor).text(n.type);

        if (n.status === 'Read') {
            $('#view-status-badge').attr('class', 'badge bg-label-secondary').html('<i class="ti tabler-eye-check me-1"></i>Read');
        } else {
            $('#view-status-badge').attr('class', 'badge bg-label-primary').html('<i class="ti tabler-eye me-1"></i>Unread');
        }

        $('#view-title').text(n.title || '(No title)');
        $('#view-message').text(n.message || '(No message)');

        if (n.url) {
            $('#view-url').attr('href', n.url).text(n.url);
            $('#view-url-wrap').removeClass('d-none');
        } else {
            $('#view-url-wrap').addClass('d-none');
        }

        if (n.extra && Object.keys(n.extra).length > 0) {
            $('#view-extra').text(JSON.stringify(n.extra, null, 2));
            $('#view-extra-wrap').removeClass('d-none');
        } else {
            $('#view-extra-wrap').addClass('d-none');
        }

        $('#view-recipient').text(n.recipient);
        $('#view-created').text(n.created_at);

        if (n.read_at) {
            $('#view-read-at').text(n.read_at);
            $('#view-read-wrap').removeClass('d-none');
        } else {
            $('#view-read-wrap').addClass('d-none');
        }

        viewModal.show();
    });

    // Delete from preview modal
    $('#view-delete-btn').on('click', function () {
        if (!currentViewId) return;
        var btn = $(this);

        Swal.fire({
            title: 'Delete this notification?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Delete',
            confirmButtonColor: '#d33',
        }).then(function (result) {
            if (!result.isConfirmed) return;

            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

            fetch('{{ url("dashboard/notifications") }}/' + encodeURIComponent(currentViewId), {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' }
            })
            .then(function (r) {
                if (!r.ok) return r.json().then(function (j) { throw j; });
                return r.json();
            })
            .then(function (data) {
                viewModal.hide();
                table.ajax.reload(null, false);
                updateStats(data.stats);
                Swal.fire({ icon: 'success', title: 'Deleted', text: data.message, timer: 1500, showConfirmButton: false });
            })
            .catch(function (err) {
                var msg = (err && err.message) ? err.message : 'Delete failed.';
                Swal.fire({ icon: 'error', title: 'Error', text: msg });
            })
            .finally(function () {
                btn.prop('disabled', false).html('<i class="icon-base ti tabler-trash me-1"></i> Delete');
            });
        });
    });

    // ─── Select2 User Search ───
    $('#send-user-select').select2({
        dropdownParent: $('#sendModal'),
        placeholder: 'Search users by name or email...',
        allowClear: true,
        multiple: true,
        minimumInputLength: 1,
        ajax: {
            url: '{{ route("notifications.search-users") }}',
            dataType: 'json',
            delay: 300,
            data: function (params) { return { q: params.term }; },
            processResults: function (data) { return { results: data.results }; },
            cache: true,
        },
    });

    // ─── Character Counter ───
    $('#send-message').on('input', function () {
        $('#char-count').text(this.value.length);
    });

    // ─── Send Notification ───
    $('#btn-send').on('click', function () {
        var btn = $(this);
        var activeTab = $('.tab-pane.active').attr('id');
        var title   = $('#send-title').val().trim();
        var message = $('#send-message').val().trim();
        var type    = $('#send-type').val();

        if (!title || !message) {
            return Swal.fire('Required', 'Title and message are required.', 'warning');
        }

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Sending...');

        var payload, url;
        if (activeTab === 'tab-specific') {
            var userIds = $('#send-user-select').val();
            if (!userIds || !userIds.length) {
                btn.prop('disabled', false).html('<i class="icon-base ti tabler-send me-1"></i> Send');
                return Swal.fire('Required', 'Select at least one user.', 'warning');
            }
            url = '{{ route("notifications.send") }}';
            payload = { user_ids: userIds, title: title, message: message, type: type };
        } else {
            url = '{{ route("notifications.send-all") }}';
            payload = { role: $('#send-role').val(), title: title, message: message };
        }

        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(function (r) {
            if (!r.ok) return r.json().then(function (j) { throw j; });
            return r.json();
        })
        .then(function (data) {
            Swal.fire('Sent!', data.message, 'success');
            resetModal();
            table.ajax.reload();
        })
        .catch(function (err) {
            var msg = (err && err.message) ? err.message : 'Failed to send.';
            Swal.fire({ icon: 'error', title: 'Error', text: msg });
        })
        .finally(function () {
            btn.prop('disabled', false).html('<i class="icon-base ti tabler-send me-1"></i> Send');
        });
    });

    function resetModal() {
        $('#sendModal').modal('hide');
        $('#send-title').val('');
        $('#send-message').val('');
        $('#send-user-select').val(null).trigger('change');
        $('#send-type').val('info');
        $('#char-count').text('0');
    }
});
</script>
@endpush
