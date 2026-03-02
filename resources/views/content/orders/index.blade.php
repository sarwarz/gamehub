@extends('layouts.app')
@section('title', 'Orders')

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
            <h4 class="mb-1"><i class="ti tabler-shopping-cart me-2"></i>Orders</h4>
            <p class="text-muted mb-0">Manage and process all customer orders</p>
        </div>
        <a href="{{ route('orders.create') }}" class="btn btn-primary">
            <i class="ti tabler-plus me-1"></i> New Order
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3 bg-label-primary">
                            <i class="ti tabler-shopping-cart fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ number_format($stats['total']) }}</h5>
                            <small class="text-muted">Total Orders</small>
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
                            <h5 class="mb-0">{{ number_format($stats['completed']) }}</h5>
                            <small class="text-muted">Completed</small>
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
                            <h5 class="mb-0">{{ format_currency($stats['revenue']) }}</h5>
                            <small class="text-muted">Revenue</small>
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
                <h5 class="mb-0"><i class="ti tabler-list-details me-2"></i>All Orders</h5>
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-label-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#filter-collapse">
                        <i class="ti tabler-filter me-1"></i> Filters
                    </button>
                </div>
            </div>

            <!-- Collapsible Filter Row -->
            <div class="collapse mt-3" id="filter-collapse">
                <div class="row g-3 pb-3 border-bottom">
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Order Status</label>
                        <select id="filter-status" class="form-select form-select-sm">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="refunded">Refunded</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Payment Status</label>
                        <select id="filter-payment-status" class="form-select form-select-sm">
                            <option value="">All Payment Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="paid">Paid</option>
                            <option value="failed">Failed</option>
                            <option value="refunded">Refunded</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Payment Method</label>
                        <select id="filter-payment-method" class="form-select form-select-sm">
                            <option value="">All Methods</option>
                            <option value="paypal">PayPal</option>
                            <option value="stripe">Stripe</option>
                            <option value="wallet">Wallet</option>
                            <option value="cod">Cash on Delivery</option>
                        </select>
                    </div>
                    <div class="col-md-3">
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

        <!-- Bulk Actions Bar -->
        <div class="card-body py-0">
            <div class="bulk-bar d-none py-2 px-3 mt-3" id="bulk-bar">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary rounded-pill fs-6" id="bulk-count">0</span>
                        <span class="fw-medium" style="font-size:.85rem">orders selected</span>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-label-primary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="ti tabler-refresh me-1"></i> Change Status
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item bulk-action" href="#" data-action="status" data-status="processing"><i class="ti tabler-loader ti-xs me-2 text-info"></i> Processing</a></li>
                                <li><a class="dropdown-item bulk-action" href="#" data-action="status" data-status="completed"><i class="ti tabler-circle-check ti-xs me-2 text-success"></i> Completed</a></li>
                                <li><a class="dropdown-item bulk-action" href="#" data-action="status" data-status="cancelled"><i class="ti tabler-circle-x ti-xs me-2 text-danger"></i> Cancelled</a></li>
                            </ul>
                        </div>
                        <button type="button" class="btn btn-sm btn-label-danger bulk-action" data-action="delete" data-url="{{ route('orders.bulk-delete') }}">
                            <i class="ti tabler-trash me-1"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="table-responsive">
            <table id="orders-table" class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th width="40"><input type="checkbox" class="form-check-input" id="select-all"></th>
                        <th>Order</th>
                        <th>Customer</th>
                        <th>Payment</th>
                        <th class="text-center">Pay. Status</th>
                        <th class="text-end">Total</th>
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

    const OrdersPage = {
        table: null,

        init() {
            this.cacheDom();
            this.initDataTable();
            this.bindEvents();
        },

        cacheDom() {
            this.$table      = $('#orders-table');
            this.$selectAll  = $('#select-all');
            this.$bulkBar    = $('#bulk-bar');
            this.$bulkCount  = $('#bulk-count');
        },

        initDataTable() {
            this.table = this.$table.DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route("orders.index") }}',
                    data: function (d) {
                        d.status = $('#filter-status').val();
                        d.payment_status = $('#filter-payment-status').val();
                        d.payment_method = $('#filter-payment-method').val();
                    }
                },
                order: [[1, 'desc']],
                lengthMenu: [10, 25, 50, 100],
                pageLength: 25,
                columns: [
                    { data: 'checkbox', orderable: false, searchable: false, className: 'pe-0' },
                    { data: 'order_col', name: 'order_number' },
                    { data: 'buyer', orderable: false, searchable: false },
                    { data: 'payment_method', orderable: false, searchable: false },
                    { data: 'payment_status', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'total_formatted', name: 'total_amount', className: 'text-end' },
                    { data: 'status_badge', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'actions', orderable: false, searchable: false, className: 'text-center' }
                ],
                language: {
                    emptyTable: '<div class="py-4 text-center"><i class="ti tabler-shopping-cart-off ti-xl text-muted mb-2 d-block"></i><span class="text-muted">No orders found</span></div>',
                    zeroRecords: '<div class="py-3 text-center text-muted">No matching orders</div>'
                }
            });
        },

        bindEvents() {
            const self = this;

            // Filter handlers
            $('#apply-filters').on('click', () => self.table.ajax.reload());

            $('#clear-filters').on('click', () => {
                $('#filter-status, #filter-payment-status, #filter-payment-method').val('');
                self.table.ajax.reload();
            });

            // Select all
            this.$selectAll.on('change', function () {
                $('.bulk-checkbox').prop('checked', this.checked);
                self.syncBulkBar();
            });

            // Individual checkbox
            $(document).on('change', '.bulk-checkbox', () => self.syncBulkBar());

            // Bulk actions
            $(document).on('click', '.bulk-action', function (e) {
                e.preventDefault();
                const $btn = $(this);
                self.handleBulkAction($btn.data('action'), $btn.data('status'), $btn.data('url'));
            });

            // Single delete
            $(document).on('click', '.delete-btn', function (e) {
                e.preventDefault();
                const url = $(this).data('url');
                Swal.fire({
                    title: 'Delete this order?',
                    text: 'This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete',
                    cancelButtonText: 'Cancel',
                    customClass: { confirmButton: 'btn btn-danger me-3', cancelButton: 'btn btn-label-secondary' },
                    buttonsStyling: false
                }).then(r => {
                    if (!r.isConfirmed) return;
                    $.ajax({
                        url: url,
                        type: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: () => {
                            self.table.ajax.reload(null, false);
                            Swal.fire({ icon: 'success', title: 'Order deleted', showConfirmButton: false, timer: 1500, timerProgressBar: true });
                        },
                        error: () => Swal.fire({ icon: 'error', title: 'Failed to delete order', showConfirmButton: false, timer: 1500 })
                    });
                });
            });
        },

        syncBulkBar() {
            const count = $('.bulk-checkbox:checked').length;
            this.$bulkCount.text(count);

            if (count > 0) {
                this.$bulkBar.removeClass('d-none');
            } else {
                this.$bulkBar.addClass('d-none');
            }

            const total = $('.bulk-checkbox').length;
            this.$selectAll.prop('checked', count > 0 && count === total);
        },

        handleBulkAction(action, status, url) {
            const ids = this.getSelectedIds();
            if (!ids.length) {
                Swal.fire({ icon: 'info', title: 'No Selection', text: 'Please select at least one order.', timer: 2000, showConfirmButton: false });
                return;
            }
            if (action === 'delete') { this.confirmBulkDelete(ids, url); return; }
            if (action === 'status') { this.confirmBulkStatus(ids, status); }
        },

        getSelectedIds() {
            return $('.bulk-checkbox:checked').map((_, el) => el.value).get();
        },

        confirmBulkStatus(ids, status) {
            Swal.fire({
                title: 'Change Order Status?',
                html: `<span class="text-muted">Selected orders will be marked as <strong>${status}</strong>.</span>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, change',
                cancelButtonText: 'Cancel',
                customClass: { confirmButton: 'btn btn-primary me-3', cancelButton: 'btn btn-label-secondary' },
                buttonsStyling: false
            }).then(r => {
                if (!r.isConfirmed) return;
                $.post('{{ route("orders.bulk-status") }}', { ids, status, _token: '{{ csrf_token() }}' })
                    .done(() => {
                        this.afterBulkAction();
                        Swal.fire({ icon: 'success', title: 'Status updated', showConfirmButton: false, timer: 1500, timerProgressBar: true });
                    })
                    .fail(() => Swal.fire({ icon: 'error', title: 'Failed', text: 'Could not update status.', timer: 2000, showConfirmButton: false }));
            });
        },

        confirmBulkDelete(ids, url) {
            Swal.fire({
                title: 'Delete Orders?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete',
                cancelButtonText: 'Cancel',
                customClass: { confirmButton: 'btn btn-danger me-3', cancelButton: 'btn btn-label-secondary' },
                buttonsStyling: false
            }).then(r => {
                if (!r.isConfirmed) return;
                $.post(url, { ids, _token: '{{ csrf_token() }}' })
                    .done(() => {
                        this.afterBulkAction();
                        Swal.fire({ icon: 'success', title: 'Orders deleted', showConfirmButton: false, timer: 1500, timerProgressBar: true });
                    })
                    .fail(() => Swal.fire({ icon: 'error', title: 'Failed', text: 'Could not delete orders.', timer: 2000, showConfirmButton: false }));
            });
        },

        afterBulkAction() {
            this.table.ajax.reload(null, false);
            this.$selectAll.prop('checked', false);
            this.$bulkBar.addClass('d-none');
        }
    };

    $(document).ready(() => OrdersPage.init());

})(jQuery);
</script>
@endpush
