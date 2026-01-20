@extends('layouts.app')

@section('title', 'Invoices')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}">
@endpush

@section('content')
<div class="app-ecommerce-invoices">

    @include('partials.alerts')

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Invoices</h4>
            <p class="text-muted mb-0">
                Automatically generated invoices from completed orders
            </p>
        </div>
    </div>

    {{-- Invoice List --}}
    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="invoices-table" style="width:100%">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Invoice</th>
                        <th>Customer</th>
                        <th>Order</th>
                        <th>Issued</th>
                        <th>Paid</th>
                        <th class="text-end">Subtotal</th>
                        <th class="text-end">Total</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

</div>
@endsection

@push('page-js')
<script>
$(function () {

    $('#invoices-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('invoices.index') }}",
        order: [[4, 'desc']], // Issued date DESC
        pageLength: 25,
        responsive: true,

        columns: [
            {
                data: 'DT_RowIndex',
                name: 'DT_RowIndex',
                orderable: false,
                searchable: false
            },
            {
                data: 'invoice_number',
                name: 'invoice_number'
            },
            {
                data: 'customer',
                name: 'user.name'
            },
            {
                data: 'order',
                name: 'order.order_number'
            },
            {
                data: 'issued_at',
                name: 'issued_at',
                className: 'text-center'
            },
            {
                data: 'paid_at',
                name: 'paid_at',
                orderable: false,
                searchable: false,
                className: 'text-center'
            },
            {
                data: 'subtotal',
                name: 'subtotal',
                className: 'text-end'
            },
            {
                data: 'grand_total',
                name: 'grand_total',
                className: 'text-end fw-semibold'
            },
            {
                data: 'status',
                name: 'status',
                orderable: false,
                searchable: false,
                className: 'text-center'
            },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false,
                className: 'text-center'
            }
        ]
    });

});
</script>
@endpush
