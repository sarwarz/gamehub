@extends('layouts.app')
@section('title', 'Failed Transactions')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}" />
@endpush

@section('content')
<div class="app-ecommerce-transactions">

    <div class="card p-2">
        <div class="card-header">
            <h5 class="mb-0">Failed Transactions</h5>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered" id="transactions-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>TRX</th>
                        <th>Owner</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Category</th>
                        <th>Status</th>
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
    ajax: '{{ route('transactions.failed') }}',
    columns: [
        { data: 'DT_RowIndex', orderable: false, searchable: false },
        { data: 'trx', name: 'trx' },
        { data: 'owner', orderable: false },
        { data: 'type_badge', orderable: false },
        { data: 'amount', orderable: false },
        { data: 'category', name: 'category' },
        { data: 'status_badge', orderable: false },
        { data: 'created_at', name: 'created_at' }
    ]
});
</script>
@endpush
