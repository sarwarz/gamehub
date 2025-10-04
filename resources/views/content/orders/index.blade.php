@extends('layouts.app')
@section('title', 'Orders')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}" />
@endpush

@section('content')
<div class="app-ecommerce-orders">

    @include('partials.alerts')

    <!-- Orders List Table -->
    <div class="card p-2">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Orders</h5>
            <div>
                <button class="btn btn-danger" id="bulk-delete" data-url="{{ route('orders.bulk-delete') }}">
                    <i class="menu-icon icon-base ti tabler-trash"></i> Delete Selected
                </button>
                <a class="btn btn-primary" href="{{ route('orders.create') }}">
                    <i class="menu-icon icon-base ti tabler-plus"></i> Add Order
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered" id="orders-table" style="width:100%">
                <thead>
                    <tr>
                        <th>
                            <input type="checkbox" class="form-check-input" id="select-all">
                        </th>
                        <th>Reference</th>
                        <th>Customer</th>
                        <th>Order Date</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Status</th>
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
let table = $('#orders-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: '{{ route('orders.index') }}',
    order: [[3, 'desc']], // Order by date column (3rd index)
    columns: [
        { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false },
        { 
            data: 'reference', 
            name: 'reference',
            render: function(data, type, row) {
                return data ? data : '#' + row.id;
            }
        },
        { data: 'buyer', name: 'buyer', orderable: false, searchable: true },
        { data: 'order_date', name: 'order_date' },
        { data: 'total_formatted', name: 'total', className: 'text-end fw-bold' },
        { data: 'payment_status', name: 'payment_status', orderable: false, className: 'text-center' },
        { data: 'status_badge', name: 'status', orderable: false, searchable: false, className: 'text-center' },
        { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' }
    ]
});

</script>
@endpush
