@extends('layouts.app')
@section('title', 'Pending Transactions')

@section('content')
<div class="card shadow-sm">
    <div class="card-header border-bottom">
        <div class="d-flex flex-wrap justify-content-between align-items-center">
            <div>
                <h5 class="card-title mb-1"><i class="ti tabler-clock ti-md me-1 text-warning"></i> Pending Transactions</h5>
                <p class="text-muted mb-0 small">Transactions awaiting confirmation or processing</p>
            </div>
            <a href="{{ route('transactions.index') }}" class="btn btn-label-secondary"><i class="ti tabler-arrow-left ti-xs me-1"></i> All Transactions</a>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="transactions-table" style="width:100%">
            <thead class="table-light">
                <tr>
                    <th>#</th>
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
$('#transactions-table').DataTable({
    processing: true, serverSide: true,
    ajax: '{{ route('transactions.pending') }}',
    order: [[7, 'desc']], pageLength: 25,
    columns: [
        { data: 'DT_RowIndex', orderable: false, searchable: false },
        { data: 'trx', name: 'trx' },
        { data: 'owner', orderable: false },
        { data: 'type', orderable: false, className: 'text-center' },
        { data: 'amount', orderable: false, className: 'text-end' },
        { data: 'category', name: 'category', className: 'text-center' },
        { data: 'status', orderable: false, className: 'text-center' },
        { data: 'date', name: 'created_at' }
    ],
    language: { emptyTable: '<div class="py-4 text-center text-muted">No pending transactions</div>' }
});
</script>
@endpush
