@extends('layouts.app')
@section('title', 'Seller Offers')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}">
@endpush

@section('content')
<div class="app-ecommerce-offers">

    @include('partials.alerts')

    {{-- ================= FILTER PANEL ================= --}}
    <div class="card mb-3 p-4 collapse position-relative" id="filter-collapse">

        <button type="button" id="closeFilters"
                class="btn btn-sm btn-icon position-absolute top-0 end-0 m-2">
            <i class="ti tabler-x"></i>
        </button>

        <p class="fw-semibold mb-3">Filters</p>

        <form id="filterForm">
            <div id="filterRows">

                {{-- FILTER ROW --}}
                <div class="row g-2 align-items-center filter-row mb-2">

                    <div class="col-md-6">
                        <select name="field[]" class="form-select">
                            <option value="">Select field</option>
                            <option value="seller_id">Seller</option>
                            <option value="product_id">Product</option>
                            <option value="status">Status</option>
                            <option value="sale_mode">Sale Mode</option>
                            <option value="is_verified">Verified</option>
                            <option value="created_at">Created Date</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <select name="operator[]" class="form-select">
                            <option value="=">Is equal</option>
                            <option value="!=">Is not equal</option>
                            <option value="like">Contains</option>
                            <option value="not_like">Does not contain</option>
                            <option value=">">Greater than</option>
                            <option value="<">Less than</option>
                            <option value=">=">Greater or equal</option>
                            <option value="<=">Less or equal</option>
                            <option value="in">In list</option>
                            <option value="not_in">Not in list</option>
                        </select>
                    </div>


                    <div class="col-md-12 value-wrapper">
                        <input type="text" name="value[]" class="form-control">
                    </div>

                    <div class="col-md-1">
                        <button type="button"
                                class="btn btn-outline-danger remove-row d-none">
                            <i class="ti tabler-trash"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 mt-3">
                <button type="button" class="btn btn-outline-secondary" id="addFilter">
                    Add Filter
                </button>
                <button type="submit" class="btn btn-primary">Apply</button>
                <button type="button"
                        class="btn btn-outline-danger d-none"
                        id="clearFilters">
                    Clear
                </button>
            </div>
        </form>
    </div>

    {{-- ================= TABLE ================= --}}
    <div class="card p-2">

        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="d-flex gap-2">

                {{-- BULK ACTIONS --}}
                <div class="btn-group">
                    <button class="btn btn-outline-secondary dropdown-toggle"
                            data-bs-toggle="dropdown">
                        Bulk Actions
                    </button>

                    <ul class="dropdown-menu">
                        <li>
                            <a href="#" class="dropdown-item bulk-action"
                               data-action="status"
                               data-status="active">Mark Active</a>
                        </li>
                        <li>
                            <a href="#" class="dropdown-item bulk-action"
                               data-action="status"
                               data-status="inactive">Mark Inactive</a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a href="#" class="dropdown-item bulk-action text-danger"
                               data-action="delete"
                               data-url="{{ route('seller-offers.bulk-delete') }}">
                                Delete Offers
                            </a>
                        </li>
                    </ul>
                </div>

                <button class="btn btn-outline-secondary"
                        data-bs-toggle="collapse"
                        data-bs-target="#filter-collapse">
                    Filters
                </button>
            </div>

            <a class="btn btn-primary" href="{{ route('seller-offers.create') }}">
                <i class="ti tabler-plus"></i> Add Offer
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle" id="offers-table">
                <thead>
                <tr>
                    <th width="30">
                        <input type="checkbox" id="select-all" class="form-check-input">
                    </th>
                    <th>Seller</th>
                    <th>Product</th>
                    <th>Retail</th>
                    <th>Wholesale 10–99</th>
                    <th>Wholesale 100+</th>
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

