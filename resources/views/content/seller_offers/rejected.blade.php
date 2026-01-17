@extends('layouts.app')
@section('title', 'Rejected Seller Offers')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}" />
@endpush

@section('content')
<div class="app-ecommerce-offers">

    @include('partials.alerts')

    <div class="card p-2">
        <div class="card-header">
            <h5 class="mb-0">Rejected Seller Offers</h5>
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
$('#offers-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: '{{ route('seller-offers.rejected') }}',
    columns: [
        { data: 'checkbox', orderable: false, searchable: false },
        { data: 'seller', name: 'seller' },
        { data: 'product', name: 'product' },
        { data: 'retail_price', name: 'retail_price' },
        { data: 'wholesale_10_99_price', name: 'wholesale_10_99_price' },
        { data: 'wholesale_100_plus_price', name: 'wholesale_100_plus_price' },
        { data: 'status_badge', orderable: false, searchable: false },
        { data: 'actions', orderable: false, searchable: false }
    ]
});
</script>
@endpush
