@extends('layouts.app')
@section('title','Wallet Transactions')

@section('content')
<div class="app-ecommerce">

<div class="card p-2">
    <div class="card-header">
        <h5 class="mb-0">All Wallet Transactions</h5>
        <small class="text-muted">
            Credits & debits across all users
        </small>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered align-middle" id="transactions-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Source</th>
                    <th>Reference</th>
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
$('#transactions-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: '{{ route('wallets.all.transactions') }}',
    order: [[5, 'desc']],
    columns: [
        { data: 'user', orderable: false, searchable: true },
        { data: 'type', orderable: false },
        { data: 'amount', name: 'amount' },
        { data: 'source', name: 'source' },
        { data: 'reference', orderable: false },
        { data: 'date', name: 'created_at' }
    ]
});
</script>
@endpush
