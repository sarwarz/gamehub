@extends('layouts.app')
@section('title', 'Product Reviews')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}">
@endpush

@section('content')
<div class="app-ecommerce-products">

    @include('partials.alerts')

    {{-- ================= FILTER PANEL ================= --}}
    <div class="card mb-3 p-4 collapse position-relative" id="filter-collapse">

        <button type="button"
                id="closeFilters"
                class="btn btn-sm btn-icon position-absolute top-0 end-0 m-2">
            <i class="ti tabler-x"></i>
        </button>

        <p>Filters</p>

        <form id="filterForm">
            <div id="filterRows">

                <div class="row g-2 align-items-center filter-row mb-2">

                    <div class="col-md-6">
                        <select name="field[]" class="form-select">
                            <option value="">Select field</option>

                            <option value="product_id">Product</option>
                            <option value="user_id">User</option>
                            <option value="rating">Rating</option>
                            <option value="status">Status</option>
                            <option value="is_verified_purchase">Verified</option>
                            <option value="created_at">Created Date</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <select name="operator[]" class="form-select">
                            <option value="=">Is equal to</option>
                            <option value="like">Contains</option>
                            <option value=">">Greater than</option>
                            <option value="<">Less than</option>
                        </select>
                    </div>

                    <div class="col-md-12 value-wrapper">
                        <input type="text" name="value[]" class="form-control" placeholder="Value">
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
                    Add filter
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
            <div>

                <div class="btn-group">
                    <button class="btn btn-outline-secondary dropdown-toggle"
                            data-bs-toggle="dropdown">
                        Bulk Actions
                    </button>

                    <ul class="dropdown-menu">

                        <li>
                            <a class="dropdown-item bulk-action"
                            href="#"
                            data-action="status"
                            data-status="approved">
                                Approve Reviews
                            </a>
                        </li>

                        <li>
                            <a class="dropdown-item bulk-action"
                            href="#"
                            data-action="status"
                            data-status="rejected">
                                Reject Reviews
                            </a>
                        </li>

                        <li><hr class="dropdown-divider"></li>

                        <li>
                            <a class="dropdown-item bulk-action text-danger"
                            href="#"
                            data-action="delete"
                            data-url="{{ route('product-reviews.bulk-delete') }}">
                                Delete Reviews
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
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle" id="product-reviews-table">
                <thead>
                <tr>
                    <th width="30">
                        <input type="checkbox" id="select-all" class="form-check-input">
                    </th>
                    <th>Review</th>
                    <th>IP</th>
                    <th>Verified</th>
                    <th>Status</th>
                    <th width="160">Actions</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

{{-- ================= VIEW MODAL ================= --}}
<div class="modal fade" id="viewReviewModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Review Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <table class="table table-sm table-bordered">
                    <tr><th>Product</th><td id="vr-product"></td></tr>
                    <tr><th>User</th><td id="vr-user"></td></tr>
                    <tr><th>Email</th><td id="vr-email"></td></tr>
                    <tr><th>Rating</th><td id="vr-rating"></td></tr>
                    <tr><th>Title</th><td id="vr-title"></td></tr>
                    <tr><th>Review</th><td id="vr-review"></td></tr>
                    <tr><th>Status</th><td id="vr-status"></td></tr>
                    <tr><th>Verified</th><td id="vr-verified"></td></tr>
                    <tr><th>IP</th><td id="vr-ip"></td></tr>
                    <tr><th>User Agent</th><td id="vr-agent"></td></tr>
                    <tr><th>Created</th><td id="vr-date"></td></tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
