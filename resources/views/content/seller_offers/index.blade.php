@extends('layouts.app')
@section('title', 'Seller Offers')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}" />
@endpush

@section('content')
<div class="app-ecommerce-offers">

    @include('partials.alerts')

    <div class="card p-2">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Seller Offers</h5>
            <div>
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

$(document).on('click', '.status-toggle-btn', function () {

    const button = $(this);
    const id = button.data('id');
    const status = button.data('status');

    Swal.fire({
        title: 'Are you sure?',
        text: `This will mark the offer as ${status}.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, continue'
    }).then((result) => {
        if (!result.isConfirmed) return;

        $.ajax({
            url: `/dashboard/seller-offers/${id}/toggle-status`,
            type: 'POST',
            data: {
                status: status,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function (res) {

                Swal.fire({
                    icon: 'success',
                    title: 'Updated',
                    text: res.message,
                    timer: 1500,
                    showConfirmButton: false
                });

                // Reload DataTable
                $('.dataTable').DataTable().ajax.reload(null, false);
            },
            error: function () {
                Swal.fire(
                    'Error',
                    'Failed to update offer status.',
                    'error'
                );
            }
        });
    });
});

window.routes = {
    sellerOfferDelete: "{{ route('seller-offers.destroy', ':id') }}"
};
$(document).on('click', '.delete-btn', function () {

    const id = $(this).data('id');

    Swal.fire({
        title: 'Are you sure?',
        text: 'This item will be permanently deleted.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it',
        cancelButtonText: 'Cancel'
    }).then((result) => {

        if (!result.isConfirmed) return;

        $.ajax({
            url: window.routes.sellerOfferDelete.replace(':id', id),
            type: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function (res) {

                Swal.fire({
                    icon: 'success',
                    title: 'Deleted',
                    text: 'Offer deleted successfully.',
                    timer: 1500,
                    showConfirmButton: false
                });

                // Reload DataTable without resetting page
                $('.dataTable').DataTable().ajax.reload(null, false);
            },
            error: function () {
                Swal.fire(
                    'Error',
                    'Failed to delete item.',
                    'error'
                );
            }
        });
    });
});


</script>
@endpush
