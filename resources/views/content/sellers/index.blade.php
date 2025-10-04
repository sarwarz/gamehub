@extends('layouts.app')
@section('title', 'Sellers')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}" />
@endpush

@section('content')
<div class="app-ecommerce-sellers">

    @include('partials.alerts')

    <!-- Sellers List Table -->
    <div class="card p-2">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Sellers</h5>
            <div>
                <button class="btn btn-danger" id="bulk-delete" data-url="{{ route('sellers.bulk-delete') }}">
                    <i class="menu-icon icon-base ti tabler-trash"></i> Delete Selected
                </button>
                <a class="btn btn-primary" href="{{ route('sellers.create') }}">
                    <i class="menu-icon icon-base ti tabler-plus"></i> Add Seller
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered align-middle" id="sellers-table">
                <thead>
                    <tr>
                        <th width="40">
                            <input type="checkbox" class="form-check-input" id="select-all">
                        </th>
                        <th>Seller</th>
                        <th>Store</th>
                        <th>Status</th>
                        <th>Verified</th>
                        <th>Total Sales</th>
                        <th>Balance</th>
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
let table = $('#sellers-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: '{{ route('sellers.index') }}',
    columns: [
        { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false },
        { data: 'seller_column', name: 'name', orderable: false, searchable: true },
        { data: 'store_name', name: 'store_name', searchable: true },
        { data: 'status_badge', name: 'status', orderable: false, searchable: false },
        { data: 'is_verified_badge', name: 'is_verified', orderable: false, searchable: false },
        { data: 'total_sales', name: 'total_sales', searchable: false },
        { data: 'balance', name: 'balance', searchable: false },
        { data: 'actions', name: 'actions', orderable: false, searchable: false }
    ],
    columnDefs: [
        { targets: [0,3,4,5,6,7], className: "text-center" } // center badges, numbers & actions
    ]
});
</script>
@endpush
