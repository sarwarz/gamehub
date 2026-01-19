@extends('layouts.app')
@section('title', 'Products')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}" />
@endpush

@section('content')
<div class="app-ecommerce-products">

    @include('partials.alerts')

    <div class="card mb-3 p-4 collapse" id="filter-collapse">
        <div>
            <div class="card-bodys">

                <form id="filterForm">
                    <div id="filterRows">
                        <!-- Filter row -->
                        <div class="row g-2 align-items-center filter-row mb-2">
                            <div class="col-md-6">
                                <select name="field[]" class="form-select">
                                    <option value="">Select field</option>
                                    <option value="title">Title</option>
                                    <option value="sku">SKU</option>
                                    <option value="status">Status</option>
                                    <option value="delivery_type">Delivery Type</option>
                                    <option value="is_featured">Featured</option>

                                    <option value="category_id">Category</option>
                                    <option value="platform_id">Platform</option>
                                    <option value="type_id">Type</option>
                                    <option value="region_id">Region</option>
                                    <option value="language_id">Language</option>

                                    <option value="developer_id">Developer</option>
                                    <option value="publisher_id">Publisher</option>
                                </select>

                            </div>

                            <div class="col-md-6">
                                <select class="form-select" name="operator[]">
                                    <option value="=">Is equal to</option>
                                    <option value="!=">Is not equal to</option>
                                    <option value="like">Contains</option>
                                    <option value=">">Greater than</option>
                                    <option value="<">Less than</option>
                                </select>
                            </div>

                            <div class="col-md-12">
                                <input type="text" class="form-control" name="value[]" placeholder="Value">
                            </div>

                            <div class="col-md-1 text-start">
                                <button type="button" class="btn btn-outline-danger remove-row d-none">
                                    <i class="menu-icon icon-base ti tabler-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-3">
                        <button type="button" class="btn btn-outline-secondary" id="addFilter">
                            Add additional filter
                        </button>
                        <button type="submit" class="btn btn-primary">
                            Apply
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>


    <!-- Products List Table -->
    <div class="card p-2">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
               <div class="btn-group">
                    <button type="button" class="btn btn-outline-secondary dropdown-toggle waves-effect" data-bs-toggle="dropdown" aria-expanded="false">
                    Bulk Actions
                    </button>
                    <ul class="dropdown-menu" style="">
                        <li><a class="dropdown-item waves-effect" href="javascript:void(0);">Bulk Changes</a></li>
                        <li><a class="dropdown-item waves-effect" id="bulk-delete" href="javascript:void(0);" data-url="{{ route('products.bulk-delete') }}">Delete</a></li>
                    </ul>
                </div>
                <button type="button" class="btn btn-outline-secondary waves-effect" 
                          data-bs-toggle="collapse"
                          data-bs-target="#filter-collapse"
                          aria-expanded="true"
                          aria-controls="filter-collapse">Filters</button>
            </div>
            <div>
                <a class="btn btn-primary" href="{{ route('products.create') }}">
                    <i class="menu-icon icon-base ti tabler-plus"></i> Add Product
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered" id="products-table">
                <thead>
                    <tr>
                        <th><input type="checkbox" class="form-check-input" id="select-all"></th>
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
    ajax: '{{ route('products.index') }}',
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
<script>
$(document).ready(function () {

    // Add new filter row
    $('#addFilter').on('click', function () {
        let $row = $('.filter-row:first').clone();

        $row.find('select, input').val('');
        $row.find('.remove-row').removeClass('d-none');

        $('#filterRows').append($row);
    });

    // Remove filter row (delegated)
    $(document).on('click', '.remove-row', function () {
        $(this).closest('.filter-row').remove();
    });

    // Optional: handle submit
    $('#filterForm').on('submit', function (e) {
        e.preventDefault();

        let data = $(this).serializeArray();
        console.log(data);

        // Example AJAX (Laravel)
        /*
        $.get("{{ route('orders.index') }}", data, function (response) {
            $('#tableData').html(response);
        });
        */
    });

});
</script>


@endpush
