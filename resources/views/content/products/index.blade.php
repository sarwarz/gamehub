@extends('layouts.app')
@section('title', 'Products')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}">
@endpush

@section('content')
<div class="app-ecommerce-products">

    @include('partials.alerts')

    {{-- ================= FILTER PANEL ================= --}}
    <div class="card mb-3 p-4 collapse" id="filter-collapse">
        {{-- Close (X) Button --}}
        <button type="button"
                id="closeFilters"
                class="btn btn-sm btn-icon position-absolute top-0 end-0 m-2"
                aria-label="Close filters">
            <i class="ti tabler-x"></i>
        </button>

        <div class="card-bodys">
            <p>Filters</p>

            <form id="filterForm">
                <div id="filterRows">

                    {{-- Filter Row --}}
                    <div class="row g-2 align-items-center filter-row mb-2">

                        <div class="col-md-6">
                            <select name="field[]" class="form-select">
                                <option value="">Select field</option>

                                {{-- Product --}}
                                <option value="title">Product Title</option>
                                <option value="sku">SKU</option>
                                <option value="status">Status</option>
                                <option value="is_featured">Featured</option>
                                <option value="created_at">Created At</option>

                                {{-- Relations --}}
                                <option value="category_id">Category</option>
                                <option value="type_id">Type</option>
                                <option value="region_id">Region</option>
                                <option value="developer_id">Developer</option>
                                <option value="publisher_id">Publisher</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <select class="form-select" name="operator[]">
                                <option value="=">Is equal to</option>
                                <option value="!=">Is not equal to</option>
                                <option value="like">Contains</option>
                            </select>
                        </div>

                        <div class="col-md-12 value-wrapper">
                            <input type="text"
                                class="form-control"
                                name="value[]"
                                placeholder="Value">
                        </div>

                        <div class="col-md-1 text-start">
                            <button type="button"
                                    class="btn btn-outline-danger remove-row d-none">
                                <i class="menu-icon icon-base ti tabler-trash"></i>
                            </button>
                        </div>

                    </div>
                </div>

                <div class="d-flex gap-2 mt-3">
                    <button type="button"
                            class="btn btn-outline-secondary"
                            id="addFilter">
                        Add additional filter
                    </button>

                    <button type="submit"
                            class="btn btn-primary">
                        Apply
                    </button>

                    <button type="button"
                            class="btn btn-outline-danger d-none"
                            id="clearFilters">
                        Clear Filters
                    </button>
                </div>
            </form>
        </div>
    </div>


    {{-- ================= TABLE ================= --}}
    <div class="card p-2">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <div class="btn-group">
                    <button type="button"
                            class="btn btn-outline-secondary dropdown-toggle waves-effect"
                            data-bs-toggle="dropdown"
                            aria-expanded="false">
                        Bulk Actions
                    </button>

                    <ul class="dropdown-menu">

                        {{-- STATUS --}}
                        <li>
                            <a class="dropdown-item bulk-action"
                            href="#"
                            data-action="status"
                            data-status="active">
                                Mark as Active
                            </a>
                        </li>

                        <li>
                            <a class="dropdown-item bulk-action"
                            href="#"
                            data-action="status"
                            data-status="inactive">
                                Mark as Inactive
                            </a>
                        </li>

                        <li><hr class="dropdown-divider"></li>

                        {{-- FEATURED --}}
                        <li>
                            <a class="dropdown-item bulk-action"
                            href="#"
                            data-action="featured"
                            data-value="1">
                                Mark as Featured
                            </a>
                        </li>

                        <li>
                            <a class="dropdown-item bulk-action"
                            href="#"
                            data-action="featured"
                            data-value="0">
                                Remove Featured
                            </a>
                        </li>

                        <li><hr class="dropdown-divider"></li>

                        {{-- DELETE --}}
                        <li>
                            <a class="dropdown-item bulk-action text-danger"
                            href="#"
                            data-action="delete"
                            data-url="{{ route('products.bulk-delete') }}">
                                Delete Products
                            </a>
                        </li>
                    </ul>
                </div>


                <button class="btn btn-outline-secondary" data-bs-toggle="collapse"
                        data-bs-target="#filter-collapse">
                    Filters
                </button>
            </div>

            <a href="{{ route('products.create') }}" class="btn btn-primary">
                <i class="ti tabler-plus"></i> Add Product
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle" id="products-table">
                <thead>
                <tr>
                    <th width="30">
                        <input type="checkbox" id="select-all" class="form-check-input">
                    </th>
                    <th>Product</th>
                    <th>Categories</th>
                    <th>Types</th>
                    <th>Regions</th>
                    <th>Status</th>
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
(function ($) {
    'use strict';

    const ProductsPage = {

        table: null,

        init() {
            this.cacheDom();
            this.initDataTable();
            this.bindEvents();
            this.initSelect2(); 
        },

        cacheDom() {
            this.$table        = $('#products-table');
            this.$filterForm   = $('#filterForm');
            this.$filterRows   = $('#filterRows');
            this.$clearBtn     = $('#clearFilters');
            this.$addFilterBtn = $('#addFilter');
            this.$selectAll    = $('#select-all');
        },

        /* ===============================
         * DataTable
         =============================== */
        initDataTable() {
            this.table = this.$table.DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('products.index') }}',
                    data: d => {
                        const raw = this.$filterForm.serializeArray();
                        const filters = [];

                        for (let i = 0; i < raw.length; i += 3) {
                            filters.push({
                                field: raw[i]?.value ?? null,
                                operator: raw[i + 1]?.value ?? null,
                                value: raw[i + 2]?.value ?? null,
                            });
                        }

                        d.filters = filters;
                    }

                },
                order: [[1, 'asc']],
                columns: [
                    { data: 'checkbox', orderable: false, searchable: false },
                    { data: 'product_column', name: 'title' },
                    { data: 'categories', orderable: false, searchable: false },
                    { data: 'types', orderable: false, searchable: false },
                    { data: 'regions', orderable: false, searchable: false },
                    { data: 'status_badge', orderable: false, searchable: false },
                    { data: 'actions', orderable: false, searchable: false }
                ]
            });
        },

        /* ===============================
         * Events
         =============================== */
        bindEvents() {

            // Apply filters
            this.$filterForm.on('submit', e => {
                e.preventDefault();
                this.table.ajax.reload();
                this.$clearBtn.removeClass('d-none');
            });

            // Clear filters
            this.$clearBtn.on('click', () => this.clearFilters());

            // Add filter row
            this.$addFilterBtn.on('click', () => this.addFilterRow());

            // Remove filter row
            $(document).on('click', '.remove-row', e =>
                $(e.currentTarget).closest('.filter-row').remove()
            );

            // Dynamic field input
            $(document).on('change', 'select[name="field[]"]', e =>
                this.handleFieldChange($(e.currentTarget))
            );

            // Select all
            this.$selectAll.on('change', e =>
                $('.bulk-checkbox').prop('checked', e.target.checked)
            );

            // close filter card
            $('#closeFilters').on('click', () => {
                this.clearFilters();
                $('#filter-collapse').collapse('hide');
            });

            // Bulk action click
            $(document).on('click', '.bulk-action', e => {
                e.preventDefault();

                const $btn   = $(e.currentTarget);
                const action = $btn.data('action');

                this.handleBulkAction(
                    action,
                    $btn.data('status') ?? $btn.data('value') ?? null,
                    $btn.data('url') ?? null
                );
            });



        },

        /* ===============================
         * Filters
         =============================== */
        clearFilters() {
            this.$filterForm[0].reset();
            this.$filterRows.find('.filter-row:not(:first)').remove();
            this.$filterRows.find('.value-wrapper').html(this.defaultInput());
            this.table.ajax.reload();
            this.$clearBtn.addClass('d-none');
        },

        addFilterRow() {
            const $row = this.$filterRows.find('.filter-row:first').clone();
            $row.find('select, input').val('');
            $row.find('.remove-row').removeClass('d-none');
            this.$filterRows.append($row);
            this.initSelect2($row);
        },

        handleFieldChange($select) {
            const field = $select.val();
            const $row  = $select.closest('.filter-row');

            $row.find('.value-wrapper').html(this.getFieldInput(field));
            this.initSelect2($row);

            if (['created_at'].includes(field)) {
                $row.find('select[name="operator[]"]').val('=');
            }
        },

        getFieldInput(field) {

            const inputs = {

                status: `
                    <select name="value[]" class="form-select">
                        <option value="">Select Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>`,

                is_featured: `
                    <select name="value[]" class="form-select">
                        <option value="">Featured?</option>
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>`,

                category_id: `
                    <select name="value[]" class="form-select select2">
                        <option value="">Select Category</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>`,

                type_id: `
                    <select name="value[]" class="form-select select2">
                        <option value="">Select Type</option>
                        @foreach($types as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>`,

                region_id: `
                    <select name="value[]" class="form-select select2">
                        <option value="">Select Region</option>
                        @foreach($regions as $region)
                            <option value="{{ $region->id }}">{{ $region->name }}</option>
                        @endforeach
                    </select>`,

                developer_id: `
                    <select name="value[]" class="form-select select2">
                        <option value="">Select Developer</option>
                        @foreach($developers as $dev)
                            <option value="{{ $dev->id }}">{{ $dev->name }}</option>
                        @endforeach
                    </select>`,

                publisher_id: `
                    <select name="value[]" class="form-select select2">
                        <option value="">Select Publisher</option>
                        @foreach($publishers as $pub)
                            <option value="{{ $pub->id }}">{{ $pub->name }}</option>
                        @endforeach
                    </select>`,

                created_at: `
                    <input type="date"
                            id="flatpickr-date"
                            placeholder="YYYY-MM-DD"
                           class="form-control"
                           name="value[]">`
            };

            return inputs[field] || this.defaultInput();
        },

        defaultInput() {
            return `<input type="text"
                           class="form-control"
                           name="value[]"
                           placeholder="Value">`;
        },


        /* ===============================
        * Bulk Actions
        =============================== */
        handleBulkAction(action, value = null, url = null) {

            const ids = this.getSelectedIds();

            if (!ids.length) {
                Swal.fire('Info', 'Please select at least one product', 'info');
                return;
            }

            if (action === 'delete') {
                this.confirmBulkDelete(ids, url);
                return;
            }

            if (action === 'status') {
                this.confirmBulkStatus(ids, value);
                return;
            }

            if (action === 'featured') {
                this.confirmBulkFeatured(ids, value);
                return;
            }
        },

        getSelectedIds() {
            return $('.bulk-checkbox:checked')
                .map((_, el) => el.value)
                .get();
        },

        confirmBulkStatus(ids, status) {
            Swal.fire({
                title: 'Change product status?',
                text: `Selected products will be marked as "${status}".`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, update',
                reverseButtons: true
            }).then(result => {

                if (!result.isConfirmed) return;

                Swal.showLoading();

                $.post('{{ route('products.bulk-status') }}', {
                    ids,
                    status,
                    _token: '{{ csrf_token() }}'
                })
                .done(() => this.afterBulkAction('Status updated'))
                .fail(() => Swal.fire('Error', 'Failed to update status', 'error'));
            });
        },

        confirmBulkFeatured(ids, value) {
            Swal.fire({
                title: 'Update featured products?',
                text: value == 1
                    ? 'Products will be marked as featured.'
                    : 'Products will be removed from featured.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, update',
                reverseButtons: true
            }).then(result => {

                if (!result.isConfirmed) return;

                Swal.showLoading();

                $.post('{{ route('products.bulk-featured') }}', {
                    ids,
                    value,
                    _token: '{{ csrf_token() }}'
                })
                .done(() => this.afterBulkAction('Featured updated'))
                .fail(() => Swal.fire('Error', 'Failed to update featured', 'error'));
            });
        },

        confirmBulkDelete(ids, url) {
            Swal.fire({
                title: 'Delete products?',
                text: 'This action cannot be undone.',
                icon: 'error',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete',
                reverseButtons: true
            }).then(result => {

                if (!result.isConfirmed) return;

                Swal.showLoading();

                $.post(url, {
                    ids,
                    _token: '{{ csrf_token() }}'
                })
                .done(() => this.afterBulkAction('Products deleted'))
                .fail(() => Swal.fire('Error', 'Delete failed', 'error'));
            });
        },

        afterBulkAction(message) {
            Swal.close();
            this.table.ajax.reload(null, false);
            this.$selectAll.prop('checked', false);

            Swal.fire({
                icon: 'success',
                title: message,
                showConfirmButton: false,
                timer: 1500
            });
        },

        initSelect2(context = document) {
            $(context).find('.select2').select2({
                width: '100%',
                dropdownParent: $('#filter-collapse')
            });
        },



    };

    $(document).ready(() => ProductsPage.init());

})(jQuery);
</script>
@endpush