const OffersPage = {

    table: null,

    init() {
        this.cache();
        this.initTable();
        this.bindEvents();
    },

    cache() {
        this.$table      = $('#offers-table');
        this.$filterForm = $('#filterForm');
        this.$filterRows = $('#filterRows');
        this.$addBtn     = $('#addFilter');
        this.$clearBtn   = $('#clearFilters');
        this.$selectAll  = $('#select-all');
    },

    /* ================= DATATABLE ================= */
    initTable() {
        this.table = this.$table.DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('seller-offers.index') }}',
                data: d => {
                    const raw = this.$filterForm.serializeArray();
                    const filters = [];

                    for (let i = 0; i < raw.length; i += 3) {
                        filters.push({
                            field: raw[i]?.value,
                            operator: raw[i + 1]?.value,
                            value: raw[i + 2]?.value,
                        });
                    }
                    d.filters = filters;
                }
            },
            columns: [
                { data: 'checkbox', orderable: false, searchable: false },
                { data: 'seller', name: 'seller' },
                { data: 'product', name: 'product' },
                { data: 'retail_price' },
                { data: 'wholesale_10_99_price' },
                { data: 'wholesale_100_plus_price' },
                { data: 'status_badge', orderable: false },
                { data: 'actions', orderable: false }
            ]
        });
    },

    /* ================= EVENTS ================= */
    bindEvents() {

        this.$filterForm.on('submit', e => {
            e.preventDefault();
            this.table.ajax.reload();
            this.$clearBtn.removeClass('d-none');
        });

        this.$clearBtn.on('click', () => this.clearFilters());
        this.$addBtn.on('click', () => this.addRow());

        $(document).on('click', '.remove-row', e =>
            $(e.currentTarget).closest('.filter-row').remove()
        );

        $(document).on('change', 'select[name="field[]"]', e =>
            this.handleFieldChange($(e.currentTarget))
        );

        this.$selectAll.on('change', e =>
            $('.bulk-checkbox').prop('checked', e.target.checked)
        );

        $('#closeFilters').on('click', () => {
            this.clearFilters();
            $('#filter-collapse').collapse('hide');
        });

        $(document).on('click', '.bulk-action', e => {
            e.preventDefault();
            const btn = $(e.currentTarget);
            this.handleBulk(btn.data('action'), btn.data('status'), btn.data('url'));
        });
    },

    /* ================= FILTER LOGIC ================= */
    addRow() {
        const $row = this.$filterRows.find('.filter-row:first').clone();
        $row.find('select, input').val('');
        $row.find('.remove-row').removeClass('d-none');
        this.$filterRows.append($row);
    },

    clearFilters() {
        this.$filterForm[0].reset();
        this.$filterRows.find('.filter-row:not(:first)').remove();
        this.table.ajax.reload();
        this.$clearBtn.addClass('d-none');
    },

    handleFieldChange($select) {
        const field = $select.val();
        const $row = $select.closest('.filter-row');
        const $wrap = $row.find('.value-wrapper');

        const inputs = {

            status: `
                <select name="value[]" class="form-select">
                    <option value="">Select</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="draft">Draft</option>
                    <option value="suspended">Suspended</option>
                </select>`,

            sale_mode: `
                <select name="value[]" class="form-select">
                    <option value="">Select</option>
                    <option value="retail">Retail</option>
                    <option value="wholesale">Wholesale</option>
                    <option value="both">Both</option>
                </select>`,

            is_verified: `
                <select name="value[]" class="form-select">
                    <option value="">Select</option>
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                </select>`,

            seller_id: `
                <select name="value[]" class="form-select select2">
                    <option value="">Select Seller</option>
                    @foreach($sellers as $s)
                        <option value="{{ $s->id }}">{{ $s->store_name }}</option>
                    @endforeach
                </select>`,

            product_id: `
                <select name="value[]" class="form-select select2">
                    <option value="">Select Product</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}">{{ $p->title }}</option>
                    @endforeach
                </select>`,

            created_at: `<input type="date" name="value[]" class="form-control">`
        };

        $wrap.html(inputs[field] || `<input type="text" name="value[]" class="form-control">`);

        $wrap.find('.select2').select2({ width: '100%' });
    },

    /* ================= BULK ================= */
    handleBulk(action, status, url) {

        const ids = $('.bulk-checkbox:checked').map((_, el) => el.value).get();

        if (!ids.length) {
            Swal.fire('Info', 'Select at least one offer', 'info');
            return;
        }

        if (action === 'delete') {
            this.confirmDelete(ids, url);
            return;
        }

        if (action === 'status') {
            this.confirmStatus(ids, status);
        }
    },

    confirmStatus(ids, status) {
        Swal.fire({
            title: 'Update status?',
            text: `Offers will be marked as "${status}"`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes'
        }).then(res => {
            if (!res.isConfirmed) return;

            $.post('{{ route('seller-offers.bulk-status') }}', {
                ids, status, _token: '{{ csrf_token() }}'
            }).done(() => this.afterBulk('Status updated'));
        });
    },

    confirmDelete(ids, url) {
        Swal.fire({
            title: 'Delete offers?',
            icon: 'error',
            showCancelButton: true,
            confirmButtonText: 'Delete'
        }).then(res => {
            if (!res.isConfirmed) return;

            $.post(url, { ids, _token: '{{ csrf_token() }}' })
                .done(() => this.afterBulk('Offers deleted'));
        });
    },

    afterBulk(msg) {
        this.table.ajax.reload(null, false);
        this.$selectAll.prop('checked', false);
        Swal.fire({ icon: 'success', title: msg, timer: 1200, showConfirmButton: false });
    }
};

$(document).ready(() => OffersPage.init());

})(jQuery);
</script>
@endpush
