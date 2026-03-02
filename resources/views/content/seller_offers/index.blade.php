@extends('layouts.app')
@section('title', 'Seller Offers')

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
            <h4 class="mb-1"><i class="ti tabler-tag me-2"></i>Seller Offers</h4>
            <p class="text-muted mb-0">Manage all seller product offers</p>
        </div>
        <a href="{{ route('seller-offers.create') }}" class="btn btn-primary">
            <i class="ti tabler-plus me-1"></i> Add Offer
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3 bg-label-primary">
                            <i class="ti tabler-tag fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ number_format($stats['total']) }}</h5>
                            <small class="text-muted">Total Offers</small>
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
                            <h5 class="mb-0">{{ number_format($stats['active']) }}</h5>
                            <small class="text-muted">Active</small>
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
                            <i class="ti tabler-clock-pause fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ number_format($stats['inactive']) }}</h5>
                            <small class="text-muted">Inactive</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3 bg-label-danger">
                            <i class="ti tabler-ban fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ number_format($stats['suspended']) }}</h5>
                            <small class="text-muted">Suspended</small>
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
                <h5 class="mb-0"><i class="ti tabler-list-details me-2"></i>All Offers</h5>
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
                                        <option value="seller_id">Seller</option>
                                        <option value="product_id">Product</option>
                                        <option value="status">Status</option>
                                        <option value="sale_mode">Sale Mode</option>
                                        <option value="is_verified">Verified</option>
                                        <option value="created_at">Created Date</option>
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <select name="operator[]" class="form-select form-select-sm">
                                        <option value="=">Is equal</option>
                                        <option value="!=">Is not equal</option>
                                        <option value="like">Contains</option>
                                        <option value="not_like">Does not contain</option>
                                        <option value=">">Greater than</option>
                                        <option value="<">Less than</option>
                                        <option value=">=">Greater or equal</option>
                                        <option value="<=">Less or equal</option>
                                        <option value="in">In list</option>
                                        <option value="not_in">Not in list</option>
                                    </select>
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-row d-none">
                                        <i class="ti tabler-trash ti-xs"></i>
                                    </button>
                                </div>
                                <div class="col-md-12 value-wrapper">
                                    <input type="text" name="value[]" class="form-control form-control-sm">
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
                        <span class="fw-medium" style="font-size:.85rem">offers selected</span>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-label-primary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="ti tabler-refresh me-1"></i> Change Status
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item bulk-action" href="#" data-action="status" data-status="active">
                                        <i class="ti tabler-circle-check ti-xs me-2 text-success"></i> Mark Active
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item bulk-action" href="#" data-action="status" data-status="inactive">
                                        <i class="ti tabler-circle-x ti-xs me-2 text-warning"></i> Mark Inactive
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <button type="button" class="btn btn-sm btn-label-danger bulk-action" data-action="delete" data-url="{{ route('seller-offers.bulk-delete') }}">
                            <i class="ti tabler-trash me-1"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="table-responsive">
            <table class="table table-hover" id="offers-table">
                <thead class="table-light">
                    <tr>
                        <th width="40"><input type="checkbox" id="select-all" class="form-check-input"></th>
                        <th>Seller</th>
                        <th>Product</th>
                        <th>Retail</th>
                        <th>Wholesale 10–99</th>
                        <th>Wholesale 100+</th>
                        <th class="text-center">Status</th>
                        <th class="text-center" width="80">Actions</th>
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

