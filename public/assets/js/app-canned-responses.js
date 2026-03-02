/**
 * Canned Responses — CRUD page logic
 *
 * Expects a global `window.cannedConfig` object set by the Blade view:
 *   { csrfToken, baseUrl }
 */
'use strict';

(function ($) {
    var cfg = window.cannedConfig || {};

    // ── DataTable ────────────────────────────────────────
    var dt = $('#canned-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: cfg.baseUrl,
            data: function (d) {
                d.category = $('#filter-category').val();
            }
        },
        columns: [
            { data: 'title_col', name: 'title' },
            { data: 'body_col', name: 'body', orderable: false, searchable: true },
            { data: 'category_badge', name: 'category' },
            { data: 'status_col', name: 'is_active' },
            { data: 'sort_order', name: 'sort_order' },
            { data: 'actions', orderable: false, searchable: false },
        ],
        order: [[4, 'asc'], [0, 'asc']],
        pageLength: 15,
        language: { emptyTable: 'No templates found. Click "New Template" to create one.' }
    });

    $('#filter-category').on('change', function () { dt.ajax.reload(); });
    $('#btn-reset-filters').on('click', function () { $('#filter-category').val(''); dt.ajax.reload(); });

    // ── Offcanvas ────────────────────────────────────────
    var offcanvasEl = document.getElementById('formOffcanvas');
    var offcanvas = new bootstrap.Offcanvas(offcanvasEl);

    function resetForm() {
        document.getElementById('canned-form').reset();
        $('#form-id').val('');
        $('#form-active').prop('checked', true);
        $('#offcanvas-title').html('<i class="ti tabler-template me-2"></i>New Template');
    }

    // Add new
    $('#btn-add-new').on('click', function () {
        resetForm();
        offcanvas.show();
    });

    // Edit
    $(document).on('click', '.btn-edit', function () {
        var id = $(this).data('id');
        $.getJSON(cfg.baseUrl + '/' + id, function (data) {
            $('#form-id').val(data.id);
            $('#form-title').val(data.title);
            $('#form-shortcut').val((data.shortcut || '').replace(/^\//, ''));
            $('#form-category').val(data.category);
            $('#form-sort').val(data.sort_order);
            $('#form-body').val(data.body);
            $('#form-active').prop('checked', data.is_active);
            $('#offcanvas-title').html('<i class="ti tabler-pencil me-2"></i>Edit Template');
            offcanvas.show();
        });
    });

    // Save (create / update)
    $('#canned-form').on('submit', function (e) {
        e.preventDefault();

        var id = $('#form-id').val();
        var shortcut = $('#form-shortcut').val().trim();
        if (shortcut && !shortcut.startsWith('/')) shortcut = '/' + shortcut;

        var payload = {
            _token: cfg.csrfToken,
            title: $('#form-title').val(),
            body: $('#form-body').val(),
            category: $('#form-category').val(),
            shortcut: shortcut || null,
            sort_order: parseInt($('#form-sort').val()) || 0,
            is_active: $('#form-active').is(':checked') ? 1 : 0,
        };

        var url = id ? cfg.baseUrl + '/' + id : cfg.baseUrl;
        var type = id ? 'PUT' : 'POST';

        $('#btn-save').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Saving…');

        $.ajax({
            url: url, type: type, data: payload,
            success: function (res) {
                offcanvas.hide();
                dt.ajax.reload(null, false);
                Swal.fire({ icon: 'success', title: res.message, timer: 1500, showConfirmButton: false });
            },
            error: function (xhr) {
                var msg = 'Something went wrong.';
                if (xhr.responseJSON?.errors) {
                    msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                } else if (xhr.responseJSON?.message) {
                    msg = xhr.responseJSON.message;
                }
                Swal.fire({ icon: 'error', title: 'Validation Error', html: msg });
            },
            complete: function () {
                $('#btn-save').prop('disabled', false).html('<i class="ti tabler-check me-1"></i> Save Template');
            }
        });
    });

    // Delete
    $(document).on('click', '.btn-delete', function () {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Delete Template?', text: 'This action cannot be undone.',
            icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Delete',
        }).then(function (result) {
            if (result.isConfirmed) {
                $.ajax({
                    url: cfg.baseUrl + '/' + id, type: 'DELETE',
                    data: { _token: cfg.csrfToken },
                    success: function (res) {
                        dt.ajax.reload(null, false);
                        Swal.fire({ icon: 'success', title: res.message, timer: 1500, showConfirmButton: false });
                    },
                    error: function (xhr) {
                        Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Something went wrong.' });
                    }
                });
            }
        });
    });

})(jQuery);
