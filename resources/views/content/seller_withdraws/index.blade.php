@extends('layouts.app')
@section('title', 'Seller Withdrawals')

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
            <h4 class="mb-1"><i class="ti tabler-cash me-2"></i>Seller Withdrawals</h4>
            <p class="text-muted mb-0">Manage seller withdrawal requests and payouts</p>
        </div>
        <a href="{{ route('seller-withdraws.pending') }}" class="btn btn-label-warning">
            <i class="ti tabler-clock me-1"></i> Pending Only
        </a>
    </div>

    {{-- Stats Cards --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3 bg-label-primary">
                            <i class="ti tabler-file-invoice fs-4"></i>
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
                            <i class="ti tabler-currency-dollar fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ format_currency($stats['amount']) }}</h5>
                            <small class="text-muted">Total Paid</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- DataTable Card --}}
    <div class="card">
        {{-- Card Header --}}
        <div class="card-header pb-0">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <h5 class="mb-0"><i class="ti tabler-list-details me-2"></i>All Withdrawals</h5>
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-label-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#filter-collapse" aria-expanded="false">
                        <i class="ti tabler-filter me-1"></i> Filters
                    </button>
                </div>
            </div>

            {{-- Collapsible Filter Row --}}
            <div class="collapse mt-3" id="filter-collapse">
                <div class="row g-3 pb-3 border-bottom">
                    <div class="col-md-4 col-sm-6">
                        <label class="form-label small text-muted">Status</label>
                        <select id="filter-status" class="form-select form-select-sm">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-4 col-sm-6">
                        <label class="form-label small text-muted">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-primary" id="apply-filters">
                                <i class="ti tabler-check me-1"></i> Apply
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" id="clear-filters">
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
                        <span class="fw-medium" style="font-size:.85rem">withdrawals selected</span>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-sm btn-label-success bulk-action" data-action="approve">
                            <i class="ti tabler-check me-1"></i> Approve
                        </button>
                        <button type="button" class="btn btn-sm btn-label-danger bulk-action" data-action="reject">
                            <i class="ti tabler-x me-1"></i> Reject
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="table table-hover" id="withdraws-table">
                <thead class="table-light">
                    <tr>
                        <th width="40"><input type="checkbox" class="form-check-input" id="select-all"></th>
                        <th>Seller</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th class="text-center">Status</th>
                        <th>Date</th>
                        <th width="80" class="text-center">Actions</th>
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

