@extends('layouts.app')
@section('title', 'Coupons')

@push('page-css')
<style>
    .filter-panel { border-left: 3px solid var(--bs-primary); }
    .bulk-bar { background:#f0f2ff; border-radius:8px; animation:bulkSlide .3s ease; }
    @keyframes bulkSlide { from { opacity:0; transform:translateY(-8px); } to { opacity:1; transform:translateY(0); } }
</style>
@endpush

@section('content')
<div class="app-ecommerce-coupons">

    @include('partials.alerts')

    {{-- Page Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="ti tabler-ticket ti-md me-1 text-primary"></i> Coupons</h4>
            <p class="text-muted mb-0">Manage discount codes and promotional coupons</p>
        </div>
        <a href="{{ route('coupons.create') }}" class="btn btn-primary">
            <i class="ti tabler-plus ti-xs me-1"></i> Add Coupon
        </a>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3 bg-label-primary">
                            <i class="ti tabler-ticket fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ number_format($stats['total']) }}</h5>
                            <small class="text-muted">Total Coupons</small>
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
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3 bg-label-danger">
                            <i class="ti tabler-clock-off fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ number_format($stats['expired']) }}</h5>
                            <small class="text-muted">Expired</small>
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
                            <i class="ti tabler-building-store fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ number_format($stats['seller']) }}</h5>
                            <small class="text-muted">Seller Coupons</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Panel --}}
    <div class="card mb-4 collapse" id="filter-collapse">
        <div class="card-body filter-panel">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="mb-0"><i class="ti tabler-filter ti-sm me-1"></i> Filters</h6>
                <button type="button" class="btn-close" data-bs-toggle="collapse" data-bs-target="#filter-collapse"></button>
            </div>
            <form id="filterForm">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label small text-muted">Status</label>
                        <select name="status" id="filter-status" class="form-select form-select-sm">
                            <option value="">All Statuses</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="expired">Expired</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small text-muted">Type</label>
                        <select name="type" id="filter-type" class="form-select form-select-sm">
                            <option value="">All Types</option>
                            <option value="fixed">Fixed</option>
                            <option value="percent">Percentage</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small text-muted">Scope</label>
                        <select name="scope" id="filter-scope" class="form-select form-select-sm">
                            <option value="">All</option>
                            <option value="global">Global (Admin)</option>
                            <option value="seller">Seller</option>
                        </select>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="ti tabler-check ti-xs me-1"></i> Apply
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger d-none" id="clearFilters">
                        <i class="ti tabler-x ti-xs me-1"></i> Clear
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Bulk Action Bar --}}
    <div class="bulk-bar p-3 mb-3 d-none" id="bulk-bar">
        <div class="d-flex align-items-center justify-content-between">
            <span class="fw-semibold"><span id="bulk-count">0</span> coupon(s) selected</span>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-danger" id="bulk-delete-btn">
                    <i class="ti tabler-trash ti-xs me-1"></i> Delete Selected
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="bulk-cancel-btn">Cancel</button>
            </div>
        </div>
    </div>

    {{-- Main Card --}}
    <div class="card shadow-sm">
        <div class="card-header border-bottom">
            <div class="d-flex flex-wrap justify-content-between align-items-center row-gap-2">
                <div>
                    <h5 class="card-title mb-0">All Coupons</h5>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-label-secondary" data-bs-toggle="collapse" data-bs-target="#filter-collapse">
                        <i class="ti tabler-filter ti-xs me-1"></i> Filters
                    </button>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="coupon-table" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <th width="30"><input type="checkbox" class="form-check-input" id="select-all"></th>
                        <th width="40">#</th>
                        <th>Code</th>
                        <th>Discount</th>
                        <th>Usage</th>
                        <th>Restrictions</th>
                        <th>Expires</th>
                        <th class="text-center">Status</th>
                        <th class="text-center" width="80">Actions</th>
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

    const CouponsPage = {
        table: null,

        init() {
            this.cacheDom();
            this.initDataTable();
            this.bindEvents();
        },

        cacheDom() {
            this.$table      = $('#coupon-table');
            this.$filterForm = $('#filterForm');
            this.$clearBtn   = $('#clearFilters');
            this.$selectAll  = $('#select-all');
            this.$bulkBar    = $('#bulk-bar');
            this.$bulkCount  = $('#bulk-count');
        },

        initDataTable() {
            this.table = this.$table.DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('coupons.index') }}',
                    data: d => {
                        d.status = $('#filter-status').val();
                        d.type   = $('#filter-type').val();
                        d.scope  = $('#filter-scope').val();
                    }
                },
                order: [[2, 'asc']],
                pageLength: 25,
                columns: [
                    { data: 'checkbox', orderable: false, searchable: false, className: 'pe-0' },
                    { data: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'code_col', name: 'code' },
                    { data: 'discount', orderable: false },
                    { data: 'usage', name: 'used' },
                    { data: 'restrictions', orderable: false },
                    { data: 'expiry', orderable: false },
                    { data: 'status', orderable: false, className: 'text-center' },
                    { data: 'actions', orderable: false, searchable: false, className: 'text-center' }
                ],
                language: {
                    emptyTable: '<div class="py-4 text-center"><i class="ti tabler-ticket-off ti-xl text-muted mb-2 d-block"></i><span class="text-muted">No coupons found</span></div>'
                }
            });
        },

        bindEvents() {
            this.$filterForm.on('submit', e => {
                e.preventDefault();
                this.table.ajax.reload();
                this.$clearBtn.removeClass('d-none');
            });

            this.$clearBtn.on('click', () => {
                this.$filterForm[0].reset();
                this.table.ajax.reload();
                this.$clearBtn.addClass('d-none');
            });

            this.$selectAll.on('change', e => {
                $('.bulk-checkbox').prop('checked', e.target.checked);
                this.syncBulkBar();
            });

            $(document).on('change', '.bulk-checkbox', () => this.syncBulkBar());

            $('#bulk-cancel-btn').on('click', () => {
                this.$selectAll.prop('checked', false);
                $('.bulk-checkbox').prop('checked', false);
                this.syncBulkBar();
            });

            $('#bulk-delete-btn').on('click', () => this.bulkDelete());

            $(document).on('click', '.delete-btn', e => {
                e.preventDefault();
                const url = $(e.currentTarget).data('url');
                Swal.fire({
                    title: 'Delete Coupon?',
                    text: 'This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete',
                    customClass: { confirmButton: 'btn btn-danger me-3', cancelButton: 'btn btn-label-secondary' },
                    buttonsStyling: false
                }).then(r => {
                    if (!r.isConfirmed) return;
                    $.ajax({
                        url,
                        type: 'POST',
                        data: { _token: $('meta[name="csrf-token"]').attr('content'), _method: 'DELETE' },
                        success: () => {
                            this.table.ajax.reload(null, false);
                            Swal.fire({ icon: 'success', title: 'Coupon deleted', showConfirmButton: false, timer: 1500, timerProgressBar: true });
                        },
                        error: () => Swal.fire({ icon: 'error', title: 'Failed', timer: 1500, showConfirmButton: false })
                    });
                });
            });
        },

        syncBulkBar() {
            const count = $('.bulk-checkbox:checked').length;
            this.$bulkCount.text(count);
            this.$bulkBar.toggleClass('d-none', count === 0);
        },

        bulkDelete() {
            const ids = $('.bulk-checkbox:checked').map((_, el) => el.value).get();
            if (!ids.length) return;

            Swal.fire({
                title: 'Delete Coupons?',
                text: `${ids.length} coupon(s) will be permanently deleted.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete',
                customClass: { confirmButton: 'btn btn-danger me-3', cancelButton: 'btn btn-label-secondary' },
                buttonsStyling: false
            }).then(r => {
                if (!r.isConfirmed) return;
                $.post('{{ route('coupons.bulk-delete') }}', { ids, _token: '{{ csrf_token() }}' })
                    .done(() => {
                        this.table.ajax.reload(null, false);
                        this.$selectAll.prop('checked', false);
                        this.syncBulkBar();
                        Swal.fire({ icon: 'success', title: 'Coupons deleted', showConfirmButton: false, timer: 1500, timerProgressBar: true });
                    })
                    .fail(() => Swal.fire({ icon: 'error', title: 'Failed', timer: 1500, showConfirmButton: false }));
            });
        }
    };

    $(document).ready(() => CouponsPage.init());

})(jQuery);
</script>
@endpush
