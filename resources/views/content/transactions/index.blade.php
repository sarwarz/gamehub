@extends('layouts.app')
@section('title', 'Transactions')

@push('page-css')
<style>
.bulk-bar { background:#f0f2ff; border-radius:8px; animation:bulkSlide .3s ease; }
@keyframes bulkSlide { from { opacity:0; transform:translateY(-8px); } to { opacity:1; transform:translateY(0); } }
.filter-panel { border-left: 3px solid var(--bs-primary); }
</style>
@endpush

@section('content')

    {{-- Page Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1"><i class="ti tabler-arrows-exchange me-2"></i>Transactions</h4>
            <p class="text-muted mb-0">All financial transactions across the platform</p>
        </div>
    </div>

    {{-- Stats --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3 bg-label-primary">
                            <i class="ti tabler-arrows-exchange fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ number_format($stats['total']) }}</h5>
                            <small class="text-muted">Total Transactions</small>
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
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3 bg-label-info">
                            <i class="ti tabler-currency-dollar fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ format_currency($stats['volume']) }}</h5>
                            <small class="text-muted">Total Volume</small>
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
                <h5 class="mb-0"><i class="ti tabler-receipt me-2"></i>All Transactions</h5>
                <div class="d-flex align-items-center gap-2">
                    <div class="btn-group">
                        <button type="button" class="btn btn-label-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="ti tabler-settings-2 ti-xs me-1"></i> Bulk Actions
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><h6 class="dropdown-header">Change Status</h6></li>
                            <li><a class="dropdown-item bulk-action" href="#" data-action="status" data-status="completed"><i class="ti tabler-circle-check ti-xs me-2 text-success"></i> Completed</a></li>
                            <li><a class="dropdown-item bulk-action" href="#" data-action="status" data-status="failed"><i class="ti tabler-circle-x ti-xs me-2 text-danger"></i> Failed</a></li>
                            <li><a class="dropdown-item bulk-action" href="#" data-action="status" data-status="reversed"><i class="ti tabler-arrow-back-up ti-xs me-2 text-warning"></i> Reversed</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item bulk-action text-danger" href="#" data-action="delete" data-url="{{ route('transactions.bulk-delete') }}"><i class="ti tabler-trash ti-xs me-2"></i> Delete Selected</a></li>
                        </ul>
                    </div>
                    <button class="btn btn-label-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#filter-collapse" aria-expanded="false">
                        <i class="ti tabler-filter me-1"></i> Filters
                    </button>
                </div>
            </div>

            {{-- Collapsible Filter Row --}}
            <div class="collapse mt-3" id="filter-collapse">
                <div class="filter-panel p-3 mb-3 rounded">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="mb-0"><i class="ti tabler-filter ti-sm me-1"></i> Advanced Filters</h6>
                        <button type="button" class="btn-close btn-close-sm" data-bs-toggle="collapse" data-bs-target="#filter-collapse"></button>
                    </div>
                    <form id="filterForm">
                        <div id="filterRows">
                            <div class="row g-2 align-items-end filter-row mb-2">
                                <div class="col-md-3">
                                    <label class="form-label small text-muted">Field</label>
                                    <select name="field[]" class="form-select form-select-sm">
                                        <option value="">Select field</option>
                                        <option value="status">Status</option>
                                        <option value="type">Type</option>
                                        <option value="category">Category</option>
                                        <option value="amount">Amount</option>
                                        <option value="created_at">Date</option>
                                        <option value="owner_name">Owner Name</option>
                                        <option value="owner_email">Owner Email</option>
                                        <option value="payment_method">Payment Method</option>
                                        <option value="currency">Currency</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small text-muted">Operator</label>
                                    <select name="operator[]" class="form-select form-select-sm">
                                        <option value="=">Is equal to</option>
                                        <option value="!=">Is not equal to</option>
                                        <option value="like">Contains</option>
                                        <option value=">">Greater than</option>
                                        <option value="<">Less than</option>
                                    </select>
                                </div>
                                <div class="col-md-4 value-wrapper">
                                    <label class="form-label small text-muted">Value</label>
                                    <input type="text" class="form-control form-control-sm" name="value[]" placeholder="Enter value...">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-sm btn-icon btn-outline-danger remove-row d-none"><i class="ti tabler-x ti-xs"></i></button>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex gap-2 mt-3">
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="addFilter"><i class="ti tabler-plus ti-xs me-1"></i> Add Filter</button>
                            <button type="submit" class="btn btn-sm btn-primary"><i class="ti tabler-check ti-xs me-1"></i> Apply</button>
                            <button type="button" class="btn btn-sm btn-outline-danger d-none" id="clearFilters"><i class="ti tabler-x ti-xs me-1"></i> Clear</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Bulk Actions Bar --}}
        <div class="card-body py-0">
            <div class="bulk-bar d-none py-2 px-3 mb-2" id="bulk-bar">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary rounded-pill fs-6" id="bulk-count">0</span>
                        <span class="fw-medium" style="font-size:.85rem">transactions selected</span>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-label-primary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="ti tabler-switch-horizontal me-1"></i> Change Status
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item bulk-status-btn" href="#" data-status="completed"><i class="ti tabler-point-filled me-1" style="font-size:.5rem"></i> Completed</a></li>
                                <li><a class="dropdown-item bulk-status-btn" href="#" data-status="failed"><i class="ti tabler-point-filled me-1" style="font-size:.5rem"></i> Failed</a></li>
                                <li><a class="dropdown-item bulk-status-btn" href="#" data-status="reversed"><i class="ti tabler-point-filled me-1" style="font-size:.5rem"></i> Reversed</a></li>
                            </ul>
                        </div>
                        <button class="btn btn-sm btn-label-danger bulk-delete-btn" type="button">
                            <i class="ti tabler-trash me-1"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="transactions-table" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <th width="30"><input type="checkbox" id="select-all" class="form-check-input"></th>
                        <th>TRX</th>
                        <th>Owner</th>
                        <th class="text-center">Type</th>
                        <th class="text-end">Amount</th>
                        <th class="text-end">Fee</th>
                        <th class="text-end">Net</th>
                        <th class="text-center">Category</th>
                        <th>Payment</th>
                        <th class="text-center">Status</th>
                        <th>Date</th>
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
            this.$bulkBar    = $('#bulk-bar');
            this.$bulkCount  = $('#bulk-count');
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
                        d.filters = fields.map((field, i) => ({ field, operator: operators[i], value: values[i] }));
                    }
                },
                order: [[10, 'desc']],
                pageLength: 25,
                columns: [
                    { data: 'checkbox', orderable: false, searchable: false, className: 'pe-0' },
                    { data: 'trx', name: 'trx' },
                    { data: 'owner', orderable: false, searchable: false },
                    { data: 'type', orderable: false, className: 'text-center' },
                    { data: 'amount', orderable: false, className: 'text-end' },
                    { data: 'fee', orderable: false, className: 'text-end' },
                    { data: 'net_amount', orderable: false, className: 'text-end' },
                    { data: 'category', name: 'category', className: 'text-center' },
                    { data: 'payment', name: 'payment_method' },
                    { data: 'status', orderable: false, className: 'text-center' },
                    { data: 'date', name: 'created_at' }
                ],
                language: {
                    emptyTable: '<div class="py-4 text-center"><i class="ti tabler-receipt-off ti-xl text-muted mb-2 d-block"></i><span class="text-muted">No transactions found</span></div>'
                }
            });
        },

        bindEvents() {
            const self = this;

            this.$filterForm.on('submit', e => { e.preventDefault(); self.table.ajax.reload(); self.$clearBtn.removeClass('d-none'); });
            this.$clearBtn.on('click', () => self.clearFilters());
            this.$addBtn.on('click', () => self.addFilterRow());
            $(document).on('click', '.remove-row', e => $(e.currentTarget).closest('.filter-row').remove());
            $(document).on('change', 'select[name="field[]"]', e => self.handleFieldChange($(e.currentTarget)));

            this.$selectAll.on('change', e => {
                $('.bulk-checkbox').prop('checked', e.target.checked);
                self.updateBulkBar();
            });
            $(document).on('change', '.bulk-checkbox', () => self.updateBulkBar());

            $(document).on('click', '.bulk-action', e => { e.preventDefault(); self.handleBulkAction($(e.currentTarget)); });
            $(document).on('click', '.bulk-status-btn', function (e) {
                e.preventDefault();
                self.confirmStatus(self.getSelectedIds(), $(this).data('status'));
            });
            $(document).on('click', '.bulk-delete-btn', function (e) {
                e.preventDefault();
                self.confirmDelete(self.getSelectedIds(), '{{ route('transactions.bulk-delete') }}');
            });
        },

        updateBulkBar() {
            const count = this.getSelectedIds().length;
            this.$bulkCount.text(count);
            count > 0 ? this.$bulkBar.removeClass('d-none') : this.$bulkBar.addClass('d-none');
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
            $select.closest('.filter-row').find('.value-wrapper').html(this.getFieldInput($select.val()));
        },

        getFieldInput(field) {
            const l = '<label class="form-label small text-muted">Value</label>';
            const map = {
                status: l+`<select name="value[]" class="form-select form-select-sm"><option value="">Select Status</option><option value="pending">Pending</option><option value="completed">Completed</option><option value="failed">Failed</option><option value="reversed">Reversed</option></select>`,
                type: l+`<select name="value[]" class="form-select form-select-sm"><option value="">Select Type</option><option value="credit">Credit</option><option value="debit">Debit</option></select>`,
                category: l+`<select name="value[]" class="form-select form-select-sm"><option value="">Select Category</option><option value="order">Order</option><option value="withdraw">Withdraw</option><option value="commission">Commission</option><option value="refund">Refund</option><option value="bonus">Bonus</option><option value="adjustment">Adjustment</option><option value="payout">Payout</option></select>`,
                created_at: l+`<input type="date" class="form-control form-control-sm" name="value[]">`,
                amount: l+`<input type="number" step="0.01" class="form-control form-control-sm" name="value[]" placeholder="Amount">`
            };
            return map[field] || this.defaultInput();
        },

        defaultInput() {
            return `<label class="form-label small text-muted">Value</label><input type="text" class="form-control form-control-sm" name="value[]" placeholder="Enter value...">`;
        },

        handleBulkAction($btn) {
            const ids = this.getSelectedIds();
            if (!ids.length) { Swal.fire({ icon: 'info', title: 'No Selection', text: 'Select at least one transaction.', timer: 2000, showConfirmButton: false }); return; }
            const action = $btn.data('action');
            if (action === 'delete') return this.confirmDelete(ids, $btn.data('url'));
            if (action === 'status') return this.confirmStatus(ids, $btn.data('status'));
        },

        getSelectedIds() { return $('.bulk-checkbox:checked').map((_, el) => el.value).get(); },

        confirmStatus(ids, status) {
            if (!ids.length) { Swal.fire({ icon: 'info', title: 'No Selection', text: 'Select at least one transaction.', timer: 2000, showConfirmButton: false }); return; }
            const self = this;
            Swal.fire({
                title: 'Change Status?',
                html: `<span class="text-muted">Selected transactions will be marked as <strong>${status}</strong>.</span>`,
                icon: 'question', showCancelButton: true, confirmButtonText: 'Yes, change',
                customClass: { confirmButton: 'btn btn-primary me-3', cancelButton: 'btn btn-label-secondary' }, buttonsStyling: false
            }).then(r => {
                if (!r.isConfirmed) return;
                $.post('{{ route('transactions.bulk-status') }}', { ids, status, _token: '{{ csrf_token() }}' })
                    .done(() => { self.afterBulk(); Swal.fire({ icon: 'success', title: 'Status updated', showConfirmButton: false, timer: 1500, timerProgressBar: true }); })
                    .fail(() => Swal.fire({ icon: 'error', title: 'Failed', timer: 2000, showConfirmButton: false }));
            });
        },

        confirmDelete(ids, url) {
            if (!ids.length) { Swal.fire({ icon: 'info', title: 'No Selection', text: 'Select at least one transaction.', timer: 2000, showConfirmButton: false }); return; }
            const self = this;
            Swal.fire({
                title: 'Delete Transactions?', text: 'This action cannot be undone.', icon: 'warning',
                showCancelButton: true, confirmButtonText: 'Yes, delete',
                customClass: { confirmButton: 'btn btn-danger me-3', cancelButton: 'btn btn-label-secondary' }, buttonsStyling: false
            }).then(r => {
                if (!r.isConfirmed) return;
                $.post(url, { ids, _token: '{{ csrf_token() }}' })
                    .done(() => { self.afterBulk(); Swal.fire({ icon: 'success', title: 'Deleted', showConfirmButton: false, timer: 1500, timerProgressBar: true }); })
                    .fail(() => Swal.fire({ icon: 'error', title: 'Failed', timer: 2000, showConfirmButton: false }));
            });
        },

        afterBulk() {
            this.table.ajax.reload(null, false);
            this.$selectAll.prop('checked', false);
            this.$bulkBar.addClass('d-none');
        }
    };

    $(document).ready(() => TransactionsPage.init());
})(jQuery);
</script>
@endpush
