@extends('layouts.app')
@section('title', 'Affiliate Withdrawals')

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
            <h4 class="mb-1"><i class="ti tabler-cash me-2"></i>Affiliate Withdrawals</h4>
            <p class="text-muted mb-0">Review and process affiliate withdrawal requests</p>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row mb-4">
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
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3 bg-label-info">
                            <i class="ti tabler-circle-check fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ $stats['approved'] }}</h5>
                            <small class="text-muted">Approved</small>
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
                            <i class="ti tabler-checks fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ $stats['completed'] }}</h5>
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
                        <div class="avatar avatar-md me-3 bg-label-danger">
                            <i class="ti tabler-circle-x fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ $stats['rejected'] }}</h5>
                            <small class="text-muted">Rejected</small>
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
                <h5 class="mb-0"><i class="ti tabler-list-details me-2"></i>All Withdrawals</h5>
                <div class="d-flex align-items-center gap-2">
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
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="completed">Completed</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small text-muted">Method</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="ti tabler-credit-card ti-xs"></i></span>
                            <select class="form-select form-select-sm" id="filter-method">
                                <option value="">All Methods</option>
                                <option value="paypal">PayPal</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="wallet">Wallet</option>
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

        {{-- Table --}}
        <div class="table-responsive">
            <table id="withdrawals-table" class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Affiliate</th>
                        <th>Transaction</th>
                        <th class="text-end">Amount</th>
                        <th class="text-end">Net</th>
                        <th>Method</th>
                        <th class="text-center">Status</th>
                        <th>Date</th>
                        <th class="text-center" width="100">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    {{-- Reject Reason Modal --}}
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ti tabler-circle-x me-2 text-danger"></i>Reject Withdrawal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="reject-withdrawal-id">
                    <div class="mb-3">
                        <label class="form-label">Reason for Rejection <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="reject-reason" rows="3" placeholder="Provide a reason for rejecting this withdrawal..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirm-reject">
                        <i class="ti tabler-circle-x me-1"></i> Reject Withdrawal
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('page-js')
<script>
(function($) {
'use strict';

$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

const WithdrawalsPage = {
    table: null,
    rejectModal: null,

    init() {
        this.rejectModal = new bootstrap.Modal('#rejectModal');
        this.initDataTable();
        this.bindEvents();
    },

    initDataTable() {
        this.table = $('#withdrawals-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("affiliate-withdrawals.index") }}',
                data: d => {
                    d.status = $('#filter-status').val();
                    d.method = $('#filter-method').val();
                }
            },
            order: [[6, 'desc']],
            lengthMenu: [10, 25, 50, 100],
            pageLength: 25,
            columns: [
                { data: 'affiliate_col', orderable: false, searchable: true },
                { data: 'trx_col', name: 'transaction_id' },
                { data: 'amount_col', name: 'amount', className: 'text-end' },
                { data: 'net_col', orderable: false, searchable: false, className: 'text-end' },
                { data: 'method_col', orderable: false, searchable: false },
                { data: 'status_badge', orderable: false, searchable: false, className: 'text-center' },
                { data: 'date_col', name: 'created_at' },
                { data: 'actions', orderable: false, searchable: false, className: 'text-center' }
            ],
            language: {
                emptyTable: '<div class="py-4 text-center"><i class="ti tabler-cash-off ti-xl text-muted mb-2 d-block"></i><span class="text-muted">No withdrawals found</span></div>',
                zeroRecords: '<div class="py-3 text-center text-muted">No matching withdrawals</div>'
            }
        });
    },

    bindEvents() {
        const self = this;

        $('#apply-filters').on('click', () => self.table.ajax.reload());

        $('#clear-filters').on('click', () => {
            $('#filter-status, #filter-method').val('');
            self.table.ajax.reload();
        });

        $(document).on('click', '.btn-approve', function() {
            const id = $(this).data('id');
            Swal.fire({
                title: 'Approve this withdrawal?',
                text: 'The withdrawal will be marked as approved for processing.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, approve',
                cancelButtonText: 'Cancel',
                customClass: { confirmButton: 'btn btn-success me-3', cancelButton: 'btn btn-label-secondary' },
                buttonsStyling: false
            }).then(res => {
                if (!res.isConfirmed) return;
                $.post(self.actionUrl(id, 'approve'))
                    .done(() => {
                        self.table.ajax.reload(null, false);
                        Swal.fire({ icon: 'success', title: 'Withdrawal approved', showConfirmButton: false, timer: 1500, timerProgressBar: true });
                    })
                    .fail(xhr => Swal.fire({ icon: 'error', title: 'Failed', text: xhr.responseJSON?.message || 'Could not approve withdrawal.', timer: 2000, showConfirmButton: false }));
            });
        });

        $(document).on('click', '.btn-reject', function() {
            $('#reject-withdrawal-id').val($(this).data('id'));
            $('#reject-reason').val('');
            self.rejectModal.show();
        });

        $('#confirm-reject').on('click', function() {
            const id = $('#reject-withdrawal-id').val();
            const reason = $('#reject-reason').val().trim();
            if (!reason) {
                Swal.fire({ icon: 'warning', title: 'Reason required', text: 'Please provide a reason for rejection.', timer: 2000, showConfirmButton: false });
                return;
            }
            $.post(self.actionUrl(id, 'reject'), { reason })
                .done(() => {
                    self.rejectModal.hide();
                    self.table.ajax.reload(null, false);
                    Swal.fire({ icon: 'success', title: 'Withdrawal rejected', showConfirmButton: false, timer: 1500, timerProgressBar: true });
                })
                .fail(xhr => Swal.fire({ icon: 'error', title: 'Failed', text: xhr.responseJSON?.message || 'Could not reject withdrawal.', timer: 2000, showConfirmButton: false }));
        });
    },

    actionUrl(id, action) {
        return '{{ url("dashboard/affiliate-withdrawals") }}/' + id + '/' + action;
    }
};

$(document).ready(() => WithdrawalsPage.init());

})(jQuery);
</script>
@endpush
