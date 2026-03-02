@extends('layouts.app')
@section('title', 'Sellers')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}">
<style>
.bulk-bar { background:#f0f2ff; border-radius:8px; animation:bulkSlide .3s ease; }
@keyframes bulkSlide { from { opacity:0; transform:translateY(-8px); } to { opacity:1; transform:translateY(0); } }
</style>
@endpush

@section('content')

    @include('partials.alerts')

    {{-- Page Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1"><i class="ti tabler-building-store me-2"></i>Sellers</h4>
            <p class="text-muted mb-0">Manage your marketplace sellers</p>
        </div>
        <a href="{{ route('sellers.create') }}" class="btn btn-primary">
            <i class="ti tabler-plus me-1"></i> Add Seller
        </a>
    </div>

    {{-- Stats Cards --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3 bg-label-primary">
                            <i class="ti tabler-building-store fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ $stats['total'] }}</h5>
                            <small class="text-muted">Total Sellers</small>
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
                            <h5 class="mb-0">{{ $stats['active'] }}</h5>
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
                            <i class="ti tabler-clock fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ $stats['pending'] }}</h5>
                            <small class="text-muted">Pending</small>
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
                            <h5 class="mb-0">{{ $stats['suspended'] }}</h5>
                            <small class="text-muted">Suspended</small>
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
                <h5 class="mb-0"><i class="ti tabler-list-details me-2"></i>All Sellers</h5>
                <div class="d-flex align-items-center gap-2">
                    <div class="btn-group">
                        <button type="button" class="btn btn-label-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ti tabler-settings-2 me-1 ti-xs"></i> Bulk Actions
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><h6 class="dropdown-header">Change Status</h6></li>
                            <li>
                                <a class="dropdown-item bulk-status-action" href="#" data-status="active">
                                    <i class="ti tabler-circle-check ti-xs me-2 text-success"></i> Active
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item bulk-status-action" href="#" data-status="suspended">
                                    <i class="ti tabler-ban ti-xs me-2 text-danger"></i> Suspended
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item bulk-status-action" href="#" data-status="rejected">
                                    <i class="ti tabler-circle-x ti-xs me-2 text-warning"></i> Rejected
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="#" id="bulk-delete">
                                    <i class="ti tabler-trash ti-xs me-2"></i> Delete Selected
                                </a>
                            </li>
                        </ul>
                    </div>
                    <button class="btn btn-label-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#filter-collapse">
                        <i class="ti tabler-filter me-1"></i> Filters
                    </button>
                </div>
            </div>

            {{-- Collapsible Filters --}}
            <div class="collapse mt-3" id="filter-collapse">
                <div class="row g-3 pb-3 border-bottom">
                    <div class="col-md-4">
                        <label class="form-label small text-muted">Status</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="ti tabler-filter ti-xs"></i></span>
                            <select class="form-select form-select-sm" id="filter-status">
                                <option value="">All Statuses</option>
                                <option value="active">Active</option>
                                <option value="pending">Pending</option>
                                <option value="suspended">Suspended</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small text-muted">Verified</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="ti tabler-shield-check ti-xs"></i></span>
                            <select class="form-select form-select-sm" id="filter-verified">
                                <option value="">All</option>
                                <option value="yes">Verified</option>
                                <option value="no">Unverified</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-end gap-2">
                        <button type="button" class="btn btn-sm btn-primary" id="apply-filters">
                            <i class="ti tabler-check me-1 ti-xs"></i> Apply
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger" id="clear-filters">
                            <i class="ti tabler-x me-1 ti-xs"></i> Clear
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bulk Bar --}}
        <div class="card-body py-0">
            <div class="bulk-bar d-none px-3 py-2 my-2 d-flex align-items-center justify-content-between" id="bulk-bar">
                <span class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary" id="selected-count">0</span>
                    <span class="text-muted small">seller(s) selected</span>
                </span>
            </div>
        </div>

        {{-- Table --}}
        <div class="table-responsive">
            <table id="sellers-table" class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="40" class="text-center"><input type="checkbox" class="form-check-input" id="select-all"></th>
                        <th>Seller</th>
                        <th>Status</th>
                        <th>Verified</th>
                        <th>Balance</th>
                        <th>Total Sales</th>
                        <th class="text-center" width="80">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('page-js')
<script>
(function($) {
'use strict';

const SellersPage = {
    table: null,

    init() {
        this.initDataTable();
        this.bindEvents();
    },

    initDataTable() {
        this.table = $('#sellers-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('sellers.index') }}',
                data: d => {
                    d.status   = $('#filter-status').val();
                    d.verified = $('#filter-verified').val();
                }
            },
            columns: [
                { data: 'checkbox', orderable: false, searchable: false },
                { data: 'seller_column', orderable: false, searchable: true },
                { data: 'status_badge', orderable: false, searchable: false },
                { data: 'is_verified_badge', orderable: false, searchable: false },
                { data: 'balance', orderable: false, searchable: false },
                { data: 'total_sales', searchable: false },
                { data: 'actions', orderable: false, searchable: false }
            ],
            columnDefs: [
                { targets: [0], className: 'text-center align-middle' },
                { targets: [2, 3, 4, 5, 6], className: 'text-center align-middle' }
            ],
            drawCallback: () => {
                this.syncBulkBar();
            },
            language: {
                emptyTable: '<div class="py-4 text-center"><i class="ti tabler-building-store ti-xl text-muted mb-2 d-block"></i><span class="text-muted">No sellers found</span></div>',
                zeroRecords: '<div class="py-3 text-center text-muted">No matching sellers</div>'
            }
        });
    },

    bindEvents() {
        $('#apply-filters').on('click', () => this.table.ajax.reload());

        $('#clear-filters').on('click', () => {
            $('#filter-status').val('');
            $('#filter-verified').val('');
            this.table.ajax.reload();
        });

        $('#select-all').on('change', function() {
            $('.bulk-checkbox').prop('checked', this.checked);
            SellersPage.syncBulkBar();
        });

        $(document).on('change', '.bulk-checkbox', () => {
            const total = $('.bulk-checkbox').length;
            const checked = $('.bulk-checkbox:checked').length;
            $('#select-all').prop('checked', checked === total && total > 0);
            this.syncBulkBar();
        });

        $(document).on('click', '.bulk-status-action', function(e) {
            e.preventDefault();
            SellersPage.handleBulkStatus($(this).data('status'));
        });

        $('#bulk-delete').on('click', function(e) {
            e.preventDefault();
            SellersPage.handleBulkDelete();
        });

        $(document).on('click', '.delete-btn', function() {
            const url = $(this).data('url');
            Swal.fire({
                title: 'Delete this seller?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete',
                cancelButtonText: 'Cancel',
                customClass: { confirmButton: 'btn btn-danger me-3', cancelButton: 'btn btn-label-secondary' },
                buttonsStyling: false
            }).then(res => {
                if (!res.isConfirmed) return;
                $.ajax({ url, method: 'DELETE', data: { _token: '{{ csrf_token() }}' } })
                    .done(() => {
                        SellersPage.table.ajax.reload(null, false);
                        Swal.fire({ icon: 'success', title: 'Seller deleted', showConfirmButton: false, timer: 1500, timerProgressBar: true });
                    })
                    .fail(() => Swal.fire({ icon: 'error', title: 'Delete failed', showConfirmButton: false, timer: 1500 }));
            });
        });
    },

    syncBulkBar() {
        const count = $('.bulk-checkbox:checked').length;
        const $bar = $('#bulk-bar');
        if (count > 0) {
            $bar.removeClass('d-none');
            $('#selected-count').text(count);
        } else {
            $bar.addClass('d-none');
        }
    },

    getSelectedIds() {
        return $('.bulk-checkbox:checked').map((_, el) => el.value).get();
    },

    handleBulkStatus(status) {
        const ids = this.getSelectedIds();
        if (!ids.length) {
            Swal.fire({ icon: 'info', title: 'No Selection', text: 'Please select at least one seller.', timer: 2000, showConfirmButton: false });
            return;
        }

        Swal.fire({
            title: 'Change Seller Status?',
            html: `<span class="text-muted">Selected sellers will be marked as <strong>${status}</strong>.</span>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, change',
            cancelButtonText: 'Cancel',
            customClass: { confirmButton: 'btn btn-primary me-3', cancelButton: 'btn btn-label-secondary' },
            buttonsStyling: false
        }).then(res => {
            if (!res.isConfirmed) return;
            $.post('{{ route('sellers.bulk-status') }}', { ids, status, _token: '{{ csrf_token() }}' })
                .done(() => {
                    this.afterBulkAction();
                    Swal.fire({ icon: 'success', title: 'Status updated', showConfirmButton: false, timer: 1500, timerProgressBar: true });
                })
                .fail(() => Swal.fire({ icon: 'error', title: 'Failed', text: 'Could not update status.', timer: 2000, showConfirmButton: false }));
        });
    },

    handleBulkDelete() {
        const ids = this.getSelectedIds();
        if (!ids.length) {
            Swal.fire({ icon: 'info', title: 'No Selection', text: 'Please select at least one seller.', timer: 2000, showConfirmButton: false });
            return;
        }

        Swal.fire({
            title: 'Delete selected sellers?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete',
            cancelButtonText: 'Cancel',
            customClass: { confirmButton: 'btn btn-danger me-3', cancelButton: 'btn btn-label-secondary' },
            buttonsStyling: false
        }).then(res => {
            if (!res.isConfirmed) return;
            $.post('{{ route('sellers.bulk-delete') }}', { ids, _token: '{{ csrf_token() }}' })
                .done(() => {
                    this.afterBulkAction();
                    Swal.fire({ icon: 'success', title: 'Sellers deleted', showConfirmButton: false, timer: 1500, timerProgressBar: true });
                })
                .fail(() => Swal.fire({ icon: 'error', title: 'Failed', text: 'Could not delete sellers.', timer: 2000, showConfirmButton: false }));
        });
    },

    afterBulkAction() {
        this.table.ajax.reload(null, false);
        $('#select-all').prop('checked', false);
        this.syncBulkBar();
    }
};

$(document).ready(() => SellersPage.init());

})(jQuery);
</script>
@endpush
