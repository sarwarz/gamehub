@extends('layouts.app')

@section('title', 'Transactions')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}">
@endpush

@section('content')
<div class="app-ecommerce-transactions">

    {{-- Alerts --}}
    @include('partials.alerts')

    {{-- Filters --}}
    <div class="card mb-3 p-4 collapse" id="filter-collapse">
        <p class="fw-semibold mb-2">Filters</p>

        <form id="filterForm">
            <div id="filterRows">

                {{-- Filter row --}}
                <div class="row g-2 align-items-center filter-row mb-2">
                    <div class="col-md-4">
                        <select name="field[]" class="form-select">
                            <option value="">Select field</option>

                            {{-- Transaction --}}
                            <option value="status">Status</option>
                            <option value="type">Type</option>
                            <option value="category">Category</option>
                            <option value="amount">Amount</option>
                            <option value="created_at">Date</option>

                            {{-- Owner --}}
                            <option value="owner_name">Owner Name</option>
                            <option value="owner_email">Owner Email</option>

                            {{-- Payment --}}
                            <option value="payment_method">Payment Method</option>
                            <option value="currency">Currency</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <select name="operator[]" class="form-select">
                            <option value="=">Is equal to</option>
                            <option value="!=">Is not equal to</option>
                            <option value="like">Contains</option>
                            <option value=">">Greater than</option>
                            <option value="<">Less than</option>
                        </select>
                    </div>

                    <div class="col-md-4 value-wrapper">
                        <input type="text" class="form-control" name="value[]" placeholder="Value">
                    </div>

                    <div class="col-md-1">
                        <button type="button" class="btn btn-outline-danger remove-row d-none">
                            <i class="ti tabler-trash"></i>
                        </button>
                    </div>
                </div>

            </div>

            <div class="d-flex gap-2 mt-3">
                <button type="button" class="btn btn-outline-secondary" id="addFilter">
                    Add filter
                </button>

                <button type="submit" class="btn btn-primary">
                    Apply
                </button>

                <button type="button" class="btn btn-outline-danger d-none" id="clearFilters">
                    Clear
                </button>
            </div>
        </form>
    </div>

    {{-- Main Card --}}
    <div class="card shadow-sm">

        {{-- Header --}}
        <div class="card-header d-flex  align-items-center">
            <div class="btn-group">
                <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                    Bulk Actions
                </button>
                <ul class="dropdown-menu">

                    <li>
                        <a class="dropdown-item bulk-action"
                           href="#"
                           data-action="status"
                           data-status="completed">
                            Mark as Completed
                        </a>
                    </li>

                    <li>
                        <a class="dropdown-item bulk-action"
                           href="#"
                           data-action="status"
                           data-status="failed">
                            Mark as Failed
                        </a>
                    </li>

                    <li>
                        <a class="dropdown-item bulk-action"
                           href="#"
                           data-action="status"
                           data-status="reversed">
                            Mark as Reversed
                        </a>
                    </li>

                    <li><hr class="dropdown-divider"></li>

                    <li>
                        <a class="dropdown-item text-danger bulk-action"
                           href="#"
                           data-action="delete"
                           data-url="{{ route('transactions.bulk-delete') }}">
                            Delete Transactions
                        </a>
                    </li>
                </ul>
            </div>

            <button class="btn btn-outline-secondary mx-2"
                    data-bs-toggle="collapse"
                    data-bs-target="#filter-collapse">
                Filters
            </button>
        </div>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="transactions-table" style="width:100%">
                <thead>
                    <tr>
                        <th width="30">
                            <input type="checkbox" id="select-all" class="form-check-input">
                        </th>
                        <th>TRX</th>
                        <th>Owner</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Fee</th>
                        <th>Net</th>
                        <th>Category</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Date</th>
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

    const TransactionsPage = {

        table: null,

        init() {
            this.cacheDom();
            this.initDataTable();
            this.bindEvents();
        },

        cacheDom() {
            this.$table      = $('#transactions-table');
            this.$filterForm = $('#filterForm');
            this.$filterRows = $('#filterRows');
            this.$clearBtn   = $('#clearFilters');
            this.$addBtn     = $('#addFilter');
            this.$selectAll  = $('#select-all');
        },

        initDataTable() {
            this.table = this.$table.DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('transactions.index') }}',
                    data: d => {
                        const fields    = $('select[name="field[]"]').map((_, el) => el.value).get();
                        const operators = $('select[name="operator[]"]').map((_, el) => el.value).get();
                        const values    = $('*[name="value[]"]').map((_, el) => el.value).get();

                        d.filters = fields.map((field, i) => ({
                            field: field,
                            operator: operators[i],
                            value: values[i]
                        }));
                    }

                },
                order: [[10, 'desc']],
                pageLength: 25,
                columns: [
                    { data: 'checkbox', orderable: false, searchable: false },
                    { data: 'trx', name: 'trx' },
                    { data: 'owner', orderable: false, searchable: false },
                    { data: 'type', orderable: false },
                    { data: 'amount', orderable: false },
                    { data: 'fee', orderable: false },
                    { data: 'net_amount', orderable: false },
                    { data: 'category', name: 'category' },
                    { data: 'payment', name: 'payment_method' },
                    { data: 'status', orderable: false },
                    { data: 'date', name: 'created_at' }
                ]
            });
        },

        bindEvents() {

            this.$filterForm.on('submit', e => {
                e.preventDefault();
                this.table.ajax.reload();
                this.$clearBtn.removeClass('d-none');
            });

            this.$clearBtn.on('click', () => this.clearFilters());

            this.$addBtn.on('click', () => this.addFilterRow());

            $(document).on('click', '.remove-row', e =>
                $(e.currentTarget).closest('.filter-row').remove()
            );

            $(document).on('change', 'select[name="field[]"]', e =>
                this.handleFieldChange($(e.currentTarget))
            );

            this.$selectAll.on('change', e =>
                $('.bulk-checkbox').prop('checked', e.target.checked)
            );

            $(document).on('click', '.bulk-action', e => {
                e.preventDefault();
                this.handleBulkAction($(e.currentTarget));
            });
        },

        addFilterRow() {
            const $row = this.$filterRows.find('.filter-row:first').clone();
            $row.find('select, input').val('');
            $row.find('.remove-row').removeClass('d-none');
            this.$filterRows.append($row);
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
        },

        getFieldInput(field) {
            const map = {
                status: `
                    <select name="value[]" class="form-select">
                        <option value="">Select Status</option>
                        <option value="pending">Pending</option>
                        <option value="completed">Completed</option>
                        <option value="failed">Failed</option>
                        <option value="reversed">Reversed</option>
                    </select>`,

                type: `
                    <select name="value[]" class="form-select">
                        <option value="">Select Type</option>
                        <option value="credit">Credit</option>
                        <option value="debit">Debit</option>
                    </select>`,

                category: `
                    <select name="value[]" class="form-select">
                        <option value="">Select Category</option>
                        <option value="order">Order</option>
                        <option value="withdraw">Withdraw</option>
                        <option value="commission">Commission</option>
                        <option value="refund">Refund</option>
                        <option value="bonus">Bonus</option>
                        <option value="adjustment">Adjustment</option>
                        <option value="payout">Payout</option>
                    </select>`,

                created_at: `<input type="date" class="form-control" name="value[]">`,
                amount: `<input type="number" step="0.01" class="form-control" name="value[]">`
            };

            return map[field] || this.defaultInput();
        },

        defaultInput() {
            return `<input type="text" class="form-control" name="value[]" placeholder="Value">`;
        },

        handleBulkAction($btn) {
            const action = $btn.data('action');
            const status = $btn.data('status');
            const url    = $btn.data('url');
            const ids    = this.getSelectedIds();

            if (!ids.length) {
                Swal.fire('No selection', 'Select at least one transaction', 'info');
                return;
            }

            if (action === 'delete') return this.confirmDelete(ids, url);
            if (action === 'status') return this.confirmStatus(ids, status);
        },

        getSelectedIds() {
            return $('.bulk-checkbox:checked').map((_, el) => el.value).get();
        },

        confirmStatus(ids, status) {
            Swal.fire({
                title: 'Confirm status change?',
                text: `Selected transactions will be marked as "${status}"`,
                icon: 'warning',
                showCancelButton: true
            }).then(r => {
                if (!r.isConfirmed) return;

                $.post('{{ route('transactions.bulk-status') }}', {
                    ids, status, _token: '{{ csrf_token() }}'
                }).done(() => {
                    this.afterBulk();
                    Swal.fire('Updated', 'Status updated successfully', 'success');
                });
            });
        },

        confirmDelete(ids, url) {
            Swal.fire({
                title: 'Delete transactions?',
                icon: 'error',
                showCancelButton: true
            }).then(r => {
                if (!r.isConfirmed) return;

                $.post(url, { ids, _token: '{{ csrf_token() }}' })
                .done(() => {
                    this.afterBulk();
                    Swal.fire('Deleted', 'Transactions removed', 'success');
                });
            });
        },

        afterBulk() {
            this.table.ajax.reload(null, false);
            this.$selectAll.prop('checked', false);
        }
    };

    $(document).ready(() => TransactionsPage.init());

})(jQuery);
</script>
@endpush
