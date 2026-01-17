@extends('layouts.app')
@section('title','Wallet Transactions')

@section('content')
<div class="app-ecommerce">

<div class="card p-2">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Wallet Transactions</h5>
        <a href="{{ route('wallets.index') }}" class="btn btn-label-secondary">Back</a>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered" id="transactions-table">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Source</th>
                    <th>Description</th>
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
    ajax: '{{ route('wallets.transactions',$wallet) }}',
    order: [[4,'desc']],
    columns: [
        { data: 'type', orderable: false },
        { data: 'amount', name: 'amount' },
        { data: 'source', name: 'source' },
        { data: 'description', name: 'description' },
        { data: 'created_at', name: 'created_at' }
    ]
});
</script>
@endpush
