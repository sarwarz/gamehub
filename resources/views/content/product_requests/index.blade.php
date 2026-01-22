@extends('layouts.app')
@section('title', 'Product Requests')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}">
@endpush

@section('content')
<div class="app-ecommerce-products">

    @include('partials.alerts')

    {{-- ================= FILTER PANEL ================= --}}
    <div class="card mb-3 p-4 collapse position-relative" id="filter-collapse">
        <button type="button"
                id="closeFilters"
                class="btn btn-sm btn-icon position-absolute top-0 end-0 m-2">
            <i class="ti tabler-x"></i>
        </button>

        <p>Filters</p>

        <form id="filterForm">
            <div id="filterRows">

                <div class="row g-2 align-items-center filter-row mb-2">

                    <div class="col-md-6">
                        <select name="field[]" class="form-select">
                            <option value="">Select field</option>

                            <option value="title">Title</option>
                            <option value="status">Status</option>
                            <option value="created_at">Created Date</option>

                            {{-- Relations --}}
                            <option value="category_id">Category</option>
                            <option value="platform_id">Platform</option>
                            <option value="type_id">Type</option>
                            <option value="region_id">Region</option>
                            <option value="language_id">Language</option>
                            <option value="works_on_id">Works On</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <select name="operator[]" class="form-select">
                            <option value="=">Is equal to</option>
                            <option value="like">Contains</option>
                        </select>
                    </div>

                    <div class="col-md-12 value-wrapper">
                        <input type="text" name="value[]" class="form-control" placeholder="Value">
                    </div>

                    <div class="col-md-1">
                        <button type="button"
                                class="btn btn-outline-danger remove-row d-none">
                            <i class="ti tabler-trash"></i>
                        </button>
                    </div>

                </div>
            </div>

            <div class="d-flex gap-2 mt-3">
                <button type="button" class="btn btn-outline-secondary" id="addFilter">
                    Add filter
                </button>
                <button type="submit" class="btn btn-primary">Apply</button>
                <button type="button"
                        class="btn btn-outline-danger d-none"
                        id="clearFilters">
                    Clear
                </button>
            </div>
        </form>
    </div>

    {{-- ================= TABLE ================= --}}
    <div class="card p-2">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <div class="btn-group">
                    <button class="btn btn-outline-secondary dropdown-toggle"
                            data-bs-toggle="dropdown">
                        Bulk Actions
                    </button>

                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item bulk-action"
                               href="#"
                               data-action="status"
                               data-status="approved">
                                Mark Approved
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item bulk-action"
                               href="#"
                               data-action="status"
                               data-status="rejected">
                                Mark Rejected
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item bulk-action"
                               href="#"
                               data-action="status"
                               data-status="completed">
                                Mark Completed
                            </a>
                        </li>

                        <li><hr class="dropdown-divider"></li>

                        <li>
                            <a class="dropdown-item bulk-action text-danger"
                               href="#"
                               data-action="delete"
                               data-url="{{ route('product-requests.bulk-delete') }}">
                                Delete Requests
                            </a>
                        </li>
                    </ul>
                </div>

                <button class="btn btn-outline-secondary"
                        data-bs-toggle="collapse"
                        data-bs-target="#filter-collapse">
                    Filters
                </button>
            </div>

            <a href="{{ route('product-requests.create') }}" class="btn btn-primary">
                <i class="ti tabler-plus"></i> Add Request
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle" id="product-requests-table">
                <thead>
                <tr>
                    <th width="30">
                        <input type="checkbox" id="select-all" class="form-check-input">
                    </th>
                    <th>Request</th>
                    <th>Details</th>
                    <th>Source</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>

