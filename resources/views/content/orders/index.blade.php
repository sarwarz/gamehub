@extends('layouts.app')

@section('title', 'Orders')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}">
@endpush

@section('content')
<div class="app-ecommerce-orders">

    {{-- Alerts --}}
    @include('partials.alerts')

    <div class="card mb-3 p-4 collapse" id="filter-collapse">
        <div>
            <div class="card-bodys">
                <p>Filters</p>
                <form id="filterForm">
                    <div id="filterRows">
                        <!-- Filter row -->
                        <div class="row g-2 align-items-center filter-row mb-2">
                            <div class="col-md-6">
                                <select name="field[]" class="form-select">
                                    <option value="">Select field</option>

                                    <!-- Order / General -->
                                    <option value="status">Status</option>
                                    <option value="created_at">Created At</option>
                                    <option value="amount">Amount</option>
                                    <!-- Customer -->
                                    <option value="customer_name">Customer Name</option>
                                    <option value="customer_email">Customer Email</option>
                                    <option value="customer_phone">Customer Phone</option>

                                    <!-- Payment / Shipping -->
                                    <option value="payment_method">Payment Method</option>
                                    <option value="payment_status">Payment Status</option>
                                </select>


                            </div>

                            <div class="col-md-6">
                                <select class="form-select" name="operator[]">
                                    <option value="=">Is equal to</option>
                                    <option value="!=">Is not equal to</option>
                                    <option value="like">Contains</option>
                                    <option value=">">Greater than</option>
                                    <option value="<">Less than</option>
                                </select>
                            </div>

                            <div class="col-md-12 value-wrapper">
                                <input type="text" class="form-control" name="value[]" placeholder="Value">
                            </div>

                            <div class="col-md-1 text-start">
                                <button type="button" class="btn btn-outline-danger remove-row d-none">
                                    <i class="menu-icon icon-base ti tabler-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-3">
                        <button type="button" class="btn btn-outline-secondary" id="addFilter">
                            Add additional filter
                        </button>
                        <button type="submit" class="btn btn-primary">
                            Apply
                        </button>
                        <button type="button"
                            class="btn btn-outline-danger d-none"
                            id="clearFilters">
                        Clear Filters
                    </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        {{-- Header --}}


        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
               <div class="btn-group">
                    <button type="button" class="btn btn-outline-secondary dropdown-toggle waves-effect" data-bs-toggle="dropdown" aria-expanded="false">
                    Bulk Actions
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item bulk-action"
                            href="#"
                            data-action="status"
                            data-status="processing">
                            Change status to processing
                            </a>
                        </li>

                        <li>
                            <a class="dropdown-item bulk-action"
                            href="#"
                            data-action="status"
                            data-status="completed">
                            Change status to completed
                            </a>
                        </li>

                        <li>
                            <a class="dropdown-item bulk-action"
                            href="#"
                            data-action="status"
                            data-status="cancelled">
                            Change status to cancelled
                            </a>
                        </li>

                        <li><hr class="dropdown-divider"></li>

                        <li>
                            <a class="dropdown-item bulk-action text-danger"
                            href="#"
                            data-action="delete"
                            data-url="{{ route('orders.bulk-delete') }}">
                            Move to Trash
                            </a>
                        </li>
                    </ul>

                </div>
                <button type="button" class="btn btn-outline-secondary waves-effect" data-bs-toggle="collapse" data-bs-target="#filter-collapse" aria-expanded="true" aria-controls="filter-collapse">Filters</button>
            </div>
            <div>
                <a href="{{ route('orders.create') }}" class="btn btn-primary">
                    <i class="ti tabler-plus me-1"></i>
                    Add Order
                </a>
            </div>
        </div>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="orders-table" style="width:100%">
                <thead>
                    <tr>
                        <th width="30">
                            <input type="checkbox" class="form-check-input" id="select-all">
                        </th>
                        <th>Order</th>
                        <th>Customer</th>
                        <th>Payment Method</th>
                        <th class="text-center">Payment Status</th>
                        <th>Order Date</th>
                        <th class="text-end">Total</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Actions</th>
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

    const OrdersPage = {

        table: null,

        init() {
            this.cacheDom();
            this.initDataTable();
            this.bindEvents();
        },

        cacheDom() {
            this.$table        = $('#orders-table');
            this.$filterForm   = $('#filterForm');
            this.$filterRows   = $('#filterRows');
            this.$clearBtn     = $('#clearFilters');
            this.$addFilterBtn = $('#addFilter');
            this.$selectAll    = $('#select-all');
        },

        /* ===============================
         * DataTable
         =============================== */
        initDataTable() {
            this.table = this.$table.DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('orders.index') }}',
                    data: d => {
                        d.filters = this.$filterForm.serializeArray();
                    }
                },
                order: [[5, 'desc']],
                lengthMenu: [10, 25, 50, 100],
                pageLength: 25,
                columns: this.getColumns()
            });
        },

        getColumns() {
            return [
                { data: 'checkbox', orderable: false, searchable: false },
                {
                    data: 'order_number',
                    render: (data, type, row) => {
                        const orderNo = data ?? row.id;
                        return `<span class="fw-semibold">#${orderNo}</span>`;
                    }
                },
                { data: 'buyer', orderable: false, searchable: false },
                { data: 'payment_method', orderable: false, searchable: false },
                { data: 'payment_status', orderable: false, searchable: false, className: 'text-center' },
                { data: 'order_date', name: 'created_at' },
                { data: 'total_formatted', className: 'text-end fw-semibold' },
                { data: 'status_badge', orderable: false, searchable: false, className: 'text-center' },
                { data: 'actions', orderable: false, searchable: false, className: 'text-center' }
            ];
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
            this.$addFilterBtn.on('click', () => this.addFilterRow());

            // Remove filter row
            $(document).on('click', '.remove-row', e =>
                $(e.currentTarget).closest('.filter-row').remove()
            );

            // Dynamic field input
            $(document).on('change', 'select[name="field[]"]', e =>
                this.handleFieldChange($(e.currentTarget))
            );

            // Select all
            this.$selectAll.on('change', e =>
                $('.bulk-checkbox').prop('checked', e.target.checked)
            );

            // Bulk actions
            $(document).on('click', '.bulk-action', e => {
                e.preventDefault();

                const $btn   = $(e.currentTarget);
                const action = $btn.data('action');
                const status = $btn.data('status');
                const url    = $btn.data('url');

                this.handleBulkAction(action, status, url);
            });
        },

        /* ===============================
         * Filters
         =============================== */
        clearFilters() {
            this.$filterForm[0].reset();
            this.$filterRows.find('.filter-row:not(:first)').remove();
            this.$filterRows.find('.value-wrapper').html(this.defaultInput());
            this.table.ajax.reload();
            this.$clearBtn.addClass('d-none');
        },

        addFilterRow() {
            const $row = this.$filterRows.find('.filter-row:first').clone();
            $row.find('select, input').val('');
            $row.find('.remove-row').removeClass('d-none');
            this.$filterRows.append($row);
        },

        handleFieldChange($select) {
            const field = $select.val();
            const $row  = $select.closest('.filter-row');

            $row.find('.value-wrapper').html(this.getFieldInput(field));

            if (['created_at', 'amount'].includes(field)) {
                $row.find('select[name="operator[]"]').val('=');
            }
        },

        getFieldInput(field) {
            const inputs = {
                status: `
                    <select name="value[]" class="form-select">
                        <option value="">Select Status</option>
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                        <option value="refunded">Refunded</option>
                    </select>`,

                payment_status: `
                    <select name="value[]" class="form-select">
                        <option value="">Select Payment Status</option>
                        <option value="pending">Pending</option>
                        <option value="paid">Paid</option>
                        <option value="failed">Failed</option>
                        <option value="refunded">Refunded</option>
                    </select>`,


                payment_method: `
                    <select name="value[]" class="form-select">
                        <option value="">Select Payment Method</option>
                        <option value="paypal">PayPal</option>
                        <option value="stripe">Stripe</option>
                        <option value="cod">Cash on Delivery</option>
                    </select>`,

                created_at: `<input type="date" class="form-control" name="value[]">`,
                amount: `<input type="number" step="0.01" class="form-control" name="value[]" placeholder="Amount">`
            };

            return inputs[field] || this.defaultInput();
        },

        defaultInput() {
            return `<input type="text" class="form-control" name="value[]" placeholder="Value">`;
        },

        /* ===============================
         * Bulk Actions (SweetAlert)
         =============================== */
        handleBulkAction(action, status = null, url = null) {

            const ids = this.getSelectedIds();

            if (!ids.length) {
                this.toast('info', 'Please select at least one order');
                return;
            }

            if (action === 'delete') {
                this.confirmBulkDelete(ids, url);
                return;
            }

            if (action === 'status') {
                this.confirmBulkStatus(ids, status);
            }
        },

        getSelectedIds() {
            return $('.bulk-checkbox:checked')
                .map((_, el) => el.value)
                .get();
        },

        confirmBulkStatus(ids, status) {
            Swal.fire({
                title: 'Change Order Status?',
                text: `Selected orders will be marked as "${status}".`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, change status',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then(result => {

                if (!result.isConfirmed) return;

                Swal.showLoading();

                $.post('{{ route('orders.bulk-status') }}', {
                    ids,
                    status,
                    _token: '{{ csrf_token() }}'
                })
                .done(() => {
                    Swal.close();
                    this.afterBulkAction();
                    Swal.fire({
                        icon: 'success',
                        title: 'Order status updated',
                        showConfirmButton: false,
                        timer: 1800,
                        timerProgressBar: true
                    });

                })
                .fail(() => {
                    Swal.close();
                    this.toast('error', 'Failed to update order status');
                });
            });
        },

        confirmBulkDelete(ids, url) {
            Swal.fire({
                title: 'Delete Orders?',
                text: 'This action cannot be undone.',
                icon: 'error',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then(result => {

                if (!result.isConfirmed) return;

                Swal.showLoading();

                $.post(url, {
                    ids,
                    _token: '{{ csrf_token() }}'
                })
                .done(() => {
                    Swal.close();
                    this.afterBulkAction();
                    Swal.fire({
                        icon: 'success',
                        title: 'Orders deleted successfully',
                        showConfirmButton: false,
                        timer: 1800,
                        timerProgressBar: true
                    });
                })
                .fail(() => {
                    Swal.close();
                    Swal.fire({
                        icon: 'success',
                        title: 'Failed to delete orders',
                        showConfirmButton: false,
                        timer: 1800,
                        timerProgressBar: true
                    });
                });
            });
        },

        afterBulkAction() {
            this.table.ajax.reload(null, false);
            this.$selectAll.prop('checked', false);
        },



    };

    $(document).ready(() => OrdersPage.init());

})(jQuery);
</script>
@endpush
