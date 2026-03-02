@extends('layouts.app')
@section('title', 'All Wallet Transactions')

@push('page-css')
<style>
    .stat-icon { width: 46px; height: 46px; border-radius: 12px; display: flex; align-items: center; justify-content: center; }
    .filter-btn-group .btn.active { box-shadow: 0 2px 8px rgba(105,108,255,.3); }
    .filter-btn-group .btn-label-success.active { box-shadow: 0 2px 8px rgba(40,199,111,.3); }
    .filter-btn-group .btn-label-danger.active { box-shadow: 0 2px 8px rgba(234,84,85,.3); }
    .source-filter .btn.active { background: #696cff; color: #fff; border-color: #696cff; }
    #transactions-table td { vertical-align: middle; }
</style>
@endpush

@section('content')

{{-- Page Header --}}
<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1">
            <span class="text-muted fw-light">Wallets /</span> All Transactions
        </h4>
        <p class="text-muted mb-0">Credits & debits across all user wallets</p>
    </div>
    <a href="{{ route('wallets.index') }}" class="btn btn-label-secondary">
        <i class="ti tabler-arrow-left ti-xs me-1"></i> Back to Wallets
    </a>
</div>

{{-- Stats Row --}}
<div class="row g-4 mb-4">
    <div class="col-xl-2 col-sm-4 col-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                    <div class="stat-icon me-3" style="background: rgba(105,108,255,.12);">
                        <i class="ti tabler-receipt-2 fs-4" style="color: #696cff;"></i>
                    </div>
                    <div>
                        <h4 class="mb-0">{{ number_format($stats['total']) }}</h4>
                        <small class="text-muted">Total</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-sm-4 col-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                    <div class="stat-icon me-3" style="background: rgba(40,199,111,.12);">
                        <i class="ti tabler-arrow-down-left fs-4" style="color: #28c76f;"></i>
                    </div>
                    <div>
                        <h4 class="mb-0 text-success">+${{ number_format($stats['total_credit'], 2) }}</h4>
                        <small class="text-muted">Credits</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-sm-4 col-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                    <div class="stat-icon me-3" style="background: rgba(234,84,85,.12);">
                        <i class="ti tabler-arrow-up-right fs-4" style="color: #ea5455;"></i>
                    </div>
                    <div>
                        <h4 class="mb-0 text-danger">-${{ number_format($stats['total_debit'], 2) }}</h4>
                        <small class="text-muted">Debits</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-sm-4 col-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                    <div class="stat-icon me-3" style="background: rgba(40,199,111,.12);">
                        <i class="ti tabler-circle-check fs-4" style="color: #28c76f;"></i>
                    </div>
                    <div>
                        <h4 class="mb-0">{{ number_format($stats['completed']) }}</h4>
                        <small class="text-muted">Completed</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-sm-4 col-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                    <div class="stat-icon me-3" style="background: rgba(255,159,67,.12);">
                        <i class="ti tabler-clock fs-4" style="color: #ff9f43;"></i>
                    </div>
                    <div>
                        <h4 class="mb-0">{{ number_format($stats['pending']) }}</h4>
                        <small class="text-muted">Pending</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-sm-4 col-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                    <div class="stat-icon me-3" style="background: rgba(234,84,85,.12);">
                        <i class="ti tabler-circle-x fs-4" style="color: #ea5455;"></i>
                    </div>
                    <div>
                        <h4 class="mb-0">{{ number_format($stats['failed']) }}</h4>
                        <small class="text-muted">Failed</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- DataTable Card --}}
<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3 py-3">
        <div class="d-flex align-items-center gap-2">
            <div class="avatar avatar-sm" style="background: rgba(105,108,255,.12); border-radius: 8px;">
                <span class="avatar-initial rounded" style="background: transparent; color: #696cff;">
                    <i class="ti tabler-list-details"></i>
                </span>
            </div>
            <h5 class="mb-0">Transaction History</h5>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-3">
            {{-- Type Filter --}}
            <div class="btn-group filter-btn-group" role="group" id="type-filter">
                <button type="button" class="btn btn-label-secondary btn-sm active" data-filter="">All</button>
                <button type="button" class="btn btn-label-success btn-sm" data-filter="credit">Credit</button>
                <button type="button" class="btn btn-label-danger btn-sm" data-filter="debit">Debit</button>
            </div>

            {{-- Status Filter --}}
            <select class="form-select form-select-sm" id="status-filter" style="width: auto;">
                <option value="">All Status</option>
                <option value="completed">Completed</option>
                <option value="pending">Pending</option>
                <option value="failed">Failed</option>
            </select>

            {{-- Source Filter --}}
            <select class="form-select form-select-sm" id="source-filter" style="width: auto;">
                <option value="">All Sources</option>
                <option value="deposit">Deposit</option>
                <option value="order">Order</option>
                <option value="refund">Refund</option>
                <option value="transfer">Transfer</option>
                <option value="seller_transfer">Seller Transfer</option>
                <option value="withdraw">Withdraw</option>
                <option value="admin">Admin</option>
            </select>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="transactions-table" style="width:100%">
            <thead class="table-light">
                <tr>
                    <th>User</th>
                    <th class="text-center">Type</th>
                    <th class="text-end">Amount</th>
                    <th>Source</th>
                    <th>Description</th>
                    <th class="text-center">Status</th>
                    <th class="text-end">Balance After</th>
                    <th>Date</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

@endsection

@push('page-js')
<script>
$(function () {
    var typeFilter   = '';
    var statusFilter = '';
    var sourceFilter = '';

    var dt = $('#transactions-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("wallets.all.transactions") }}',
            data: function (d) {
                d.type_filter   = typeFilter;
                d.status_filter = statusFilter;
                d.source_filter = sourceFilter;
            }
        },
        order: [[7, 'desc']],
        pageLength: 25,
        columns: [
            { data: 'user',          orderable: false, searchable: true },
            { data: 'type',          orderable: false, searchable: false, className: 'text-center' },
            { data: 'amount',        name: 'amount', className: 'text-end' },
            { data: 'source',        name: 'source' },
            { data: 'description',   name: 'description' },
            { data: 'status',        orderable: false, searchable: false, className: 'text-center' },
            { data: 'balance_after', name: 'balance_after', className: 'text-end' },
            { data: 'created_at',    name: 'created_at' }
        ],
        language: {
            emptyTable: '<div class="text-center py-4"><i class="ti tabler-receipt-off" style="font-size:2.5rem;color:#ccc;"></i><p class="text-muted mt-2 mb-0">No wallet transactions found</p></div>',
            zeroRecords: '<div class="text-center py-4"><i class="ti tabler-search" style="font-size:2.5rem;color:#ccc;"></i><p class="text-muted mt-2 mb-0">No matching transactions</p></div>'
        },
        dom: '<"d-flex justify-content-between align-items-center px-3 py-2"lf>t<"d-flex justify-content-between align-items-center px-3 py-2"ip>',
        drawCallback: function () {
            $('[title]').tooltip({ trigger: 'hover' });
        }
    });

    $('#type-filter .btn').on('click', function () {
        $('#type-filter .btn').removeClass('active');
        $(this).addClass('active');
        typeFilter = $(this).data('filter');
        dt.draw();
    });

    $('#status-filter').on('change', function () {
        statusFilter = $(this).val();
        dt.draw();
    });

    $('#source-filter').on('change', function () {
        sourceFilter = $(this).val();
        dt.draw();
    });
});
</script>
@endpush
