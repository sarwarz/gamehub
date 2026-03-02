@extends('layouts.app')
@section('title', 'Refund Requests')

@push('page-css')
<style>
    .bulk-bar { background:#f0f2ff; border-radius:8px; animation:bulkSlide .3s ease; }
    @keyframes bulkSlide { from { opacity:0; transform:translateY(-8px); } to { opacity:1; transform:translateY(0); } }
</style>
@endpush

@section('content')

    @include('partials.alerts')

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1"><i class="ti tabler-receipt-refund me-2"></i>Refund Requests</h4>
            <p class="text-muted mb-0">Manage customer refund requests</p>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-xl-2 col-sm-4 mb-xl-0 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3 bg-label-primary">
                            <i class="ti tabler-file-invoice fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ number_format($stats['total']) }}</h5>
                            <small class="text-muted">Total</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-sm-4 mb-xl-0 mb-3">
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
        <div class="col-xl-2 col-sm-4 mb-xl-0 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3 bg-label-info">
                            <i class="ti tabler-check fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ number_format($stats['approved']) }}</h5>
                            <small class="text-muted">Approved</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-sm-4 mb-xl-0 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3 bg-label-danger">
                            <i class="ti tabler-x fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ number_format($stats['rejected']) }}</h5>
                            <small class="text-muted">Rejected</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-sm-4 mb-xl-0 mb-3">
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
        <div class="col-xl-2 col-sm-4 mb-xl-0 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3 bg-label-secondary">
                            <i class="ti tabler-currency-dollar fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ format_currency($stats['amount']) }}</h5>
                            <small class="text-muted">Refunded</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0">All Refund Requests</h5>
            <div class="d-flex gap-2">
                <select id="filter-status" class="form-select form-select-sm" style="width:140px">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                    <option value="processing">Processing</option>
                    <option value="completed">Completed</option>
                </select>
                <select id="filter-type" class="form-select form-select-sm" style="width:120px">
                    <option value="">All Types</option>
                    <option value="full">Full</option>
                    <option value="partial">Partial</option>
                </select>
            </div>
        </div>
        <div class="card-body">
            <div id="bulk-bar" class="d-none p-3 mb-3 bulk-bar d-flex align-items-center justify-content-between">
                <span><strong id="bulk-count">0</strong> selected</span>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-success" onclick="bulkAction('approve')">Approve</button>
                    <button class="btn btn-sm btn-danger" onclick="bulkAction('reject')">Reject</button>
                </div>
            </div>
            <table id="refund-table" class="table table-hover" style="width:100%">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="select-all"></th>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Order</th>
                        <th>Seller</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

@endsection

@push('page-js')
<script>
$(function () {
    const table = $('#refund-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("refunds.index") }}',
            data: function (d) {
                d.status = $('#filter-status').val();
                d.type = $('#filter-type').val();
            }
        },
        columns: [
            { data: null, orderable: false, searchable: false, className: 'text-center',
              render: function (data) { return '<input type="checkbox" class="row-check" value="' + data.id + '">'; }
            },
            { data: 'id' },
            { data: 'customer' },
            { data: 'order_number' },
            { data: 'seller_name' },
            { data: 'type', render: d => '<span class="badge bg-label-' + (d === 'full' ? 'primary' : 'info') + '">' + d.charAt(0).toUpperCase() + d.slice(1) + '</span>' },
            { data: 'formatted_amount' },
            { data: 'reason', render: d => d ? d.substring(0, 40) + (d.length > 40 ? '...' : '') : '' },
            { data: 'status_badge' },
            { data: 'created_at', render: d => d ? new Date(d).toLocaleDateString() : '' },
            { data: 'actions', orderable: false, searchable: false },
        ],
        order: [[1, 'desc']],
    });

    $('#filter-status, #filter-type').on('change', () => table.ajax.reload());

    $('#select-all').on('change', function () {
        $('.row-check').prop('checked', this.checked);
        toggleBulkBar();
    });

    $(document).on('change', '.row-check', toggleBulkBar);

    function toggleBulkBar() {
        const checked = $('.row-check:checked').length;
        $('#bulk-count').text(checked);
        $('#bulk-bar').toggleClass('d-none', checked === 0).toggleClass('d-flex', checked > 0);
    }

    window.bulkAction = function (action) {
        const ids = $('.row-check:checked').map(function () { return this.value; }).get();
        if (!ids.length) return;

        Swal.fire({
            title: 'Confirm ' + action,
            text: action === 'reject' ? 'Please provide a reason:' : 'Are you sure?',
            input: action === 'reject' ? 'textarea' : undefined,
            showCancelButton: true,
            confirmButtonText: action.charAt(0).toUpperCase() + action.slice(1),
        }).then(result => {
            if (!result.isConfirmed) return;

            $.ajax({
                url: '{{ route("refunds.bulk-action") }}',
                method: 'POST',
                data: { _token: '{{ csrf_token() }}', ids, action, admin_note: result.value || null },
                success: r => {
                    Swal.fire('Done', r.message, 'success');
                    table.ajax.reload();
                },
                error: e => Swal.fire('Error', e.responseJSON?.message || 'Failed', 'error'),
            });
        });
    };
});
</script>
@endpush
