@extends('layouts.app')
@section('title', 'Products')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}">
<style>
    .bulk-bar { background:#f0f2ff; border-radius:8px; animation:bulkSlide .3s ease; }
    @keyframes bulkSlide { from { opacity:0; transform:translateY(-8px); } to { opacity:1; transform:translateY(0); } }
</style>
@endpush

@section('content')

    @include('partials.alerts')

    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1"><i class="ti tabler-package me-2"></i>Products</h4>
            <p class="text-muted mb-0">Manage your product catalog</p>
        </div>
        <a href="{{ route('products.create') }}" class="btn btn-primary">
            <i class="ti tabler-plus me-1"></i> Add Product
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3 bg-label-primary">
                            <i class="ti tabler-package fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ number_format($stats['total']) }}</h5>
                            <small class="text-muted">Total Products</small>
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
                            <h5 class="mb-0">{{ number_format($stats['active']) }}</h5>
                            <small class="text-muted">Active</small>
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
                            <i class="ti tabler-circle-off fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ number_format($stats['inactive']) }}</h5>
                            <small class="text-muted">Inactive</small>
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
                            <h5 class="mb-0">{{ number_format($stats['featured']) }}</h5>
                            <small class="text-muted">Featured</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- DataTable Card -->
    <div class="card">
        <!-- Card Header -->
        <div class="card-header pb-0">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <h5 class="mb-0"><i class="ti tabler-list-details me-2"></i>All Products</h5>
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-label-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#filter-collapse">
                        <i class="ti tabler-filter me-1"></i> Filters
                    </button>
                </div>
            </div>

            <!-- Collapsible Filter Row -->
            <div class="collapse mt-3" id="filter-collapse">
                <div class="row g-3 pb-3 border-bottom">
                    <div class="col-md-4">
                        <label class="form-label small text-muted">Status</label>
                        <select id="filter-status" class="form-select form-select-sm">
                            <option value="">All Statuses</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small text-muted">Featured</label>
                        <select id="filter-featured" class="form-select form-select-sm">
                            <option value="">All</option>
                            <option value="yes">Yes</option>
                            <option value="no">No</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small text-muted">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-primary" id="apply-filters">
                                <i class="ti tabler-check me-1"></i> Apply
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" id="clear-filters">
                                <i class="ti tabler-x me-1"></i> Clear
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bulk Actions Bar -->
        <div class="card-body py-0">
            <div class="bulk-bar d-none py-2 px-3 mt-3" id="bulk-bar">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary rounded-pill fs-6" id="bulk-count">0</span>
                        <span class="fw-medium" style="font-size:.85rem">products selected</span>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-label-primary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="ti tabler-refresh me-1"></i> Change Status
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item bulk-action" href="#" data-action="status" data-status="active"><i class="ti tabler-circle-check ti-xs me-2 text-success"></i> Active</a></li>
                                <li><a class="dropdown-item bulk-action" href="#" data-action="status" data-status="inactive"><i class="ti tabler-circle-off ti-xs me-2 text-warning"></i> Inactive</a></li>
                            </ul>
                        </div>
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-label-info dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="ti tabler-star me-1"></i> Toggle Featured
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item bulk-action" href="#" data-action="featured" data-value="1"><i class="ti tabler-star-filled ti-xs me-2 text-warning"></i> Mark Featured</a></li>
                                <li><a class="dropdown-item bulk-action" href="#" data-action="featured" data-value="0"><i class="ti tabler-star-off ti-xs me-2 text-muted"></i> Remove Featured</a></li>
                            </ul>
                        </div>
                        <button type="button" class="btn btn-sm btn-label-danger bulk-action" data-action="delete" data-url="{{ route('products.bulk-delete') }}">
                            <i class="ti tabler-trash me-1"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="table-responsive">
            <table class="table table-hover" id="products-table">
                <thead class="table-light">
                    <tr>
                        <th width="40"><input type="checkbox" class="form-check-input" id="select-all"></th>
                        <th>Product</th>
                        <th>Categories</th>
                        <th>Types</th>
                        <th>Regions</th>
                        <th class="text-center">Status</th>
                        <th class="text-center" width="120">Actions</th>
                    </tr>
                </thead>
            </table>
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
        },

        cacheDom() {
            this.$table     = $('#products-table');
            this.$selectAll = $('#select-all');
            this.$bulkBar   = $('#bulk-bar');
            this.$bulkCount = $('#bulk-count');
        },

        initDataTable() {
            this.table = this.$table.DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route("products.index") }}',
                    data: function (d) {
                        d.status   = $('#filter-status').val();
                        d.featured = $('#filter-featured').val();
                    }
                },
                order: [[1, 'asc']],
                lengthMenu: [10, 25, 50, 100],
                pageLength: 25,
                columns: [
                    { data: 'checkbox', orderable: false, searchable: false, className: 'pe-0' },
                    { data: 'product_column', name: 'title' },
                    { data: 'categories', orderable: false, searchable: false },
                    { data: 'types', orderable: false, searchable: false },
                    { data: 'regions', orderable: false, searchable: false },
                    { data: 'status_badge', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'actions', orderable: false, searchable: false, className: 'text-center' }
                ],
                language: {
                    emptyTable: '<div class="py-4 text-center"><i class="ti tabler-package-off ti-xl text-muted mb-2 d-block"></i><span class="text-muted">No products found</span></div>',
                    zeroRecords: '<div class="py-3 text-center text-muted">No matching products</div>'
                }
            });
        },

        bindEvents() {
            const self = this;

            $('#apply-filters').on('click', () => self.table.ajax.reload());

            $('#clear-filters').on('click', () => {
                $('#filter-status, #filter-featured').val('');
                self.table.ajax.reload();
            });

            this.$selectAll.on('change', function () {
                $('.bulk-checkbox').prop('checked', this.checked);
                self.syncBulkBar();
            });

            $(document).on('change', '.bulk-checkbox', () => self.syncBulkBar());

            $(document).on('click', '.bulk-action', function (e) {
                e.preventDefault();
                const $btn = $(this);
                self.handleBulkAction(
                    $btn.data('action'),
                    $btn.data('status') ?? $btn.data('value') ?? null,
                    $btn.data('url') ?? null
                );
            });

            $(document).on('click', '.btn-delete', function (e) {
                e.preventDefault();
                const url = $(this).data('url');
                Swal.fire({
                    title: 'Delete this product?',
                    text: 'This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete',
                    cancelButtonText: 'Cancel',
                    customClass: { confirmButton: 'btn btn-danger me-3', cancelButton: 'btn btn-label-secondary' },
                    buttonsStyling: false
                }).then(r => {
                    if (!r.isConfirmed) return;
                    $.ajax({
                        url: url,
                        type: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: () => {
                            self.table.ajax.reload(null, false);
                            Swal.fire({ icon: 'success', title: 'Product deleted', showConfirmButton: false, timer: 1500, timerProgressBar: true });
                        },
                        error: () => Swal.fire({ icon: 'error', title: 'Failed to delete product', showConfirmButton: false, timer: 1500 })
                    });
                });
            });
        },

        syncBulkBar() {
            const count = $('.bulk-checkbox:checked').length;
            this.$bulkCount.text(count);

            if (count > 0) {
                this.$bulkBar.removeClass('d-none');
            } else {
                this.$bulkBar.addClass('d-none');
            }

            const total = $('.bulk-checkbox').length;
            this.$selectAll.prop('checked', count > 0 && count === total);
        },

        handleBulkAction(action, value, url) {
            const ids = this.getSelectedIds();
            if (!ids.length) {
                Swal.fire({ icon: 'info', title: 'No Selection', text: 'Please select at least one product.', timer: 2000, showConfirmButton: false });
                return;
            }
            if (action === 'delete')   { this.confirmBulkDelete(ids, url); return; }
            if (action === 'status')   { this.confirmBulkStatus(ids, value); return; }
            if (action === 'featured') { this.confirmBulkFeatured(ids, value); return; }
        },

        getSelectedIds() {
            return $('.bulk-checkbox:checked').map((_, el) => el.value).get();
        },

        confirmBulkStatus(ids, status) {
            Swal.fire({
                title: 'Change Product Status?',
                html: `<span class="text-muted">Selected products will be marked as <strong>${status}</strong>.</span>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, change',
                cancelButtonText: 'Cancel',
                customClass: { confirmButton: 'btn btn-primary me-3', cancelButton: 'btn btn-label-secondary' },
                buttonsStyling: false
            }).then(r => {
                if (!r.isConfirmed) return;
                $.post('{{ route("products.bulk-status") }}', { ids, status, _token: '{{ csrf_token() }}' })
                    .done(() => {
                        this.afterBulkAction();
                        Swal.fire({ icon: 'success', title: 'Status updated', showConfirmButton: false, timer: 1500, timerProgressBar: true });
                    })
                    .fail(() => Swal.fire({ icon: 'error', title: 'Failed', text: 'Could not update status.', timer: 2000, showConfirmButton: false }));
            });
        },

        confirmBulkFeatured(ids, value) {
            const label = value == 1 ? 'featured' : 'unfeatured';
            Swal.fire({
                title: 'Update Featured?',
                html: `<span class="text-muted">Selected products will be marked as <strong>${label}</strong>.</span>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, update',
                cancelButtonText: 'Cancel',
                customClass: { confirmButton: 'btn btn-primary me-3', cancelButton: 'btn btn-label-secondary' },
                buttonsStyling: false
            }).then(r => {
                if (!r.isConfirmed) return;
                $.post('{{ route("products.bulk-featured") }}', { ids, value, _token: '{{ csrf_token() }}' })
                    .done(() => {
                        this.afterBulkAction();
                        Swal.fire({ icon: 'success', title: 'Featured updated', showConfirmButton: false, timer: 1500, timerProgressBar: true });
                    })
                    .fail(() => Swal.fire({ icon: 'error', title: 'Failed', text: 'Could not update featured.', timer: 2000, showConfirmButton: false }));
            });
        },

        confirmBulkDelete(ids, url) {
            Swal.fire({
                title: 'Delete Products?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete',
                cancelButtonText: 'Cancel',
                customClass: { confirmButton: 'btn btn-danger me-3', cancelButton: 'btn btn-label-secondary' },
                buttonsStyling: false
            }).then(r => {
                if (!r.isConfirmed) return;
                $.post(url, { ids, _token: '{{ csrf_token() }}' })
                    .done(() => {
                        this.afterBulkAction();
                        Swal.fire({ icon: 'success', title: 'Products deleted', showConfirmButton: false, timer: 1500, timerProgressBar: true });
                    })
                    .fail(() => Swal.fire({ icon: 'error', title: 'Failed', text: 'Could not delete products.', timer: 2000, showConfirmButton: false }));
            });
        },

        afterBulkAction() {
            this.table.ajax.reload(null, false);
            this.$selectAll.prop('checked', false);
            this.$bulkBar.addClass('d-none');
        }
    };

    $(document).ready(() => ProductsPage.init());

})(jQuery);
</script>
@endpush