const OffersPage = {

    table: null,

    init() {
        this.cacheDom();
        this.initTable();
        this.bindEvents();
    },

    cacheDom() {
        this.$table      = $('#offers-table');
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
                url: '{{ route('seller-offers.index') }}',
                data: d => {
                    const raw = this.$filterForm.serializeArray();
                    const filters = [];

                    for (let i = 0; i < raw.length; i += 3) {
                        filters.push({
                            field: raw[i]?.value,
                            operator: raw[i + 1]?.value,
                            value: raw[i + 2]?.value,
                        });
                    }
                    d.filters = filters;
                }
            },
            columns: [
                { data: 'checkbox', orderable: false, searchable: false },
                { data: 'seller', name: 'seller' },
                { data: 'product', name: 'product' },
                { data: 'retail_price' },
                { data: 'wholesale_10_99_price' },
                { data: 'wholesale_100_plus_price' },
                { data: 'status_badge', orderable: false },
                { data: 'actions', orderable: false }
            ],
            columnDefs: [
                { targets: [0], className: 'text-center align-middle' },
                { targets: [3, 4, 5], className: 'text-end align-middle' },
                { targets: [6, 7], className: 'text-center align-middle' }
            ],
            language: { emptyTable: 'No offers found', zeroRecords: 'No matching offers' }
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

        $(document).on('click', '.bulk-action', e => {
            e.preventDefault();
            const btn = $(e.currentTarget);
            this.handleBulk(btn.data('action'), btn.data('status'), btn.data('url'));
        });

        $(document).on('click', '.status-toggle-btn', e => {
            e.preventDefault();
            const btn = $(e.currentTarget);
            const id = btn.data('id');
            const status = btn.data('status');

            $.ajax({
                url: `/seller-offers/${id}/toggle-status`,
                method: 'POST',
                data: { status, _token: '{{ csrf_token() }}' },
                success: () => {
                    this.table.ajax.reload(null, false);
                    Swal.fire({ icon: 'success', title: 'Status updated', timer: 1200, showConfirmButton: false });
                },
                error: (xhr) => {
                    Swal.fire('Error', xhr.responseJSON?.message || 'Failed to update status', 'error');
                }
            });
        });

        $(document).on('click', '.delete-btn', e => {
            e.preventDefault();
            const id = $(e.currentTarget).data('id');

            Swal.fire({
                title: 'Delete this offer?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Delete',
                confirmButtonColor: '#d33'
            }).then(res => {
                if (!res.isConfirmed) return;

                $.ajax({
                    url: `/seller-offers/${id}`,
                    method: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: () => {
                        this.table.ajax.reload(null, false);
                        Swal.fire({ icon: 'success', title: 'Offer deleted', timer: 1200, showConfirmButton: false });
                    },
                    error: (xhr) => {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Failed to delete offer', 'error');
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
    },

    clearFilters() {
        this.$filterForm[0].reset();
        this.$filterRows.find('.filter-row:not(:first)').remove();
        this.table.ajax.reload();
        this.$clearBtn.addClass('d-none');
    },

    handleFieldChange($select) {
        const field = $select.val();
        const $row = $select.closest('.filter-row');
        const $wrap = $row.find('.value-wrapper');

        const inputs = {
            status: `
                <select name="value[]" class="form-select form-select-sm">
                    <option value="">Select</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="draft">Draft</option>
                    <option value="suspended">Suspended</option>
                </select>`,
            sale_mode: `
                <select name="value[]" class="form-select form-select-sm">
                    <option value="">Select</option>
                    <option value="retail">Retail</option>
                    <option value="wholesale">Wholesale</option>
                    <option value="both">Both</option>
                </select>`,
            is_verified: `
                <select name="value[]" class="form-select form-select-sm">
                    <option value="">Select</option>
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                </select>`,
            seller_id: `
                <select name="value[]" class="form-select form-select-sm select2">
                    <option value="">Select Seller</option>
                    @foreach($sellers as $s)
                        <option value="{{ $s->id }}">{{ $s->store_name }}</option>
                    @endforeach
                </select>`,
            product_id: `
                <select name="value[]" class="form-select form-select-sm select2">
                    <option value="">Select Product</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}">{{ $p->title }}</option>
                    @endforeach
                </select>`,
            created_at: `<input type="date" name="value[]" class="form-control form-control-sm">`
        };

        $wrap.html(inputs[field] || `<input type="text" name="value[]" class="form-control form-control-sm">`);
        $wrap.find('.select2').select2({ width: '100%' });
    },

    handleBulk(action, status, url) {
        const ids = $('.bulk-checkbox:checked').map((_, el) => el.value).get();

        if (!ids.length) {
            Swal.fire('Info', 'Select at least one offer', 'info');
            return;
        }

        if (action === 'delete') {
            this.confirmDelete(ids, url);
            return;
        }

        if (action === 'status') {
            this.confirmStatus(ids, status);
        }
    },

    confirmStatus(ids, status) {
        Swal.fire({
            title: 'Update status?',
            text: `Offers will be marked as "${status}"`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes'
        }).then(res => {
            if (!res.isConfirmed) return;

            $.post('{{ route('seller-offers.bulk-status') }}', {
                ids, status, _token: '{{ csrf_token() }}'
            }).done(() => this.afterBulk('Status updated'));
        });
    },

    confirmDelete(ids, url) {
        Swal.fire({
            title: 'Delete offers?',
            icon: 'error',
            showCancelButton: true,
            confirmButtonText: 'Delete'
        }).then(res => {
            if (!res.isConfirmed) return;

            $.post(url, { ids, _token: '{{ csrf_token() }}' })
                .done(() => this.afterBulk('Offers deleted'));
        });
    },

    afterBulk(msg) {
        this.table.ajax.reload(null, false);
        this.$selectAll.prop('checked', false);
        this.updateBulkBar();
        Swal.fire({ icon: 'success', title: msg, timer: 1200, showConfirmButton: false });
    }
};

$(document).ready(() => OffersPage.init());

})(jQuery);
</script>
@endpush
