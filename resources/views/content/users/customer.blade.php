@extends('layouts.app')
@section('title', 'Customers')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}">
@endpush

@section('content')
<div class="app-ecommerce">

    @include('partials.alerts')

    <div class="card p-2">

        <!-- Header -->
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Customers</h5>
                <small class="text-muted">
                    Manage registered customers
                </small>
            </div>
        </div>

        <!-- Table -->
        <div class="table-responsive">
            <table class="table table-bordered align-middle" id="customers-table">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Roles</th>
                        <th>Status</th>
                        <th>Verified</th>
                        <th>Wallet Balance</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>

    </div>

</div>
@endsection

@push('page-js')
<script>
let table = $('#customers-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: '{{ route('customer.index') }}',
    order: [[0, 'asc']],
    columns: [
        { data: 'customer', orderable: false, searchable: true },
        { data: 'roles', orderable: false, searchable: false },
        { data: 'status', orderable: false, searchable: false },
        { data: 'verified', orderable: false, searchable: false },
        { data: 'wallet_balance', name: 'wallet.balance' },
        { data: 'actions', orderable: false, searchable: false }
    ],
    columnDefs: [
        { targets: [1,2,3,4], className: 'text-center' }
    ]
});
</script>
@endpush
