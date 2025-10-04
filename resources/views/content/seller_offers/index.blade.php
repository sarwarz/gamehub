@extends('layouts.app')
@section('title', 'Seller Offers')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}" />
@endpush

@section('content')
<div class="app-ecommerce-offers">

    @include('partials.alerts')

    <!-- Offers List Table -->
    <div class="card p-2">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Seller Offers</h5>
            <div>
                <button class="btn btn-danger" id="bulk-delete" data-url="{{ route('seller-offers.bulk-delete') }}">
                    <i class="menu-icon icon-base ti tabler-trash"></i> Delete Selected
                </button>
                <a class="btn btn-primary" href="{{ route('seller-offers.create') }}">
                    <i class="menu-icon icon-base ti tabler-plus"></i> Add Offer
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered" id="offers-table">
                <thead>
                    <tr>
                        <th><input type="checkbox" class="form-check-input" id="select-all"></th>
                        <th>Seller</th>
                        <th>Product</th>
                        <th>Retail Price</th>
                        <th>Wholesale 10–99</th>
                        <th>Wholesale 100+</th>
                        <th>Status</th>
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
let table = $('#offers-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: '{{ route('seller-offers.index') }}',
    columns: [
        { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false },
        { data: 'seller_column', name: 'seller.store_name', orderable: false, searchable: true },
        { data: 'product_column', name: 'product.title', orderable: false, searchable: true },
        { data: 'retail_price', name: 'retail_price' },
        { data: 'wholesale_10_99_price', name: 'wholesale_10_99_price' },
        { data: 'wholesale_100_plus_price', name: 'wholesale_100_plus_price' },
        { data: 'status_badge', name: 'status', orderable: false, searchable: false },
        { data: 'actions', name: 'actions', orderable: false, searchable: false }
    ]
});

</script>
@endpush
