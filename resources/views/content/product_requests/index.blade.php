@extends('layouts.app')
@section('title', 'Product Requests')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}" />
@endpush

@section('content')
<div class="app-ecommerce-products">

    @include('partials.alerts')

    <!-- Product Requests List Table -->
    <div class="card p-2">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Product Requests</h5>
            <div>
                <a class="btn btn-primary" href="{{ route('product-requests.create') }}">
                    <i class="menu-icon icon-base ti tabler-plus"></i>
                    Add Request
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered" id="product-requests-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Request</th>
                        <th>Details</th>
                        <th>Source</th>
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
let table = $('#product-requests-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: '{{ route('product-requests.index') }}',
    columns: [
        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
        { data: 'request_info', name: 'title', orderable: false, searchable: true },
        { data: 'meta', name: 'meta', orderable: false, searchable: false },
        { data: 'source', name: 'source', orderable: false, searchable: false },
        { data: 'status_badge', name: 'status', orderable: false, searchable: false },
        { data: 'actions', name: 'actions', orderable: false, searchable: false }
    ]
});
</script>
@endpush
