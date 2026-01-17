@extends('layouts.app')
@section('title', 'Seller Withdraw Requests')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}" />
@endpush

@section('content')
<div class="app-ecommerce-withdraws">

    @include('partials.alerts')

    <div class="card p-2">
        <div class="card-header">
            <h5 class="mb-0">Seller Withdraw Requests</h5>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered" id="withdraws-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Seller</th>
                        <th>Email</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th width="200">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

</div>
@endsection

@push('page-js')
<script>
$('#withdraws-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: '{{ route('seller-withdraws.index') }}',
    columns: [
        { data: 'DT_RowIndex', orderable: false, searchable: false },
        { data: 'seller', name: 'seller' },
        { data: 'email', name: 'email' },
        { data: 'amount', orderable: false, searchable: false },
        { data: 'method', name: 'method' },
        { data: 'status_badge', orderable: false, searchable: false },
        { data: 'created_at', name: 'created_at' },
        { data: 'actions', orderable: false, searchable: false }
    ]
});
</script>
@endpush
