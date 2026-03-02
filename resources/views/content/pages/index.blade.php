@extends('layouts.app')
@section('title', 'Pages')

@push('page-css')
<style>
.bulk-bar { background:#f0f2ff; border-radius:8px; animation:bulkSlide .3s ease; }
@keyframes bulkSlide { from { opacity:0; transform:translateY(-8px); } to { opacity:1; transform:translateY(0); } }
</style>
@endpush

@section('content')

    {{-- Page Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1"><i class="ti tabler-file-text me-2"></i>Pages</h4>
            <p class="text-muted mb-0">Manage your CMS pages and content</p>
        </div>
        <a href="{{ route('pages.create') }}" class="btn btn-primary">
            <i class="ti tabler-plus me-1"></i> Add Page
        </a>
    </div>

    {{-- Stats --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3 bg-label-primary">
                            <i class="ti tabler-file-text fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ $stats['total'] }}</h5>
                            <small class="text-muted">Total Pages</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3 bg-label-success">
                            <i class="ti tabler-circle-check fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ $stats['published'] }}</h5>
                            <small class="text-muted">Active</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3 bg-label-warning">
                            <i class="ti tabler-pencil-pause fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ $stats['draft'] }}</h5>
                            <small class="text-muted">Inactive</small>
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
                <h5 class="mb-0"><i class="ti tabler-list me-2"></i>All Pages</h5>
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-label-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#filter-collapse" aria-expanded="false">
                        <i class="ti tabler-filter me-1"></i> Filters
                    </button>
                </div>
            </div>

            {{-- Collapsible Filter Row --}}
            <div class="collapse mt-3" id="filter-collapse">
                <div class="row g-3 pb-3 border-bottom">
                    <div class="col-md-4 col-sm-6">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="ti tabler-circle-dot ti-xs"></i></span>
                            <select class="form-select" id="filter-status">
                                <option value="">All Statuses</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6 d-flex gap-2">
                        <button class="btn btn-primary btn-sm flex-fill" id="btn-apply-filters">
                            <i class="ti tabler-search me-1"></i> Apply
                        </button>
                        <button class="btn btn-outline-secondary btn-sm flex-fill" id="btn-clear-filters">
                            <i class="ti tabler-filter-off me-1"></i> Clear
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bulk Actions Bar --}}
        <div class="card-body py-0">
            <div class="bulk-bar d-none py-2 px-3 my-2" id="bulk-bar">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary rounded-pill fs-6" id="bulk-count">0</span>
                        <span class="fw-medium" style="font-size:.85rem">pages selected</span>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <button class="btn btn-sm btn-label-danger" type="button" id="bulk-delete-btn">
                            <i class="ti tabler-trash me-1"></i> Delete Selected
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="table-responsive">
            <table id="pages-table" class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th width="40"><input type="checkbox" class="form-check-input" id="select-all"></th>
                        <th>Page</th>
                        <th>Menu</th>
                        <th>Status</th>
                        <th width="80">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

@endsection

@push('page-js')
<script>
$(function () {
    let table = $('#pages-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("pages.index") }}',
            data: function (d) {
                d.status = $('#filter-status').val();
            }
        },
        columns: [
            { data: 'checkbox', orderable: false, searchable: false },
            { data: 'title_column', name: 'title' },
            { data: 'menu', orderable: false, searchable: false },
            { data: 'status', orderable: false, searchable: false },
            { data: 'actions', orderable: false, searchable: false }
        ],
        columnDefs: [
            { targets: [0, 2, 3, 4], className: 'text-center' }
        ]
    });

    // Filters
    $('#btn-apply-filters').on('click', function () { table.ajax.reload(); });
    $('#btn-clear-filters').on('click', function () {
        $('#filter-status').val('');
        table.ajax.reload();
    });

    // Select All
    $('#select-all').on('change', function () {
        $('.row-checkbox').prop('checked', this.checked);
        toggleBulkBar();
    });
    $(document).on('change', '.row-checkbox', toggleBulkBar);

    function toggleBulkBar() {
        let count = $('.row-checkbox:checked').length;
        $('#bulk-count').text(count);
        $('#bulk-bar').toggleClass('d-none', count === 0);
    }

    // Bulk Delete
    $('#bulk-delete-btn').on('click', function () {
        let ids = $('.row-checkbox:checked').map(function () { return $(this).val(); }).get();
        if (!ids.length) return;

        Swal.fire({
            title: 'Delete ' + ids.length + ' page(s)?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#7367f0',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete!'
        }).then(function (result) {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("pages.bulk-delete") }}',
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}', ids: ids },
                    success: function () {
                        Swal.fire({ icon: 'success', title: 'Deleted!', timer: 1500, showConfirmButton: false });
                        table.ajax.reload(null, false);
                        $('#select-all').prop('checked', false);
                        toggleBulkBar();
                    }
                });
            }
        });
    });

    // Single Delete
    $(document).on('click', '.delete-btn', function () {
        let id = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: 'This page will be permanently deleted!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#7367f0',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then(function (result) {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ url('dashboard/pages') }}/" + id,
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function () {
                        Swal.fire({ icon: 'success', title: 'Deleted!', text: 'Page deleted successfully.', timer: 1500, showConfirmButton: false });
                        table.ajax.reload(null, false);
                    },
                    error: function (xhr) {
                        Swal.fire('Error!', xhr.status === 403 ? 'You do not have permission.' : 'Delete failed.', 'error');
                    }
                });
            }
        });
    });
});
</script>
@endpush
