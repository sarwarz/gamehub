/**
 * Ticket Departments — CRUD page logic
 *
 * Expects window.deptConfig = { csrfToken, baseUrl }
 */
'use strict';

(function ($) {
    var cfg = window.deptConfig || {};

    // ── DataTable ────────────────────────────────────────
    var dt = $('#dept-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: { url: cfg.baseUrl },
        columns: [
            { data: 'name_col', name: 'name' },
            { data: 'color_col', name: 'color', className: 'text-center' },
            { data: 'tickets_count_col', name: 'tickets_count', className: 'text-center', orderable: false, searchable: false },
            { data: 'status_col', name: 'is_active', className: 'text-center' },
            { data: 'sort_order', name: 'sort_order' },
            { data: 'actions', orderable: false, searchable: false },
        ],
        order: [[4, 'asc'], [0, 'asc']],
        pageLength: 15,
        language: { emptyTable: 'No departments found. Click "New Department" to create one.' }
    });

    // ── Offcanvas ────────────────────────────────────────
    var offcanvasEl = document.getElementById('formOffcanvas');
    var offcanvas = new bootstrap.Offcanvas(offcanvasEl);

    function resetForm() {
        document.getElementById('dept-form').reset();
        $('#form-id').val('');
        $('#form-active').prop('checked', true);
        $('#form-icon').val('ti tabler-folder');
        $('#icon-preview').html('<i class="ti tabler-folder"></i>');
        $('#offcanvas-title').html('<i class="ti tabler-category me-2"></i>New Department');
    }

    // Live icon preview
    $('#form-icon').on('input', function () {
        var cls = $(this).val().trim() || 'ti tabler-folder';
        $('#icon-preview').html('<i class="' + cls + '"></i>');
    });

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
            $('#form-name').val(data.name);
            $('#form-slug').val(data.slug);
            $('#form-color').val(data.color);
            $('#form-sort').val(data.sort_order);
            $('#form-icon').val(data.icon || 'ti tabler-folder');
            $('#icon-preview').html('<i class="' + (data.icon || 'ti tabler-folder') + '"></i>');
            $('#form-description').val(data.description || '');
            $('#form-active').prop('checked', data.is_active);
            $('#offcanvas-title').html('<i class="ti tabler-pencil me-2"></i>Edit Department');
            offcanvas.show();
        });
    });

    // Save
    $('#dept-form').on('submit', function (e) {
        e.preventDefault();

        var id = $('#form-id').val();
        var payload = {
            _token:      cfg.csrfToken,
            name:        $('#form-name').val(),
            slug:        $('#form-slug').val().trim() || null,
            color:       $('#form-color').val(),
            icon:        $('#form-icon').val().trim() || null,
            description: $('#form-description').val().trim() || null,
            sort_order:  parseInt($('#form-sort').val()) || 0,
            is_active:   $('#form-active').is(':checked') ? 1 : 0,
        };

        var url  = id ? cfg.baseUrl + '/' + id : cfg.baseUrl;
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
                Swal.fire({ icon: 'error', title: 'Error', html: msg });
            },
            complete: function () {
                $('#btn-save').prop('disabled', false).html('<i class="ti tabler-check me-1"></i> Save Department');
            }
        });
    });

    // Delete
    $(document).on('click', '.btn-delete', function () {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Delete Department?',
            text: 'Departments with existing tickets cannot be deleted.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Delete',
        }).then(function (result) {
            if (result.isConfirmed) {
                $.ajax({
                    url: cfg.baseUrl + '/' + id,
                    type: 'DELETE',
                    data: { _token: cfg.csrfToken },
                    success: function (res) {
                        dt.ajax.reload(null, false);
                        Swal.fire({ icon: 'success', title: res.message, timer: 1500, showConfirmButton: false });
                    },
                    error: function (xhr) {
                        Swal.fire({ icon: 'error', title: 'Cannot Delete', text: xhr.responseJSON?.message || 'Something went wrong.' });
                    }
                });
            }
        });
    });

})(jQuery);
