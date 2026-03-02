@extends('layouts.app')
@section('title', 'Failed Transactions')

@push('page-css')
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
            <h4 class="mb-1"><i class="ti tabler-alert-circle me-2 text-danger"></i>Failed Transactions</h4>
            <p class="text-muted mb-0">Transactions that encountered errors during processing</p>
        </div>
        <a href="{{ route('transactions.index') }}" class="btn btn-label-secondary">
            <i class="ti tabler-arrow-left me-1"></i> All Transactions
        </a>
    </div>

    {{-- Stats Cards --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3 bg-label-danger">
                            <i class="ti tabler-alert-circle fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ number_format($stats['failed_count']) }}</h5>
                            <small class="text-muted">Failed Transactions</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3 bg-label-info">
                            <i class="ti tabler-currency-dollar fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ format_currency($stats['failed_amount']) }}</h5>
                            <small class="text-muted">Total Failed Amount</small>
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
                <h5 class="mb-0"><i class="ti tabler-receipt me-2"></i>Failed Transactions</h5>
                <div class="d-flex align-items-center gap-2">
                    <div class="btn-group">
                        <button type="button" class="btn btn-label-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="ti tabler-settings-2 ti-xs me-1"></i> Bulk Actions
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><h6 class="dropdown-header">Change Status</h6></li>
                            <li><a class="dropdown-item bulk-status-btn" href="#" data-status="completed"><i class="ti tabler-circle-check ti-xs me-2 text-success"></i> Completed</a></li>
                            <li><a class="dropdown-item bulk-status-btn" href="#" data-status="reversed"><i class="ti tabler-arrow-back-up ti-xs me-2 text-warning"></i> Reversed</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger bulk-delete-btn" href="#"><i class="ti tabler-trash ti-xs me-2"></i> Delete Selected</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bulk Actions Bar --}}
        <div class="card-body py-0">
            <div class="bulk-bar d-none py-2 px-3 my-2" id="bulk-bar">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary rounded-pill fs-6" id="bulk-count">0</span>
                        <span class="fw-medium" style="font-size:.85rem">transaction(s) selected</span>
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
                        <th class="text-center">Category</th>
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
(function($) {
'use strict';

const FailedTxPage = {
    table: null,

    init() {
        this.initDataTable();
        this.bindEvents();
    },

    initDataTable() {
        this.table = $('#transactions-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('transactions.failed') }}',
            order: [[7, 'desc']],
            pageLength: 25,
            columns: [
                { data: 'checkbox', orderable: false, searchable: false, className: 'pe-0' },
                { data: 'trx', name: 'trx' },
                { data: 'owner', orderable: false },
                { data: 'type', orderable: false, className: 'text-center' },
                { data: 'amount', orderable: false, className: 'text-end' },
                { data: 'category', name: 'category', className: 'text-center' },
                { data: 'status', orderable: false, className: 'text-center' },
                { data: 'date', name: 'created_at' }
            ],
            drawCallback: () => this.syncBulkBar(),
            language: {
                emptyTable: '<div class="py-4 text-center"><i class="ti tabler-receipt-off ti-xl text-muted mb-2 d-block"></i><span class="text-muted">No failed transactions</span></div>'
            }
        });
    },

    bindEvents() {
        const self = this;

        $('#select-all').on('change', function() {
            $('.bulk-checkbox').prop('checked', this.checked);
            self.syncBulkBar();
        });

        $(document).on('change', '.bulk-checkbox', () => this.syncBulkBar());

        $(document).on('click', '.bulk-status-btn', function(e) {
            e.preventDefault();
            self.confirmStatus(self.getSelectedIds(), $(this).data('status'));
        });

        $(document).on('click', '.bulk-delete-btn', function(e) {
            e.preventDefault();
            self.confirmDelete(self.getSelectedIds());
        });
    },

    syncBulkBar() {
        const count = this.getSelectedIds().length;
        $('#bulk-count').text(count);
        count > 0 ? $('#bulk-bar').removeClass('d-none') : $('#bulk-bar').addClass('d-none');

        const total = $('.bulk-checkbox').length;
        $('#select-all').prop('checked', count > 0 && count === total);
    },

    getSelectedIds() {
        return $('.bulk-checkbox:checked').map((_, el) => el.value).get();
    },

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

    confirmDelete(ids) {
        if (!ids.length) { Swal.fire({ icon: 'info', title: 'No Selection', text: 'Select at least one transaction.', timer: 2000, showConfirmButton: false }); return; }
        const self = this;
        Swal.fire({
            title: 'Delete Transactions?', text: 'This action cannot be undone.', icon: 'warning',
            showCancelButton: true, confirmButtonText: 'Yes, delete',
            customClass: { confirmButton: 'btn btn-danger me-3', cancelButton: 'btn btn-label-secondary' }, buttonsStyling: false
        }).then(r => {
            if (!r.isConfirmed) return;
            $.post('{{ route('transactions.bulk-delete') }}', { ids, _token: '{{ csrf_token() }}' })
                .done(() => { self.afterBulk(); Swal.fire({ icon: 'success', title: 'Deleted', showConfirmButton: false, timer: 1500, timerProgressBar: true }); })
                .fail(() => Swal.fire({ icon: 'error', title: 'Failed', timer: 2000, showConfirmButton: false }));
        });
    },

    afterBulk() {
        this.table.ajax.reload(null, false);
        $('#select-all').prop('checked', false);
        $('#bulk-bar').addClass('d-none');
    }
};

$(document).ready(() => FailedTxPage.init());

})(jQuery);
</script>
@endpush
