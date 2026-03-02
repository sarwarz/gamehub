/**
 * Support Tickets — Index page logic
 *
 * Expects a global `window.ticketIndexConfig` object set by the Blade view:
 *   { csrfToken, indexUrl, baseUrl, bulkDeleteUrl, bulkActionUrl }
 */
'use strict';

(function ($) {
    var cfg = window.ticketIndexConfig || {};
    var tok = cfg.csrfToken;

    // ── DataTable ────────────────────────────────────────
    var table = $('#tickets-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: cfg.indexUrl,
            data: function (d) {
                d.status     = $('#filter-status').val();
                d.department = $('#filter-department').val();
                d.priority   = $('#filter-priority').val();
            }
        },
        columns: [
            { data: 'checkbox', orderable: false, searchable: false, className: 'pe-0' },
            { data: 'ticket_col', name: 'ticket_number' },
            { data: 'customer_col', name: 'user.name', orderable: false },
            { data: 'department_badge', name: 'department' },
            { data: 'priority_badge', name: 'priority' },
            { data: 'status_badge', name: 'status' },
            { data: 'assigned_col', orderable: false, searchable: false },
            { data: 'date_col', name: 'created_at' },
            { data: 'actions', orderable: false, searchable: false }
        ],
        order: [[7, 'desc']],
        pageLength: 15,
        drawCallback: function () {
            $('#select-all').prop('checked', false);
            syncBulkBar();
        }
    });

    // ── Filters ──────────────────────────────────────────
    $('#filter-status, #filter-department, #filter-priority').on('change', function () {
        table.ajax.reload();
    });
    $('#btn-apply-filters').on('click', function () {
        table.ajax.reload();
    });
    $('#btn-clear-filters').on('click', function () {
        $('#filter-status, #filter-department, #filter-priority').val('');
        table.ajax.reload();
    });

    // ── Selection & Bulk Bar ─────────────────────────────
    $('#select-all').on('change', function () {
        $('.row-checkbox').prop('checked', this.checked);
        syncBulkBar();
    });
    $(document).on('change', '.row-checkbox', syncBulkBar);

    function getSelectedIds() {
        return $('.row-checkbox:checked').map(function () { return $(this).val(); }).get();
    }

    function syncBulkBar() {
        var count = getSelectedIds().length;
        if (count > 0) {
            $('#bulk-bar').removeClass('d-none');
            $('#bulk-count').text(count);
        } else {
            $('#bulk-bar').addClass('d-none');
        }
    }

    function ajaxError(xhr) {
        Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Something went wrong.' });
    }

    function afterBulk() {
        table.ajax.reload(null, false);
        $('#select-all').prop('checked', false);
        syncBulkBar();
    }

    // ── Bulk: Change Status ──────────────────────────────
    $(document).on('click', '.bulk-status-btn', function () {
        var status = $(this).data('status');
        var ids = getSelectedIds();
        if (!ids.length) return;

        var label = $(this).text().trim();
        Swal.fire({
            title: 'Change status to "' + label + '"?',
            text: ids.length + ' ticket(s) will be updated.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, update',
        }).then(function (r) {
            if (r.isConfirmed) {
                $.ajax({
                    url: cfg.bulkActionUrl, type: 'POST',
                    data: { ids: ids, action: 'change_status', status: status, _token: tok },
                    success: function (res) {
                        afterBulk();
                        Swal.fire({ icon: 'success', title: res.message, timer: 1800, showConfirmButton: false });
                    },
                    error: ajaxError
                });
            }
        });
    });

    // ── Bulk: Change Priority ────────────────────────────
    $(document).on('click', '.bulk-priority-btn', function () {
        var priority = $(this).data('priority');
        var ids = getSelectedIds();
        if (!ids.length) return;

        Swal.fire({
            title: 'Set priority to "' + priority + '"?',
            text: ids.length + ' ticket(s) will be updated.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, update',
        }).then(function (r) {
            if (r.isConfirmed) {
                $.ajax({
                    url: cfg.bulkActionUrl, type: 'POST',
                    data: { ids: ids, action: 'change_priority', priority: priority, _token: tok },
                    success: function (res) {
                        afterBulk();
                        Swal.fire({ icon: 'success', title: res.message, timer: 1800, showConfirmButton: false });
                    },
                    error: ajaxError
                });
            }
        });
    });

    // ── Bulk: Assign ─────────────────────────────────────
    $(document).on('click', '.bulk-assign-btn', function () {
        var adminId = $(this).data('admin-id');
        var name = $(this).text().trim();
        var ids = getSelectedIds();
        if (!ids.length) return;

        Swal.fire({
            title: 'Assign ' + ids.length + ' ticket(s) to ' + name + '?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, assign',
        }).then(function (r) {
            if (r.isConfirmed) {
                $.ajax({
                    url: cfg.bulkActionUrl, type: 'POST',
                    data: { ids: ids, action: 'assign', admin_id: adminId, _token: tok },
                    success: function (res) {
                        afterBulk();
                        Swal.fire({ icon: 'success', title: res.message, timer: 1800, showConfirmButton: false });
                    },
                    error: ajaxError
                });
            }
        });
    });

    // ── Bulk: Close ──────────────────────────────────────
    $(document).on('click', '.bulk-close-btn', function () {
        var ids = getSelectedIds();
        if (!ids.length) return;

        Swal.fire({
            title: 'Close ' + ids.length + ' ticket(s)?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, close all',
        }).then(function (r) {
            if (r.isConfirmed) {
                $.ajax({
                    url: cfg.bulkActionUrl, type: 'POST',
                    data: { ids: ids, action: 'close', _token: tok },
                    success: function (res) {
                        afterBulk();
                        Swal.fire({ icon: 'success', title: res.message, timer: 1800, showConfirmButton: false });
                    },
                    error: ajaxError
                });
            }
        });
    });

    // ── Bulk: Delete ─────────────────────────────────────
    $(document).on('click', '.bulk-delete-btn', function () {
        var ids = getSelectedIds();
        if (!ids.length) return;

        Swal.fire({
            title: 'Delete ' + ids.length + ' ticket(s)?',
            text: 'This will permanently delete the tickets and all messages.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Delete All',
        }).then(function (r) {
            if (r.isConfirmed) {
                $.ajax({
                    url: cfg.bulkDeleteUrl, type: 'POST',
                    data: { ids: ids, _token: tok },
                    success: function (res) {
                        afterBulk();
                        Swal.fire({ icon: 'success', title: res.message, timer: 1800, showConfirmButton: false });
                    },
                    error: ajaxError
                });
            }
        });
    });

    // ── Single: Close ticket ─────────────────────────────
    $(document).on('click', '.btn-close-ticket', function () {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Close this ticket?', icon: 'question',
            showCancelButton: true, confirmButtonText: 'Yes, close it',
        }).then(function (result) {
            if (result.isConfirmed) {
                $.ajax({
                    url: cfg.baseUrl + '/' + id + '/status', type: 'PUT',
                    data: { status: 'closed', _token: tok },
                    success: function (res) {
                        table.ajax.reload(null, false);
                        Swal.fire({ icon: 'success', title: res.message, timer: 1500, showConfirmButton: false });
                    },
                    error: ajaxError
                });
            }
        });
    });

    // ── Single: Delete ticket ────────────────────────────
    $(document).on('click', '.btn-delete', function () {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Delete Ticket?', text: 'This will delete the ticket and all its messages.',
            icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Delete',
        }).then(function (result) {
            if (result.isConfirmed) {
                $.ajax({
                    url: cfg.baseUrl + '/' + id, type: 'DELETE',
                    data: { _token: tok },
                    success: function (res) {
                        table.ajax.reload(null, false);
                        Swal.fire({ icon: 'success', title: res.message, timer: 1500, showConfirmButton: false });
                    },
                    error: ajaxError
                });
            }
        });
    });

})(jQuery);