</div>
@endsection
@push('page-js')
<script>
(function ($) {
'use strict';

const RequestsPage = {

    table: null,

    init() {
        this.cache();
        this.initTable();
        this.bindEvents();
        this.initSelect2();
    },

    cache() {
        this.$table      = $('#product-requests-table');
        this.$filterForm = $('#filterForm');
        this.$filterRows = $('#filterRows');
        this.$addBtn     = $('#addFilter');
        this.$clearBtn   = $('#clearFilters');
        this.$selectAll  = $('#select-all');
    },

    /* ===============================
     * DataTable
     =============================== */
    initTable() {
        this.table = this.$table.DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('product-requests.index') }}',
                data: d => {

                    const raw = this.$filterForm.serializeArray();
                    const filters = [];

                    for (let i = 0; i < raw.length; i += 3) {
                        filters.push({
                            field: raw[i]?.value ?? null,
                            operator: raw[i + 1]?.value ?? null,
                            value: raw[i + 2]?.value ?? null,
                        });
                    }

                    d.filters = filters;
                }
            },
            columns: [
                { data: 'checkbox', orderable: false, searchable: false },
                { data: 'request_info', orderable: false },
                { data: 'meta', orderable: false },
                { data: 'source', orderable: false },
                { data: 'status_badge', orderable: false },
                { data: 'actions', orderable: false }
            ]
        });
    },

    /* ===============================
     * Events
     =============================== */
    bindEvents() {

        // Apply filters
        this.$filterForm.on('submit', e => {
            e.preventDefault();
            this.table.ajax.reload();
            this.$clearBtn.removeClass('d-none');
        });

        // Clear filters
        this.$clearBtn.on('click', () => this.clearFilters());

        // Add filter row
        this.$addBtn.on('click', () => this.addRow());

        // Remove filter row
        $(document).on('click', '.remove-row', e =>
            $(e.currentTarget).closest('.filter-row').remove()
        );

        // Change field → switch value input
        $(document).on('change', 'select[name="field[]"]', e =>
            this.handleFieldChange($(e.currentTarget))
        );

        // Select all
        this.$selectAll.on('change', e =>
            $('.bulk-checkbox').prop('checked', e.target.checked)
        );

        // Close filter panel
        $('#closeFilters').on('click', () => {
            this.clearFilters();
            $('#filter-collapse').collapse('hide');
        });

        // Re-init Select2 when panel opens
        $('#filter-collapse').on('shown.bs.collapse', () => {
            this.initSelect2();
        });

        // Bulk actions
        $(document).on('click', '.bulk-action', e => {
            e.preventDefault();

            const $btn = $(e.currentTarget);

            this.handleBulk(
                $btn.data('action'),
                $btn.data('status') ?? null,
                $btn.data('url') ?? null
            );
        });
    },

    /* ===============================
     * Filters
     =============================== */
    addRow() {
        const $row = this.$filterRows.find('.filter-row:first').clone();
        $row.find('select, input').val('');
        $row.find('.remove-row').removeClass('d-none');
        this.$filterRows.append($row);
        this.initSelect2($row);
    },

    clearFilters() {
        this.$filterForm[0].reset();
        this.$filterRows.find('.filter-row:not(:first)').remove();
        this.$filterRows.find('.value-wrapper').html(this.defaultInput());
        this.table.ajax.reload();
        this.$clearBtn.addClass('d-none');
    },

    handleFieldChange($select) {
        const field = $select.val();
        const $row  = $select.closest('.filter-row');

        $row.find('.value-wrapper').html(this.getFieldInput(field));
        this.initSelect2($row);

        if (['created_at', 'status'].includes(field)) {
            $row.find('select[name="operator[]"]').val('=');
        }
    },

    getFieldInput(field) {

        const inputs = {

            status: `
                <select name="value[]" class="form-select">
                    <option value="">Select Status</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                    <option value="completed">Completed</option>
                </select>`,

            category_id: `
                <select name="value[]" class="form-select select2">
                    <option value="">Select Category</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>`,

            platform_id: `
                <select name="value[]" class="form-select select2">
                    <option value="">Select Platform</option>
                    @foreach($platforms as $platform)
                        <option value="{{ $platform->id }}">{{ $platform->name }}</option>
                    @endforeach
                </select>`,

            type_id: `
                <select name="value[]" class="form-select select2">
                    <option value="">Select Type</option>
                    @foreach($types as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                    @endforeach
                </select>`,

            region_id: `
                <select name="value[]" class="form-select select2">
                    <option value="">Select Region</option>
                    @foreach($regions as $region)
                        <option value="{{ $region->id }}">{{ $region->name }}</option>
                    @endforeach
                </select>`,

            language_id: `
                <select name="value[]" class="form-select select2">
                    <option value="">Select Language</option>
                    @foreach($languages as $lang)
                        <option value="{{ $lang->id }}">{{ $lang->name }}</option>
                    @endforeach
                </select>`,

            works_on_id: `
                <select name="value[]" class="form-select select2">
                    <option value="">Select Works On</option>
                    @foreach($workson as $os)
                        <option value="{{ $os->id }}">{{ $os->name }}</option>
                    @endforeach
                </select>`,

            created_at: `
                <input type="date" class="form-control" name="value[]">`
        };

        return inputs[field] || this.defaultInput();
    },

    defaultInput() {
        return `<input type="text"
                       class="form-control"
                       name="value[]"
                       placeholder="Value">`;
    },

    /* ===============================
     * Select2
     =============================== */
    initSelect2(context = document) {
        $(context).find('.select2').select2({
            width: '100%',
            dropdownParent: $('#filter-collapse')
        });
    },

    /* ===============================
     * Bulk Actions
     =============================== */
    handleBulk(action, value, url) {

        const ids = $('.bulk-checkbox:checked')
            .map((_, el) => el.value)
            .get();

        if (!ids.length) {
            Swal.fire('Info', 'Select at least one request', 'info');
            return;
        }

        if (action === 'delete') {
            this.confirmDelete(ids, url);
            return;
        }

        if (action === 'status') {
            this.confirmStatus(ids, value);
        }
    },

    confirmStatus(ids, status) {
        Swal.fire({
            title: 'Change status?',
            text: `Requests will be marked as "${status}".`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes'
        }).then(res => {
            if (!res.isConfirmed) return;

            $.post('{{ route('product-requests.bulk-status') }}', {
                ids,
                status,
                _token: '{{ csrf_token() }}'
            }).done(() => this.afterBulk('Status updated'));
        });
    },

    confirmDelete(ids, url) {
        Swal.fire({
            title: 'Delete requests?',
            icon: 'error',
            showCancelButton: true,
            confirmButtonText: 'Delete'
        }).then(res => {
            if (!res.isConfirmed) return;

            $.post(url, {
                ids,
                _token: '{{ csrf_token() }}'
            }).done(() => this.afterBulk('Requests deleted'));
        });
    },

    afterBulk(msg) {
        this.table.ajax.reload(null, false);
        this.$selectAll.prop('checked', false);

        Swal.fire({
            icon: 'success',
            title: msg,
            timer: 1500,
            showConfirmButton: false
        });
    }
};

$(document).ready(() => RequestsPage.init());

})(jQuery);
</script>
@endpush
