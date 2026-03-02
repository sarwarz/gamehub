@extends('layouts.app')
@section('title', 'Product Reviews')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}">
<style>
    .bulk-bar { background:#f0f2ff; border-radius:8px; animation:bulkSlide .3s ease; }
    @keyframes bulkSlide { from { opacity:0; transform:translateY(-8px); } to { opacity:1; transform:translateY(0); } }
</style>
@endpush

@section('content')

    @include('partials.alerts')

    {{-- Page Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1"><i class="ti tabler-star me-2"></i>Product Reviews</h4>
            <p class="text-muted mb-0">Manage and moderate customer product reviews</p>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3 bg-label-primary">
                            <i class="ti tabler-message-2 fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ number_format($stats['total']) }}</h5>
                            <small class="text-muted">Total Reviews</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3 bg-label-success">
                            <i class="ti tabler-circle-check fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ number_format($stats['approved']) }}</h5>
                            <small class="text-muted">Approved</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3 bg-label-warning">
                            <i class="ti tabler-clock fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ number_format($stats['pending']) }}</h5>
                            <small class="text-muted">Pending</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3 bg-label-info">
                            <i class="ti tabler-star fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ $stats['avg'] }} <i class="ti tabler-star-filled text-warning" style="font-size:.85rem"></i></h5>
                            <small class="text-muted">Avg Rating</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- DataTable Card --}}
    <div class="card">
        {{-- Card Header --}}
        <div class="card-header pb-0">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <h5 class="mb-0"><i class="ti tabler-list-details me-2"></i>All Reviews</h5>
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-label-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#filter-collapse" aria-expanded="false">
                        <i class="ti tabler-filter me-1"></i> Filters
                    </button>
                </div>
            </div>

            {{-- Collapsible Filter Panel --}}
            <div class="collapse mt-3" id="filter-collapse">
                <form id="filterForm">
                    <div id="filterRows">
                        <div class="row g-2 align-items-center filter-row mb-2">
                            <div class="col-md-6">
                                <select name="field[]" class="form-select form-select-sm">
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
                                <select name="operator[]" class="form-select form-select-sm">
                                    <option value="=">Is equal to</option>
                                    <option value="like">Contains</option>
                                    <option value=">">Greater than</option>
                                    <option value="<">Less than</option>
                                </select>
                            </div>
                            <div class="col-md-11 value-wrapper">
                                <input type="text" name="value[]" class="form-control form-control-sm" placeholder="Value">
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-sm btn-outline-danger remove-row d-none">
                                    <i class="ti tabler-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-2 pb-3 border-bottom">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="addFilter">
                            <i class="ti tabler-plus me-1"></i> Add filter
                        </button>
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="ti tabler-check me-1"></i> Apply
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger d-none" id="clearFilters">
                            <i class="ti tabler-x me-1"></i> Clear
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Bulk Actions Bar --}}
        <div class="card-body py-0">
            <div class="bulk-bar d-none py-2 px-3 mt-3" id="bulk-bar">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary rounded-pill fs-6" id="bulk-count">0</span>
                        <span class="fw-medium" style="font-size:.85rem">reviews selected</span>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-sm btn-label-success bulk-action" data-action="status" data-status="approved">
                            <i class="ti tabler-check me-1"></i> Approve
                        </button>
                        <button type="button" class="btn btn-sm btn-label-warning bulk-action" data-action="status" data-status="rejected">
                            <i class="ti tabler-x me-1"></i> Reject
                        </button>
                        <button type="button" class="btn btn-sm btn-label-danger bulk-action" data-action="delete" data-url="{{ route('product-reviews.bulk-delete') }}">
                            <i class="ti tabler-trash me-1"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="product-reviews-table">
                <thead class="table-light">
                    <tr>
                        <th width="40"><input type="checkbox" id="select-all" class="form-check-input"></th>
                        <th>Review</th>
                        <th>IP</th>
                        <th class="text-center">Verified</th>
                        <th class="text-center">Status</th>
                        <th width="80" class="text-center">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    {{-- View Review Modal --}}
    <div class="modal fade" id="viewReviewModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ti tabler-eye me-2"></i>Review Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-sm table-bordered">
                        <tr><th width="140">Product</th><td id="vr-product"></td></tr>
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
        this.cacheDom();
        this.initTable();
        this.bindEvents();
    },

    cacheDom() {
        this.$table      = $('#product-reviews-table');
        this.$filterForm = $('#filterForm');
        this.$filterRows = $('#filterRows');
        this.$addBtn     = $('#addFilter');
        this.$clearBtn   = $('#clearFilters');
        this.$selectAll  = $('#select-all');
        this.$bulkBar    = $('#bulk-bar');
        this.$bulkCount  = $('#bulk-count');
    },

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
                { data: 'checkbox', orderable: false, searchable: false, className: 'pe-0' },
                { data: 'review_info', orderable: false },
                { data: 'ip_address', orderable: false },
                { data: 'verified', orderable: false, className: 'text-center' },
                { data: 'status_badge', orderable: false, className: 'text-center' },
                { data: 'actions', orderable: false, className: 'text-center' }
            ],
            language: {
                emptyTable: '<div class="py-4 text-center"><i class="ti tabler-star-off ti-xl text-muted mb-2 d-block"></i><span class="text-muted">No reviews found</span></div>',
                zeroRecords: '<div class="py-3 text-center text-muted">No matching reviews</div>'
            }
        });
    },

    bindEvents() {
        const self = this;

        this.$filterForm.on('submit', e => {
            e.preventDefault();
            self.table.ajax.reload();
            self.$clearBtn.removeClass('d-none');
        });

        this.$clearBtn.on('click', () => self.clearFilters());
        this.$addBtn.on('click', () => self.addRow());

        $(document).on('click', '.remove-row', e =>
            $(e.currentTarget).closest('.filter-row').remove()
        );

        $(document).on('change', 'select[name="field[]"]', e =>
            self.handleFieldChange($(e.currentTarget))
        );

        this.$selectAll.on('change', function () {
            $('.bulk-checkbox').prop('checked', this.checked);
            self.syncBulkBar();
        });

        $(document).on('change', '.bulk-checkbox', () => self.syncBulkBar());

        $(document).on('click', '.bulk-action', function (e) {
            e.preventDefault();
            const $btn = $(this);
            self.handleBulk($btn.data('action'), $btn.data('status') ?? null, $btn.data('url') ?? null);
        });

        $(document).on('click', '.btn-view', e =>
            self.viewReview($(e.currentTarget).data('url'))
        );

        $(document).on('click', '.btn-approve', e =>
            self.changeStatus($(e.currentTarget).data('url'), 'approve')
        );

        $(document).on('click', '.btn-reject', e =>
            self.changeStatus($(e.currentTarget).data('url'), 'reject')
        );

        $(document).on('click', '.btn-delete', e =>
            self.deleteReview($(e.currentTarget).data('url'))
        );
    },

    syncBulkBar() {
        const count = $('.bulk-checkbox:checked').length;
        this.$bulkCount.text(count);
        count > 0 ? this.$bulkBar.removeClass('d-none') : this.$bulkBar.addClass('d-none');

        const total = $('.bulk-checkbox').length;
        this.$selectAll.prop('checked', count > 0 && count === total);
    },

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
            status: `<select name="value[]" class="form-select form-select-sm">
                <option value="">Select Status</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
            </select>`,

            rating: `<select name="value[]" class="form-select form-select-sm">
                <option value="">Rating</option>
                <option value="5">★★★★★</option>
                <option value="4">★★★★</option>
                <option value="3">★★★</option>
                <option value="2">★★</option>
                <option value="1">★</option>
            </select>`,

            is_verified_purchase: `<select name="value[]" class="form-select form-select-sm">
                <option value="">Verified?</option>
                <option value="1">Yes</option>
                <option value="0">No</option>
            </select>`,

            product_id: `<select name="value[]" class="form-select form-select-sm select2">
                <option value="">Select Product</option>
                @foreach($products as $product)
                    <option value="{{ $product->id }}">{{ $product->title }}</option>
                @endforeach
            </select>`,

            user_id: `<select name="value[]" class="form-select form-select-sm select2">
                <option value="">Select User</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </select>`,

            created_at: `<input type="date" name="value[]" class="form-control form-control-sm">`
        };

        $wrap.html(inputs[field] || `<input type="text" name="value[]" class="form-control form-control-sm" placeholder="Value">`);
        $wrap.find('.select2').select2();
    },

    handleBulk(action, status, url) {
        const ids = $('.bulk-checkbox:checked').map((_, el) => el.value).get();
        if (!ids.length) {
            Swal.fire({ icon: 'info', title: 'No Selection', text: 'Select at least one review.', timer: 2000, showConfirmButton: false });
            return;
        }
        if (action === 'delete') { this.confirmDelete(ids, url); return; }
        if (action === 'status') { this.confirmBulkStatus(ids, status); }
    },

    confirmBulkStatus(ids, status) {
        Swal.fire({
            title: 'Change review status?',
            html: `<span class="text-muted">Selected reviews will be marked as <strong>${status}</strong>.</span>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, change',
            customClass: { confirmButton: 'btn btn-primary me-3', cancelButton: 'btn btn-label-secondary' },
            buttonsStyling: false
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
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete',
            customClass: { confirmButton: 'btn btn-danger me-3', cancelButton: 'btn btn-label-secondary' },
            buttonsStyling: false
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
        this.$bulkBar.addClass('d-none');
        Swal.fire({ icon: 'success', title: msg, showConfirmButton: false, timer: 1500, timerProgressBar: true });
    },

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
            confirmButtonText: 'Yes',
            customClass: { confirmButton: 'btn btn-primary me-3', cancelButton: 'btn btn-label-secondary' },
            buttonsStyling: false
        }).then(res => {
            if (!res.isConfirmed) return;
            $.post(url, { _token: '{{ csrf_token() }}' })
                .done(() => {
                    this.table.ajax.reload(null, false);
                    Swal.fire({ icon: 'success', title: 'Action completed', showConfirmButton: false, timer: 1500, timerProgressBar: true });
                });
        });
    },

    deleteReview(url) {
        Swal.fire({
            title: 'Delete this review?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete',
            customClass: { confirmButton: 'btn btn-danger me-3', cancelButton: 'btn btn-label-secondary' },
            buttonsStyling: false
        }).then(res => {
            if (!res.isConfirmed) return;
            $.ajax({
                url,
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: () => {
                    this.table.ajax.reload(null, false);
                    Swal.fire({ icon: 'success', title: 'Review deleted', showConfirmButton: false, timer: 1500, timerProgressBar: true });
                }
            });
        });
    }
};

$(document).ready(() => ReviewsPage.init());

})(jQuery);
</script>
@endpush
