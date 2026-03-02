@extends('layouts.app')
@section('title', 'Wallets')

@section('content')

    {{-- Page Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1"><i class="ti tabler-wallet me-2"></i>Wallets</h4>
            <p class="text-muted mb-0">Manage user wallet balances and transactions</p>
        </div>
        <button class="btn btn-primary" id="credit-debit-wallet">
            <i class="ti tabler-plus me-1"></i> Credit / Debit
        </button>
    </div>

    {{-- Stats --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3 bg-label-primary">
                            <i class="ti tabler-wallet fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ number_format($stats['total']) }}</h5>
                            <small class="text-muted">Total Wallets</small>
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
                            <h5 class="mb-0">{{ number_format($stats['active']) }}</h5>
                            <small class="text-muted">Active Wallets</small>
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
                            <i class="ti tabler-cash fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ format_currency($stats['balance']) }}</h5>
                            <small class="text-muted">Total Balance</small>
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
                <h5 class="mb-0"><i class="ti tabler-list me-2"></i>All Wallets</h5>
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-label-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#filter-collapse" aria-expanded="false">
                        <i class="ti tabler-filter me-1"></i> Filters
                    </button>
                </div>
            </div>

            {{-- Collapsible Filter Row --}}
            <div class="collapse mt-3" id="filter-collapse">
                <div class="row g-3 pb-3 border-bottom">
                    <div class="col-md-3 col-sm-6">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="ti tabler-circle-dot ti-xs"></i></span>
                            <select class="form-select" id="filter-status">
                                <option value="">All Statuses</option>
                                <option value="1">Active</option>
                                <option value="0">Disabled</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 d-flex gap-2">
                        <button class="btn btn-primary btn-sm flex-fill" id="btn-apply-filters">
                            <i class="ti tabler-search me-1"></i> Apply
                        </button>
                        <button class="btn btn-outline-secondary btn-sm flex-fill" id="btn-clear-filters">
                            <i class="ti tabler-filter-off me-1"></i> Clear
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="wallets-table" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <th>User</th>
                        <th class="text-end">Balance</th>
                        <th class="text-center">Status</th>
                        <th width="120">Actions</th>
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

    const WalletsPage = {
        table: null,

        init() {
            this.initDataTable();
            this.bindEvents();
        },

        initDataTable() {
            this.table = $('#wallets-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('wallets.index') }}',
                    data: d => {
                        d.filter_status = $('#filter-status').val();
                    }
                },
                columns: [
                    { data: 'user', orderable: false },
                    { data: 'balance', name: 'balance', className: 'text-end' },
                    { data: 'status', orderable: false, className: 'text-center' },
                    { data: 'actions', orderable: false, searchable: false }
                ],
                language: {
                    emptyTable: '<div class="py-4 text-center"><i class="ti tabler-wallet-off ti-xl text-muted mb-2 d-block"></i><span class="text-muted">No wallets found</span></div>'
                }
            });
        },

        bindEvents() {
            const self = this;

            $('#btn-apply-filters').on('click', () => self.table.ajax.reload());
            $('#btn-clear-filters').on('click', () => {
                $('#filter-status').val('');
                self.table.ajax.reload();
            });

            $('#credit-debit-wallet').on('click', () => self.showCreditDebitModal());
        },

        showCreditDebitModal() {
            const self = this;

            Swal.fire({
                title: '',
                html: `
                    <div class="wallet-modal">
                        <div class="wallet-modal-header">
                            <div class="wallet-modal-icon">
                                <i class="ti tabler-wallet" style="font-size:24px"></i>
                            </div>
                            <h4 style="margin:0 0 4px;font-weight:600;font-size:18px">Wallet Transaction</h4>
                            <p style="margin:0;color:#a1a1aa;font-size:13px">Credit or debit a user's wallet balance</p>
                        </div>

                        <div class="wallet-type-selector">
                            <button type="button" class="wallet-type-btn active" data-type="credit" id="btn-type-credit">
                                <i class="ti tabler-arrow-down-left" style="font-size:18px"></i>
                                <span>Credit</span>
                                <small>Add funds</small>
                            </button>
                            <button type="button" class="wallet-type-btn" data-type="debit" id="btn-type-debit">
                                <i class="ti tabler-arrow-up-right" style="font-size:18px"></i>
                                <span>Debit</span>
                                <small>Remove funds</small>
                            </button>
                        </div>
                        <input type="hidden" id="wallet_type" value="credit">

                        <div class="wallet-form-group">
                            <label class="wallet-label"><i class="ti tabler-user ti-xs me-1"></i>User</label>
                            <select id="user_id" class="form-select">
                                <option value="">Search user...</option>
                                @foreach(\App\Models\User::orderBy('name')->get() as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="wallet-form-group">
                            <label class="wallet-label"><i class="ti tabler-currency-dollar ti-xs me-1"></i>Amount</label>
                            <div class="wallet-amount-wrap">
                                <span class="wallet-currency">USD</span>
                                <input id="amount" type="number" step="0.01" min="0.01" class="form-control wallet-amount-input" placeholder="0.00">
                            </div>
                        </div>

                        <div class="wallet-form-group">
                            <label class="wallet-label"><i class="ti tabler-notes ti-xs me-1"></i>Description <span style="color:#a1a1aa;font-weight:400">(optional)</span></label>
                            <textarea id="description" class="form-control" rows="2" placeholder="Reason for this transaction..." style="resize:none"></textarea>
                        </div>

                        <div class="wallet-preview" id="wallet-preview" style="display:none">
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="wallet-preview-label">Transaction</span>
                                <span class="wallet-preview-value" id="preview-type">-</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mt-1">
                                <span class="wallet-preview-label">Amount</span>
                                <span class="wallet-preview-value fw-bold" id="preview-amount">$0.00</span>
                            </div>
                        </div>
                    </div>

                    <style>
                        .wallet-modal { text-align:left; }
                        .wallet-modal-header { text-align:center; margin-bottom:20px; }
                        .wallet-modal-icon {
                            width:52px; height:52px; border-radius:14px;
                            background:linear-gradient(135deg, #7c3aed 0%, #6366f1 100%);
                            color:#fff; display:inline-flex; align-items:center; justify-content:center; margin-bottom:12px;
                        }
                        .wallet-type-selector { display:flex; gap:10px; margin-bottom:20px; }
                        .wallet-type-btn {
                            flex:1; padding:12px 8px; border:2px solid #e4e4e7; border-radius:10px;
                            background:#fff; cursor:pointer; text-align:center; transition:all .2s;
                            display:flex; flex-direction:column; align-items:center; gap:2px;
                        }
                        .wallet-type-btn span { font-weight:600; font-size:14px; }
                        .wallet-type-btn small { color:#a1a1aa; font-size:11px; }
                        .wallet-type-btn:hover { border-color:#a78bfa; background:#faf5ff; }
                        .wallet-type-btn.active[data-type="credit"] {
                            border-color:#22c55e; background:#f0fdf4; color:#16a34a;
                        }
                        .wallet-type-btn.active[data-type="credit"] small { color:#16a34a; }
                        .wallet-type-btn.active[data-type="debit"] {
                            border-color:#ef4444; background:#fef2f2; color:#dc2626;
                        }
                        .wallet-type-btn.active[data-type="debit"] small { color:#dc2626; }
                        .wallet-form-group { margin-bottom:16px; }
                        .wallet-label { display:block; font-size:13px; font-weight:600; color:#3f3f46; margin-bottom:6px; }
                        .wallet-amount-wrap { position:relative; }
                        .wallet-currency {
                            position:absolute; left:12px; top:50%; transform:translateY(-50%);
                            font-size:13px; font-weight:700; color:#a1a1aa; pointer-events:none;
                        }
                        .wallet-amount-input { padding-left:50px !important; font-size:16px; font-weight:600; }
                        .wallet-preview {
                            background:#f4f4f5; border-radius:8px; padding:12px 16px; margin-top:8px;
                        }
                        .wallet-preview-label { font-size:13px; color:#71717a; }
                        .wallet-preview-value { font-size:13px; color:#18181b; }
                    </style>
                `,
                showCancelButton: true,
                confirmButtonText: '<i class="ti tabler-check me-1"></i> Process Transaction',
                cancelButtonText: 'Cancel',
                focusConfirm: false,
                width: 460,
                customClass: {
                    popup: 'rounded-4',
                    confirmButton: 'btn btn-primary me-3',
                    cancelButton: 'btn btn-label-secondary'
                },
                buttonsStyling: false,
                didOpen: () => {
                    $('#user_id').select2({
                        dropdownParent: Swal.getPopup(),
                        width: '100%',
                        placeholder: 'Search user...',
                        allowClear: true
                    });

                    $('.wallet-type-btn').on('click', function() {
                        $('.wallet-type-btn').removeClass('active');
                        $(this).addClass('active');
                        $('#wallet_type').val($(this).data('type'));
                        updatePreview();
                    });

                    $('#amount').on('input', updatePreview);

                    function updatePreview() {
                        const type = $('#wallet_type').val();
                        const amount = parseFloat($('#amount').val()) || 0;
                        if (amount > 0) {
                            $('#wallet-preview').show();
                            $('#preview-type').html(type === 'credit'
                                ? '<span style="color:#16a34a">+ Credit</span>'
                                : '<span style="color:#dc2626">- Debit</span>');
                            const sign = type === 'credit' ? '+' : '-';
                            const color = type === 'credit' ? '#16a34a' : '#dc2626';
                            $('#preview-amount').html(`<span style="color:${color}">${sign} $${amount.toFixed(2)}</span>`);
                        } else {
                            $('#wallet-preview').hide();
                        }
                    }
                },
                preConfirm: () => {
                    const data = {
                        type: document.getElementById('wallet_type').value,
                        user_id: document.getElementById('user_id').value,
                        amount: document.getElementById('amount').value,
                        description: document.getElementById('description').value
                    };
                    if (!data.type) { Swal.showValidationMessage('Please select a transaction type'); return false; }
                    if (!data.user_id) { Swal.showValidationMessage('Please select a user'); return false; }
                    if (!data.amount || parseFloat(data.amount) <= 0) { Swal.showValidationMessage('Please enter a valid amount'); return false; }
                    return data;
                }
            }).then((result) => {
                if (!result.isConfirmed) return;

                const data = result.value;
                const label = data.type === 'credit' ? 'credited' : 'debited';

                $.ajax({
                    url: `/dashboard/wallet/${data.user_id}/${data.type}`,
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}', amount: data.amount, description: data.description },
                    success() {
                        Swal.fire({
                            icon: 'success',
                            title: `Wallet ${label}`,
                            text: `$${parseFloat(data.amount).toFixed(2)} has been ${label} successfully.`,
                            showConfirmButton: false,
                            timer: 2000,
                            timerProgressBar: true
                        });
                        self.table.ajax.reload(null, false);
                    },
                    error(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message ?? 'Operation failed', 'error');
                    }
                });
            });
        }
    };

    $(document).ready(() => WalletsPage.init());
})(jQuery);
</script>
@endpush