const WithdrawsPage = {
    table: null,

    init() {
        this.cacheDom();
        this.initDataTable();
        this.bindEvents();
    },

    cacheDom() {
        this.$table     = $('#withdraws-table');
        this.$selectAll = $('#select-all');
        this.$bulkBar   = $('#bulk-bar');
        this.$bulkCount = $('#bulk-count');
    },

    initDataTable() {
        this.table = this.$table.DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('seller-withdraws.index') }}',
                data: d => {
                    d.status = $('#filter-status').val();
                }
            },
            order: [[5, 'desc']],
            columns: [
                { data: 'checkbox', orderable: false, searchable: false, className: 'pe-0' },
                { data: 'seller', orderable: false, searchable: true },
                { data: 'amount', orderable: false, searchable: false },
                { data: 'method', name: 'method' },
                { data: 'status_badge', orderable: false, searchable: false, className: 'text-center' },
                { data: 'created_at', name: 'created_at' },
                { data: 'actions', orderable: false, searchable: false, className: 'text-center' }
            ],
            language: {
                emptyTable: '<div class="py-4 text-center"><i class="ti tabler-cash-off ti-xl text-muted mb-2 d-block"></i><span class="text-muted">No withdraw requests</span></div>',
                zeroRecords: '<div class="py-3 text-center text-muted">No matching requests</div>'
            }
        });
    },

    bindEvents() {
        const self = this;

        $('#apply-filters').on('click', () => self.table.ajax.reload());

        $('#clear-filters').on('click', () => {
            $('#filter-status').val('');
            self.table.ajax.reload();
        });

        this.$selectAll.on('change', function () {
            $('.bulk-checkbox').prop('checked', this.checked);
            self.syncBulkBar();
        });

        $(document).on('change', '.bulk-checkbox', () => self.syncBulkBar());

        $(document).on('click', '.bulk-action', function (e) {
            e.preventDefault();
            const action = $(this).data('action');
            self.handleBulkAction(action);
        });

        $(document).on('click', '.approve-btn', function () {
            const url = $(this).data('url');
            Swal.fire({
                title: 'Approve this withdrawal?',
                text: 'The seller will be paid and balance deducted.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Approve',
                customClass: { confirmButton: 'btn btn-success me-3', cancelButton: 'btn btn-label-secondary' },
                buttonsStyling: false
            }).then(res => {
                if (!res.isConfirmed) return;
                $.post(url, { _token: '{{ csrf_token() }}' })
                    .done(r => {
                        self.table.ajax.reload(null, false);
                        Swal.fire({ icon: 'success', title: r.message || 'Approved', timer: 1500, showConfirmButton: false, timerProgressBar: true });
                    })
                    .fail(xhr => Swal.fire('Error', xhr.responseJSON?.message || 'Failed to approve', 'error'));
            });
        });

        $(document).on('click', '.reject-btn', function () {
            const url = $(this).data('url');
            Swal.fire({
                title: 'Reject this withdrawal?',
                input: 'textarea',
                inputLabel: 'Reason (optional)',
                inputPlaceholder: 'Enter rejection reason...',
                showCancelButton: true,
                confirmButtonText: 'Reject',
                customClass: { confirmButton: 'btn btn-danger me-3', cancelButton: 'btn btn-label-secondary' },
                buttonsStyling: false
            }).then(res => {
                if (!res.isConfirmed) return;
                $.post(url, { _token: '{{ csrf_token() }}', note: res.value || '' })
                    .done(r => {
                        self.table.ajax.reload(null, false);
                        Swal.fire({ icon: 'success', title: r.message || 'Rejected', timer: 1500, showConfirmButton: false, timerProgressBar: true });
                    })
                    .fail(xhr => Swal.fire('Error', xhr.responseJSON?.message || 'Failed to reject', 'error'));
            });
        });
    },

    syncBulkBar() {
        const count = $('.bulk-checkbox:checked').length;
        this.$bulkCount.text(count);
        count > 0 ? this.$bulkBar.removeClass('d-none') : this.$bulkBar.addClass('d-none');

        const total = $('.bulk-checkbox').length;
        this.$selectAll.prop('checked', count > 0 && count === total);
    },

    handleBulkAction(action) {
        const ids = $('.bulk-checkbox:checked').map((_, el) => el.value).get();
        if (!ids.length) {
            Swal.fire({ icon: 'info', title: 'No Selection', text: 'Please select at least one withdrawal.', timer: 2000, showConfirmButton: false });
            return;
        }

        const self = this;
        const label = action === 'approve' ? 'approve' : 'reject';

        Swal.fire({
            title: `${label.charAt(0).toUpperCase() + label.slice(1)} selected withdrawals?`,
            text: `${ids.length} withdrawal(s) will be ${label}d.`,
            icon: action === 'approve' ? 'question' : 'warning',
            showCancelButton: true,
            confirmButtonText: `Yes, ${label}`,
            customClass: {
                confirmButton: action === 'approve' ? 'btn btn-success me-3' : 'btn btn-danger me-3',
                cancelButton: 'btn btn-label-secondary'
            },
            buttonsStyling: false
        }).then(res => {
            if (!res.isConfirmed) return;

            let completed = 0;
            const total = ids.length;
            const routeBase = action === 'approve'
                ? '{{ url("dashboard/seller-withdraws") }}/__ID__/approve'
                : '{{ url("dashboard/seller-withdraws") }}/__ID__/reject';

            ids.forEach(id => {
                const url = routeBase.replace('__ID__', id);
                $.post(url, { _token: '{{ csrf_token() }}' })
                    .always(() => {
                        completed++;
                        if (completed === total) {
                            self.table.ajax.reload(null, false);
                            self.$selectAll.prop('checked', false);
                            self.$bulkBar.addClass('d-none');
                            Swal.fire({ icon: 'success', title: `${total} withdrawal(s) ${label}d`, showConfirmButton: false, timer: 1500, timerProgressBar: true });
                        }
                    });
            });
        });
    }
};

$(document).ready(() => WithdrawsPage.init());

})(jQuery);
</script>
@endpush
