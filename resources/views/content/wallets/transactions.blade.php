@extends('layouts.app')
@section('title','Wallet Transactions')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">
            <span class="text-muted fw-light">Wallets /</span> Transactions
        </h4>
        <p class="text-muted mb-0">Transaction history for <strong>{{ $wallet->user->name ?? 'User #'.$wallet->user_id }}</strong></p>
    </div>
    <a href="{{ route('wallets.index') }}" class="btn btn-label-secondary">
        <i class="ti tabler-arrow-left ti-xs me-1"></i> Back to Wallets
    </a>
</div>

{{-- Wallet Owner Card + Quick Stats --}}
<div class="row g-4 mb-4">
    {{-- Owner Card --}}
    <div class="col-xl-4 col-lg-5">
        <div class="card h-100 border-0 shadow-none" style="background: linear-gradient(135deg, #696cff 0%, #8592ff 100%);">
            <div class="card-body text-white d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center mb-3">
                    <div class="avatar avatar-lg me-3" style="background: rgba(255,255,255,.2); border-radius: 12px;">
                        @if($wallet->user->avatar ?? false)
                            <img src="{{ asset($wallet->user->avatar) }}" alt="" class="rounded" style="width:100%; height:100%; object-fit:cover;">
                        @else
                            <span class="avatar-initial rounded" style="background: rgba(255,255,255,.25); font-size: 1.1rem;">
                                {{ strtoupper(substr($wallet->user->name ?? 'U', 0, 2)) }}
                            </span>
                        @endif
                    </div>
                    <div>
                        <h5 class="text-white mb-0">{{ $wallet->user->name ?? 'N/A' }}</h5>
                        <small style="opacity:.8;">{{ $wallet->user->email ?? '' }}</small>
                    </div>
                </div>
                <div>
                    <div style="opacity:.75; font-size:.75rem; text-transform:uppercase; letter-spacing:1px;" class="mb-1">Current Balance</div>
                    <h2 class="text-white mb-0">${{ number_format($wallet->balance, 2) }}</h2>
                </div>
                <div class="d-flex justify-content-between mt-3 pt-3" style="border-top: 1px solid rgba(255,255,255,.2);">
                    <div>
                        <small style="opacity:.7;">Status</small>
                        <div>
                            @if($wallet->is_active)
                                <span class="badge" style="background: rgba(255,255,255,.2);">Active</span>
                            @else
                                <span class="badge" style="background: rgba(255,80,80,.5);">Disabled</span>
                            @endif
                        </div>
                    </div>
                    <div class="text-end">
                        <small style="opacity:.7;">Wallet ID</small>
                        <div class="fw-semibold">#{{ $wallet->id }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="col-xl-8 col-lg-7">
        <div class="row g-4 h-100">
            <div class="col-sm-6">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <div class="avatar avatar-sm me-2" style="background: rgba(40,199,111,.12); border-radius: 8px;">
                                <span class="avatar-initial rounded" style="background: transparent; color: #28c76f;"><i class="ti tabler-arrow-down-left"></i></span>
                            </div>
                            <span class="text-muted">Total Credits</span>
                        </div>
                        <h3 class="mb-0 text-success">+${{ number_format($stats['total_credit'], 2) }}</h3>
                        <small class="text-muted">All incoming transactions</small>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <div class="avatar avatar-sm me-2" style="background: rgba(234,84,85,.12); border-radius: 8px;">
                                <span class="avatar-initial rounded" style="background: transparent; color: #ea5455;"><i class="ti tabler-arrow-up-right"></i></span>
                            </div>
                            <span class="text-muted">Total Debits</span>
                        </div>
                        <h3 class="mb-0 text-danger">-${{ number_format($stats['total_debit'], 2) }}</h3>
                        <small class="text-muted">All outgoing transactions</small>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <div class="avatar avatar-sm me-2" style="background: rgba(105,108,255,.12); border-radius: 8px;">
                                <span class="avatar-initial rounded" style="background: transparent; color: #696cff;"><i class="ti tabler-receipt-2"></i></span>
                            </div>
                            <span class="text-muted">Total Transactions</span>
                        </div>
                        <h3 class="mb-0">{{ number_format($stats['total_txns']) }}</h3>
                        <small class="text-muted">Credit & debit combined</small>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <div class="avatar avatar-sm me-2" style="background: rgba(255,159,67,.12); border-radius: 8px;">
                                <span class="avatar-initial rounded" style="background: transparent; color: #ff9f43;"><i class="ti tabler-clock"></i></span>
                            </div>
                            <span class="text-muted">Last Activity</span>
                        </div>
                        <h5 class="mb-0">
                            @if($stats['last_activity'])
                                {{ $stats['last_activity']->diffForHumans() }}
                            @else
                                <span class="text-muted">No activity</span>
                            @endif
                        </h5>
                        <small class="text-muted">
                            @if($stats['last_activity'])
                                {{ $stats['last_activity']->format('M d, Y h:i A') }}
                            @else
                                —
                            @endif
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Transactions Table --}}
<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3 py-3">
        <div class="d-flex align-items-center gap-2">
            <div class="avatar avatar-sm" style="background: rgba(105,108,255,.12); border-radius: 8px;">
                <span class="avatar-initial rounded" style="background: transparent; color: #696cff;"><i class="ti tabler-list-details"></i></span>
            </div>
            <h5 class="mb-0">Transaction History</h5>
        </div>
        <div class="d-flex align-items-center gap-2">
            <div class="btn-group" role="group" id="type-filter">
                <button type="button" class="btn btn-label-secondary btn-sm active" data-filter="all">All</button>
                <button type="button" class="btn btn-label-success btn-sm" data-filter="credit">Credit</button>
                <button type="button" class="btn btn-label-danger btn-sm" data-filter="debit">Debit</button>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table" id="transactions-table" style="width:100%">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Source</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Balance After</th>
                    <th>Date</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
