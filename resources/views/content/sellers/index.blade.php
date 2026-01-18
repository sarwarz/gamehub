@extends('layouts.app')
@section('title', 'Sellers')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}" />
@endpush

@section('content')
<div class="app-ecommerce-sellers">

    @include('partials.alerts')

    <div class="card p-2">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Sellers</h5>
            <div class="d-flex gap-2">
                <button class="btn btn-danger" id="bulk-delete"
                        data-url="{{ route('sellers.bulk-delete') }}">
                    <i class="menu-icon icon-base ti tabler-trash"></i>
                    Delete Selected
                </button>

                <a class="btn btn-primary" href="{{ route('sellers.create') }}">
                    <i class="menu-icon icon-base ti tabler-plus"></i>
                    Add Seller
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
                    <th>Actions</th>
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

    const table = $('#sellers-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('sellers.index') }}',
        columns: [
            { data: 'checkbox', orderable: false, searchable: false },
            { data: 'seller_column', orderable: false, searchable: true },
            { data: 'store_name', searchable: true },
            { data: 'status_badge', orderable: false, searchable: false },
            { data: 'is_verified_badge', orderable: false, searchable: false },
            { data: 'total_sales', searchable: false },
            { data: 'actions', orderable: false, searchable: false }
        ],
        columnDefs: [
            { targets: [0,3,4,5,6], className: 'text-center align-middle' }
        ],
        drawCallback: function () {
            $('#select-all').prop('checked', false);
        }
    });

    /* Select all rows */
    $('#select-all').on('change', function () {
        $('.bulk-checkbox').prop('checked', this.checked);
    });

    /* Sync select-all when individual checkbox changes */
    $(document).on('change', '.bulk-checkbox', function () {
        $('#select-all').prop(
            'checked',
            $('.bulk-checkbox:checked').length === $('.bulk-checkbox').length
        );
    });

    /* Bulk delete */
    $('#bulk-delete').on('click', function () {
        const ids = $('.bulk-checkbox:checked')
            .map(function () { return $(this).val(); })
            .get();

        if (!ids.length) {
            alert('Please select at least one seller.');
            return;
        }

        if (!confirm('Are you sure you want to delete selected sellers?')) {
            return;
        }

        $.ajax({
            url: $(this).data('url'),
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                ids: ids
            },
            success: function () {
                table.ajax.reload();
            },
            error: function () {
                alert('Failed to delete sellers.');
            }
        });
    });

});
</script>
@endpush
