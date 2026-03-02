@extends('layouts.app')
@section('title', 'Product Requests')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}">
<style>
    .bulk-bar { background:#f0f2ff; border-radius:8px; animation:bulkSlide .3s ease; }
    @keyframes bulkSlide { from { opacity:0; transform:translateY(-8px); } to { opacity:1; transform:translateY(0); } }
</style>
@endpush

@section('content')

    @include('partials.alerts')

    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1"><i class="ti tabler-git-pull-request me-2"></i>Product Requests</h4>
            <p class="text-muted mb-0">Manage all product requests from users</p>
        </div>
        <a href="{{ route('product-requests.create') }}" class="btn btn-primary">
            <i class="ti tabler-plus me-1"></i> Add Request
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3 bg-label-primary">
                            <i class="ti tabler-git-pull-request fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ number_format($stats['total']) }}</h5>
                            <small class="text-muted">Total Requests</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3 bg-label-warning">
                            <i class="ti tabler-clock fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ number_format($stats['pending']) }}</h5>
                            <small class="text-muted">Pending</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3 bg-label-success">
                            <i class="ti tabler-circle-check fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ number_format($stats['approved']) }}</h5>
                            <small class="text-muted">Approved</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3 bg-label-info">
                            <i class="ti tabler-checks fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ number_format($stats['completed']) }}</h5>
                            <small class="text-muted">Completed</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- DataTable Card -->
    <div class="card">
        <!-- Card Header -->
        <div class="card-header pb-0">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <h5 class="mb-0"><i class="ti tabler-list-details me-2"></i>All Requests</h5>
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-label-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#filter-collapse">
                        <i class="ti tabler-filter me-1"></i> Filters
                    </button>
                </div>
            </div>

            <!-- Collapsible Filter Row -->
            <div class="collapse mt-3" id="filter-collapse">
                <div class="pb-3 border-bottom">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <p class="fw-semibold mb-0">Advanced Filters</p>
                        <button type="button" id="closeFilters" class="btn btn-sm btn-icon">
                            <i class="ti tabler-x"></i>
                        </button>
                    </div>

                    <form id="filterForm">
                        <div id="filterRows">
                            <div class="row g-2 align-items-center filter-row mb-2">
                                <div class="col-md-6">
                                    <select name="field[]" class="form-select form-select-sm">
                                        <option value="">Select field</option>
                                        <option value="title">Title</option>
                                        <option value="status">Status</option>
                                        <option value="created_at">Created Date</option>
                                        <option value="category_id">Category</option>
                                        <option value="platform_id">Platform</option>
                                        <option value="type_id">Type</option>
                                        <option value="region_id">Region</option>
                                        <option value="language_id">Language</option>
                                        <option value="works_on_id">Works On</option>
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <select name="operator[]" class="form-select form-select-sm">
                                        <option value="=">Is equal to</option>
                                        <option value="like">Contains</option>
                                    </select>
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-row d-none">
                                        <i class="ti tabler-trash ti-xs"></i>
                                    </button>
                                </div>
                                <div class="col-md-12 value-wrapper">
                                    <input type="text" name="value[]" class="form-control form-control-sm" placeholder="Value">
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-3">
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="addFilter">
                                <i class="ti tabler-plus me-1 ti-xs"></i> Add Filter
                            </button>
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="ti tabler-check me-1 ti-xs"></i> Apply
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger d-none" id="clearFilters">
                                <i class="ti tabler-x me-1 ti-xs"></i> Clear
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Bulk Actions Bar -->
        <div class="card-body py-0">
            <div class="bulk-bar d-none py-2 px-3 mt-3" id="bulk-bar">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary rounded-pill fs-6" id="bulk-count">0</span>
                        <span class="fw-medium" style="font-size:.85rem">requests selected</span>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-label-primary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="ti tabler-refresh me-1"></i> Change Status
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item bulk-action" href="#" data-action="status" data-status="approved">
                                        <i class="ti tabler-circle-check ti-xs me-2 text-success"></i> Mark Approved
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item bulk-action" href="#" data-action="status" data-status="rejected">
                                        <i class="ti tabler-circle-x ti-xs me-2 text-danger"></i> Mark Rejected
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item bulk-action" href="#" data-action="status" data-status="completed">
                                        <i class="ti tabler-checks ti-xs me-2 text-info"></i> Mark Completed
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <button type="button" class="btn btn-sm btn-label-danger bulk-action" data-action="delete" data-url="{{ route('product-requests.bulk-delete') }}">
                            <i class="ti tabler-trash me-1"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="product-requests-table">
                <thead class="table-light">
                    <tr>
                        <th width="40"><input type="checkbox" id="select-all" class="form-check-input"></th>
                        <th>Request</th>
                        <th>Details</th>
                        <th>Source</th>
                        <th class="text-center">Status</th>
                        <th class="text-center" width="100">Actions</th>
                    </tr>
                </thead>
            </table>
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
        this.cacheDom();
        this.initTable();
        this.bindEvents();
        this.initSelect2();
    },

    cacheDom() {
        this.$table      = $('#product-requests-table');
        this.$filterForm = $('#filterForm');
        this.$filterRows = $('#filterRows');
        this.$addBtn     = $('#addFilter');
        this.$clearBtn   = $('#clearFilters');
        this.$selectAll  = $('#select-all');
        this.$bulkBar    = $('#bulk-bar');
        this.$bulkCount  = $('#bulk-count');
    },

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
            ],
            columnDefs: [
                { targets: [0], className: 'text-center align-middle' },
                { targets: [4, 5], className: 'text-center align-middle' }
            ],
            language: { emptyTable: 'No requests found', zeroRecords: 'No matching requests' }
        });
    },

    bindEvents() {
        this.$filterForm.on('submit', e => {
            e.preventDefault();
            this.table.ajax.reload();
            this.$clearBtn.removeClass('d-none');
        });

        this.$clearBtn.on('click', () => this.clearFilters());
        this.$addBtn.on('click', () => this.addRow());

        $(document).on('click', '.remove-row', e =>
            $(e.currentTarget).closest('.filter-row').remove()
        );

        $(document).on('change', 'select[name="field[]"]', e =>
            this.handleFieldChange($(e.currentTarget))
        );

        this.$selectAll.on('change', e => {
            $('.bulk-checkbox').prop('checked', e.target.checked);
            this.updateBulkBar();
        });

        $(document).on('change', '.bulk-checkbox', () => this.updateBulkBar());

        $('#closeFilters').on('click', () => {
            this.clearFilters();
            $('#filter-collapse').collapse('hide');
        });

        $('#filter-collapse').on('shown.bs.collapse', () => {
            this.initSelect2();
        });

        $(document).on('click', '.bulk-action', e => {
            e.preventDefault();
            const $btn = $(e.currentTarget);
            this.handleBulk(
                $btn.data('action'),
                $btn.data('status') ?? null,
                $btn.data('url') ?? null
            );
        });

        $(document).on('click', '.btn-delete', e => {
            e.preventDefault();
            const url = $(e.currentTarget).data('url');

            Swal.fire({
                title: 'Delete this request?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Delete',
                confirmButtonColor: '#d33'
            }).then(res => {
                if (!res.isConfirmed) return;

                $.ajax({
                    url: url,
                    method: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: () => {
                        this.table.ajax.reload(null, false);
                        Swal.fire({ icon: 'success', title: 'Request deleted', timer: 1200, showConfirmButton: false });
                    },
                    error: (xhr) => {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Failed to delete request', 'error');
                    }
                });
            });
        });
    },

    updateBulkBar() {
        const count = $('.bulk-checkbox:checked').length;
        this.$bulkCount.text(count);

        if (count > 0) {
            this.$bulkBar.removeClass('d-none');
        } else {
            this.$bulkBar.addClass('d-none');
        }
    },

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
                <select name="value[]" class="form-select form-select-sm">
                    <option value="">Select Status</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                    <option value="completed">Completed</option>
                </select>`,
            category_id: `
                <select name="value[]" class="form-select form-select-sm select2">
                    <option value="">Select Category</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>`,
            platform_id: `
                <select name="value[]" class="form-select form-select-sm select2">
                    <option value="">Select Platform</option>
                    @foreach($platforms as $platform)
                        <option value="{{ $platform->id }}">{{ $platform->name }}</option>
                    @endforeach
                </select>`,
            type_id: `
                <select name="value[]" class="form-select form-select-sm select2">
                    <option value="">Select Type</option>
                    @foreach($types as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                    @endforeach
                </select>`,
            region_id: `
                <select name="value[]" class="form-select form-select-sm select2">
                    <option value="">Select Region</option>
                    @foreach($regions as $region)
                        <option value="{{ $region->id }}">{{ $region->name }}</option>
                    @endforeach
                </select>`,
            language_id: `
                <select name="value[]" class="form-select form-select-sm select2">
                    <option value="">Select Language</option>
                    @foreach($languages as $lang)
                        <option value="{{ $lang->id }}">{{ $lang->name }}</option>
                    @endforeach
                </select>`,
            works_on_id: `
                <select name="value[]" class="form-select form-select-sm select2">
                    <option value="">Select Works On</option>
                    @foreach($workson as $os)
                        <option value="{{ $os->id }}">{{ $os->name }}</option>
                    @endforeach
                </select>`,
            created_at: `<input type="date" class="form-control form-control-sm" name="value[]">`
        };

        return inputs[field] || this.defaultInput();
    },

    defaultInput() {
        return `<input type="text" class="form-control form-control-sm" name="value[]" placeholder="Value">`;
    },

    initSelect2(context = document) {
        $(context).find('.select2').select2({
            width: '100%',
            dropdownParent: $('#filter-collapse')
        });
    },

    handleBulk(action, value, url) {
        const ids = $('.bulk-checkbox:checked').map((_, el) => el.value).get();

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
                ids, status, _token: '{{ csrf_token() }}'
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

            $.post(url, { ids, _token: '{{ csrf_token() }}' })
                .done(() => this.afterBulk('Requests deleted'));
        });
    },

    afterBulk(msg) {
        this.table.ajax.reload(null, false);
        this.$selectAll.prop('checked', false);
        this.updateBulkBar();
        Swal.fire({ icon: 'success', title: msg, timer: 1500, showConfirmButton: false });
    }
};

$(document).ready(() => RequestsPage.init());

})(jQuery);
</script>
@endpush
