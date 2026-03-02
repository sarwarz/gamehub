@extends('layouts.app')
@section('title', 'Product Regions')

@push('page-css')
<style>
    .bulk-bar { background:#f0f2ff; border-radius:8px; animation:bulkSlide .3s ease; }
    @keyframes bulkSlide { from { opacity:0; transform:translateY(-8px); } to { opacity:1; transform:translateY(0); } }
</style>
@endpush

@section('content')
<div class="app-ecommerce-regions">

    @include('partials.alerts')

    {{-- Page Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="ti tabler-map-pin ti-md me-1 text-primary"></i> Product Regions</h4>
            <p class="text-muted mb-0">Manage product availability regions</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRegionList">
            <i class="ti tabler-plus ti-xs me-1"></i> Add Region
        </button>
    </div>

    {{-- Stats Card --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3 bg-label-primary">
                            <i class="ti tabler-map-pin fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ number_format($stats['total']) }}</h5>
                            <small class="text-muted">Total Regions</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Bulk Action Bar --}}
    <div class="bulk-bar p-3 mb-3 d-none" id="bulk-bar">
        <div class="d-flex align-items-center justify-content-between">
            <span class="fw-semibold"><span id="bulk-count">0</span> region(s) selected</span>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-danger" id="bulk-delete-btn">
                    <i class="ti tabler-trash ti-xs me-1"></i> Delete Selected
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="bulk-cancel-btn">Cancel</button>
            </div>
        </div>
    </div>

    {{-- Main Card --}}
    <div class="card shadow-sm">
        <div class="card-header border-bottom">
            <div class="d-flex flex-wrap justify-content-between align-items-center row-gap-2">
                <h5 class="card-title mb-0">All Regions</h5>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="regions-table" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <th width="30"><input type="checkbox" class="form-check-input" id="select-all"></th>
                        <th>Name</th>
                        <th>Slug</th>
                        <th class="text-center">Status</th>
                        <th class="text-center" width="100">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <!-- Offcanvas Add -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRegionList">
        <div class="offcanvas-header py-6">
            <h5 class="offcanvas-title">Add Product Region</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body border-top">
            <form method="POST" action="{{ route('regions.store') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Region Name</label>
                    <input type="text" name="name" class="form-control" placeholder="Enter region name" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Slug</label>
                    <input type="text" name="slug" class="form-control" placeholder="Enter slug" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <div>
                    <button type="submit" class="btn btn-primary">Save</button>
                    <button type="reset" class="btn btn-label-danger" data-bs-dismiss="offcanvas">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('page-js')
<script>
(function ($) {
    'use strict';

    const Page = {
        table: null,

        init() {
            this.initDataTable();
            this.bindEvents();
        },

        initDataTable() {
            this.table = $('#regions-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('regions.index') }}',
                order: [[1, 'asc']],
                pageLength: 25,
                columns: [
                    { data: 'checkbox', orderable: false, searchable: false, className: 'pe-0' },
                    { data: 'name', name: 'name' },
                    { data: 'slug', name: 'slug' },
                    { data: 'status_badge', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'actions', orderable: false, searchable: false, className: 'text-center' }
                ],
                language: {
                    emptyTable: '<div class="py-4 text-center"><i class="ti tabler-map-pin ti-xl text-muted mb-2 d-block"></i><span class="text-muted">No regions found</span></div>'
                }
            });
        },

        bindEvents() {
            $('#select-all').on('change', e => {
                $('.bulk-checkbox').prop('checked', e.target.checked);
                this.syncBulkBar();
            });

            $(document).on('change', '.bulk-checkbox', () => this.syncBulkBar());

            $('#bulk-cancel-btn').on('click', () => {
                $('#select-all').prop('checked', false);
                $('.bulk-checkbox').prop('checked', false);
                this.syncBulkBar();
            });

            $('#bulk-delete-btn').on('click', () => this.bulkDelete());

            $(document).on('click', '.btn-delete', e => {
                e.preventDefault();
                const url = $(e.currentTarget).data('url');
                Swal.fire({
                    title: 'Delete Region?',
                    text: 'This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete',
                    customClass: { confirmButton: 'btn btn-danger me-3', cancelButton: 'btn btn-label-secondary' },
                    buttonsStyling: false
                }).then(r => {
                    if (!r.isConfirmed) return;
                    $.ajax({
                        url, type: 'DELETE', data: { _token: '{{ csrf_token() }}' },
                        success: () => {
                            this.table.ajax.reload(null, false);
                            Swal.fire({ icon: 'success', title: 'Region deleted', showConfirmButton: false, timer: 1500, timerProgressBar: true });
                        },
                        error: () => Swal.fire({ icon: 'error', title: 'Failed', timer: 1500, showConfirmButton: false })
                    });
                });
            });
        },

        syncBulkBar() {
            const count = $('.bulk-checkbox:checked').length;
            $('#bulk-count').text(count);
            $('#bulk-bar').toggleClass('d-none', count === 0);
        },

        bulkDelete() {
            const ids = $('.bulk-checkbox:checked').map((_, el) => el.value).get();
            if (!ids.length) return;

            Swal.fire({
                title: 'Delete Regions?',
                text: `${ids.length} region(s) will be permanently deleted.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete',
                customClass: { confirmButton: 'btn btn-danger me-3', cancelButton: 'btn btn-label-secondary' },
                buttonsStyling: false
            }).then(r => {
                if (!r.isConfirmed) return;
                $.post('{{ route('regions.bulk-delete') }}', { ids, _token: '{{ csrf_token() }}' })
                    .done(() => {
                        this.table.ajax.reload(null, false);
                        $('#select-all').prop('checked', false);
                        this.syncBulkBar();
                        Swal.fire({ icon: 'success', title: 'Regions deleted', showConfirmButton: false, timer: 1500, timerProgressBar: true });
                    })
                    .fail(() => Swal.fire({ icon: 'error', title: 'Failed', timer: 1500, showConfirmButton: false }));
            });
        }
    };

    $(document).ready(() => Page.init());

})(jQuery);
</script>
@endpush
