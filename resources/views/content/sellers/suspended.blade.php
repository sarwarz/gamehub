@extends('layouts.app')
@section('title', 'Suspended Sellers')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}" />
@endpush

@section('content')
<div class="app-ecommerce-sellers">

    @include('partials.alerts')

    <div class="card p-2">
        <div class="card-header">
            <h5 class="mb-0">Suspended Sellers</h5>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered" id="sellers-table">
                <thead>
                    <tr>
                        <th>
                            <input type="checkbox" class="form-check-input" id="select-all">
                        </th>
                        <th>Seller</th>
                        <th>Store Name</th>
                        <th>Status</th>
                        <th>Verified</th>
                        <th>Total Sales</th>
                        <th>Balance</th>
                        <th width="180">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

</div>
@endsection

@push('page-js')
<script>
$('#sellers-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: '{{ route('sellers.suspended') }}',
    columns: [
        { data: 'checkbox', orderable: false, searchable: false },
        { data: 'seller_column', name: 'seller' },
        { data: 'store_name', name: 'store_name' },
        { data: 'status_badge', orderable: false, searchable: false },
        { data: 'is_verified_badge', orderable: false, searchable: false },
        { data: 'total_sales', name: 'total_sales' },
        { data: 'balance', orderable: false, searchable: false },
        { data: 'actions', orderable: false, searchable: false }
    ]
});
</script>
@endpush