@endsection

@push('page-css')
<style>
    #transactions-table td { vertical-align: middle; }
    #type-filter .btn.active {
        box-shadow: 0 2px 6px rgba(105,108,255,.35);
    }
    #type-filter .btn-label-success.active {
        box-shadow: 0 2px 6px rgba(40,199,111,.35);
    }
    #type-filter .btn-label-danger.active {
        box-shadow: 0 2px 6px rgba(234,84,85,.35);
    }
</style>
@endpush

@push('page-js')
<script>
$(function () {
    var typeFilter = '';

    var dt = $('#transactions-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("wallets.transactions", $wallet) }}',
            data: function (d) {
                d.type_filter = typeFilter;
            }
        },
        order: [[6, 'desc']],
        columns: [
            { data: 'type',          orderable: false, searchable: false },
            { data: 'amount',        name: 'amount' },
            { data: 'source',        name: 'source' },
            { data: 'description',   name: 'description' },
            { data: 'status',        orderable: false, searchable: false },
            { data: 'balance_after', name: 'balance_after' },
            { data: 'created_at',    name: 'created_at' }
        ],
        language: {
            emptyTable: '<div class="text-center py-4"><i class="ti tabler-receipt-off" style="font-size:2rem;color:#ccc;"></i><p class="text-muted mt-2 mb-0">No transactions found</p></div>',
            zeroRecords: '<div class="text-center py-4"><i class="ti tabler-search" style="font-size:2rem;color:#ccc;"></i><p class="text-muted mt-2 mb-0">No matching transactions</p></div>'
        },
        dom: '<"d-flex justify-content-between align-items-center px-3 py-2"lf>t<"d-flex justify-content-between align-items-center px-3 py-2"ip>',
        drawCallback: function () {
            $('[title]').tooltip({ trigger: 'hover' });
        }
    });

    $('#type-filter .btn').on('click', function () {
        $('#type-filter .btn').removeClass('active');
        $(this).addClass('active');
        typeFilter = $(this).data('filter') === 'all' ? '' : $(this).data('filter');
        dt.draw();
    });
});
</script>
@endpush