@push('page-js')
<script>
(function ($) {
'use strict';

const ReviewsPage = {

    table: null,

    init() {
        this.cache();
        this.initTable();
        this.bindEvents();
    },

    cache() {
        this.$table      = $('#product-reviews-table');
        this.$filterForm = $('#filterForm');
        this.$filterRows = $('#filterRows');
        this.$addBtn     = $('#addFilter');
        this.$clearBtn   = $('#clearFilters');
        this.$selectAll  = $('#select-all');
    },

    /* ===============================
     * DATATABLE
     =============================== */
    initTable() {
        this.table = this.$table.DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('product-reviews.index') }}',
                data: d => {

                    const raw = this.$filterForm.serializeArray();
                    const filters = [];

                    for (let i = 0; i < raw.length; i += 3) {
                        filters.push({
                            field: raw[i]?.value ?? null,
                            operator: raw[i + 1]?.value ?? '=',
                            value: raw[i + 2]?.value ?? null,
                        });
                    }

                    d.filters = filters;
                }
            },
            order: [[1, 'desc']],
            columns: [
                { data: 'checkbox', orderable: false, searchable: false },
                { data: 'review_info', orderable: false },
                { data: 'ip_address', orderable: false },
                { data: 'verified', orderable: false },
                { data: 'status_badge', orderable: false },
                { data: 'actions', orderable: false }
            ]
        });
    },

    /* ===============================
     * EVENTS
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
        this.$addBtn.on('click', () => this.addRow());

        // Remove filter row
        $(document).on('click', '.remove-row', e =>
            $(e.currentTarget).closest('.filter-row').remove()
        );

        // Dynamic field change
        $(document).on('change', 'select[name="field[]"]', e =>
            this.handleFieldChange($(e.currentTarget))
        );

        // Select all
        this.$selectAll.on('change', e =>
            $('.bulk-checkbox').prop('checked', e.target.checked)
        );

        // Close filter panel
        $('#closeFilters').on('click', () => {
            this.clearFilters();
            $('#filter-collapse').collapse('hide');
        });

        // Bulk actions
        $(document).on('click', '.bulk-action', e => {
            e.preventDefault();

            const $btn = $(e.currentTarget);

            this.handleBulk(
                $btn.data('action'),
                $btn.data('status') ?? null,
                $btn.data('url') ?? null
            );
        });

        // View review
        $(document).on('click', '.btn-view', e =>
            this.viewReview($(e.currentTarget).data('url'))
        );

        // Approve
        $(document).on('click', '.btn-approve', e =>
            this.changeStatus($(e.currentTarget).data('url'), 'approve')
        );

        // Reject
        $(document).on('click', '.btn-reject', e =>
            this.changeStatus($(e.currentTarget).data('url'), 'reject')
        );

        // Delete
        $(document).on('click', '.btn-delete', e =>
            this.deleteReview($(e.currentTarget).data('url'))
        );
    },

    /* ===============================
     * FILTERS
     =============================== */
    addRow() {
        const $row = this.$filterRows.find('.filter-row:first').clone();
        $row.find('select, input').val('');
        $row.find('.remove-row').removeClass('d-none');
        this.$filterRows.append($row);
        $row.find('.select2').select2();
    },

    clearFilters() {
        this.$filterForm[0].reset();
        this.$filterRows.find('.filter-row:not(:first)').remove();
        this.table.ajax.reload();
        this.$clearBtn.addClass('d-none');
    },

    handleFieldChange($select) {
        const field = $select.val();
        const $row  = $select.closest('.filter-row');
        const $wrap = $row.find('.value-wrapper');

        const inputs = {

            status: `
                <select name="value[]" class="form-select">
                    <option value="">Select Status</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                </select>`,

            rating: `
                <select name="value[]" class="form-select">
                    <option value="">Rating</option>
                    <option value="5">★★★★★</option>
                    <option value="4">★★★★</option>
                    <option value="3">★★★</option>
                    <option value="2">★★</option>
                    <option value="1">★</option>
                </select>`,

            is_verified_purchase: `
                <select name="value[]" class="form-select">
                    <option value="">Verified?</option>
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                </select>`,

            product_id: `
                <select name="value[]" class="form-select select2">
                    <option value="">Select Product</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}">{{ $product->title }}</option>
                    @endforeach
                </select>`,

            user_id: `
                <select name="value[]" class="form-select select2">
                    <option value="">Select User</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>`,

            created_at: `
                <input type="date" name="value[]" class="form-control">`
        };

        $wrap.html(inputs[field] || `
            <input type="text" name="value[]" class="form-control" placeholder="Value">
        `);
        $wrap.find('.select2').select2();
    },

    /* ===============================
     * BULK ACTIONS
     =============================== */
    handleBulk(action, status, url) {

        const ids = $('.bulk-checkbox:checked')
            .map((_, el) => el.value).get();

        if (!ids.length) {
            Swal.fire('Info', 'Select at least one review', 'info');
            return;
        }

        if (action === 'delete') {
            this.confirmDelete(ids, url);
            return;
        }

        if (action === 'status') {
            this.confirmBulkStatus(ids, status);
        }
    },

    confirmBulkStatus(ids, status) {
        Swal.fire({
            title: 'Change review status?',
            text: `Selected reviews will be marked as "${status}".`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes'
        }).then(res => {

            if (!res.isConfirmed) return;

            $.post('{{ route('product-reviews.bulk-status') }}', {
                ids, status, _token: '{{ csrf_token() }}'
            }).done(() => this.afterBulk('Status updated'));
        });
    },

    confirmDelete(ids, url) {
        Swal.fire({
            title: 'Delete reviews?',
            icon: 'error',
            showCancelButton: true,
            confirmButtonText: 'Delete'
        }).then(res => {

            if (!res.isConfirmed) return;

            $.post(url, {
                ids, _token: '{{ csrf_token() }}'
            }).done(() => this.afterBulk('Reviews deleted'));
        });
    },

    afterBulk(msg) {
        this.table.ajax.reload(null, false);
        this.$selectAll.prop('checked', false);

        Swal.fire({
            icon: 'success',
            title: msg,
            timer: 1500,
            showConfirmButton: false
        });
    },

    /* ===============================
     * ROW ACTIONS
     =============================== */
    viewReview(url) {
        $.get(url, res => {
            $('#vr-product').text(res.product);
            $('#vr-user').text(res.user);
            $('#vr-email').text(res.email);
            $('#vr-rating').html('⭐'.repeat(res.rating));
            $('#vr-title').text(res.title || '-');
            $('#vr-review').text(res.review || '-');
            $('#vr-status').text(res.status);
            $('#vr-verified').text(res.verified ? 'Yes' : 'No');
            $('#vr-ip').text(res.ip || '-');
            $('#vr-agent').text(res.agent || '-');
            $('#vr-date').text(res.created_at);

            $('#viewReviewModal').modal('show');
        });
    },

    changeStatus(url) {
        Swal.fire({
            title: 'Confirm action?',
            showCancelButton: true,
            confirmButtonText: 'Yes'
        }).then(res => {
            if (!res.isConfirmed) return;

            $.post(url, {_token: '{{ csrf_token() }}'})
                .done(() => {
                    this.table.ajax.reload(null, false);
                    Swal.fire('Success', 'Action completed', 'success');
                });
        });
    },

    deleteReview(url) {
        Swal.fire({
            title: 'Delete this review?',
            icon: 'error',
            showCancelButton: true,
            confirmButtonText: 'Delete'
        }).then(res => {
            if (!res.isConfirmed) return;

            $.ajax({
                url,
                type: 'DELETE',
                data: {_token: '{{ csrf_token() }}'},
                success: () => {
                    this.table.ajax.reload(null, false);
                    Swal.fire('Deleted', 'Review deleted', 'success');
                }
            });
        });
    }
};

$(document).ready(() => ReviewsPage.init());

})(jQuery);
</script>

@endpush
