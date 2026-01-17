@extends('layouts.app')
@section('title', 'Inactive Products')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}" />
@endpush

@section('content')
<div class="app-ecommerce-products">

    @include('partials.alerts')

    <!-- Inactive Products List -->
    <div class="card p-2">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                Inactive Products
                <span class="badge bg-secondary ms-2">Inactive</span>
            </h5>
            <div>
                <button class="btn btn-danger" id="bulk-delete"
                    data-url="{{ route('products.bulk-delete') }}">
                    <i class="menu-icon icon-base ti tabler-trash"></i>
                    Delete Selected
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered" id="products-table">
                <thead>
                    <tr>
                        <th>
                            <input type="checkbox" class="form-check-input" id="select-all">
                        </th>
                        <th>Product</th>
                        <th>Categories</th>
                        <th>Types</th>
                        <th>Regions</th>
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
let table = $('#products-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: '{{ route('products.inactive') }}',
    columns: [
        { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false },
        { data: 'product_column', name: 'title', orderable: false, searchable: true },
        { data: 'categories', name: 'categories', orderable: false, searchable: false },
        { data: 'types', name: 'types', orderable: false, searchable: false },
        { data: 'regions', name: 'regions', orderable: false, searchable: false },
        { data: 'status_badge', name: 'status', orderable: false, searchable: false },
        { data: 'actions', name: 'actions', orderable: false, searchable: false }
    ]
});
</script>
@endpush
