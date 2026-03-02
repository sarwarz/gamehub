@extends('layouts.app')

@section('title', 'Blog Comments')

@push('page-css')
<style>
.comment-stats .avatar {
    display: flex;
    align-items: center;
    justify-content: center;
}
.bulk-bar {
    background: #f0f2ff;
    border-radius: 8px;
    animation: bulkSlide .3s ease;
}
@keyframes bulkSlide {
    from { opacity: 0; transform: translateY(-8px); }
    to   { opacity: 1; transform: translateY(0); }
}
</style>
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

    {{-- Page Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1"><i class="ti tabler-message-2 me-2"></i>Blog Comments</h4>
            <p class="text-muted mb-0">Moderate user comments on blog posts</p>
        </div>
    </div>

    {{-- Stats --}}
    <div class="row mb-4 comment-stats">
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3 bg-label-primary">
                            <i class="ti tabler-messages fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ $stats['total'] }}</h5>
                            <small class="text-muted">Total Comments</small>
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
                            <i class="ti tabler-message-check fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ $stats['approved'] }}</h5>
                            <small class="text-muted">Approved</small>
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
                            <i class="ti tabler-clock-pause fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ $stats['pending'] }}</h5>
                            <small class="text-muted">Pending</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Collapsible Filter --}}
    <div class="card mb-3">
        <div class="card-header py-2">
            <a class="d-flex align-items-center" data-bs-toggle="collapse" href="#filterCollapse" role="button" aria-expanded="false">
                <i class="ti tabler-filter me-2"></i>
                <span class="fw-semibold">Filters</span>
                <i class="ti tabler-chevron-down ms-auto"></i>
            </a>
        </div>
        <div class="collapse" id="filterCollapse">
            <div class="card-body pt-0">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select class="form-select" id="filter-status">
                            <option value="">All</option>
                            <option value="approved">Approved</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button class="btn btn-outline-secondary btn-sm" id="btn-reset-filters">
                            <i class="ti tabler-rotate me-1"></i> Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Bulk Action Bar --}}
    <div class="bulk-bar d-none p-3 mb-3 d-flex align-items-center justify-content-between" id="bulk-bar">
        <span class="fw-semibold text-primary">
            <i class="ti tabler-check me-1"></i>
            <span class="bulk-count">0</span> selected
        </span>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-success btn-bulk-approve" type="button">
                <i class="ti tabler-check me-1"></i> Approve Selected
            </button>
            <button class="btn btn-sm btn-danger btn-bulk-delete" type="button">
                <i class="ti tabler-trash me-1"></i> Delete Selected
            </button>
        </div>
    </div>

    {{-- DataTable Card --}}
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
            <h5 class="mb-0"><i class="ti tabler-list me-2"></i>All Comments</h5>
        </div>
        <div class="table-responsive">
            <table id="comments-table" class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th width="40"><input type="checkbox" class="form-check-input" id="select-all"></th>
                        <th>Blog</th>
                        <th>User</th>
                        <th>Comment</th>
                        <th>Status</th>
                        <th width="80">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
@endsection

@push('page-js')
<script>
$(function() {
    const csrfToken = '{{ csrf_token() }}';

    const table = $('#comments-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("blog-comments.index") }}',
            data: function(d) {
                d.status = $('#filter-status').val();
            }
        },
        columns: [
            { data: 'checkbox', orderable: false, searchable: false },
            { data: 'blog', name: 'blog.title' },
            { data: 'user', name: 'user.name' },
            { data: 'comment', name: 'comment' },
            { data: 'status', orderable: false, searchable: false },
            { data: 'actions', orderable: false, searchable: false }
        ],
        columnDefs: [
            { targets: [0, 4, 5], className: 'text-center' }
        ],
        pageLength: 15,
    });

    // Filter change
    $('#filter-status').on('change', function() {
        table.ajax.reload();
    });
    $('#btn-reset-filters').on('click', function() {
        $('#filter-status').val('');
        table.ajax.reload();
    });

    // Select all
    $('#select-all').on('change', function() {
        $('.row-checkbox').prop('checked', this.checked);
        updateBulk();
    });
    $(document).on('change', '.row-checkbox', updateBulk);

    function updateBulk() {
        const count = $('.row-checkbox:checked').length;
        if (count > 0) {
            $('#bulk-bar').removeClass('d-none').find('.bulk-count').text(count);
        } else {
            $('#bulk-bar').addClass('d-none');
        }
    }

    // Single Approve
    $(document).on('click', '.approve-btn', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Approve Comment?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Approve',
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ url("dashboard/blog-comments") }}/' + id + '/approve',
                    type: 'PUT',
                    data: { _token: csrfToken },
                    success: function() {
                        table.ajax.reload(null, false);
                        Swal.fire({ icon: 'success', title: 'Approved!', timer: 1500, showConfirmButton: false });
                    }
                });
            }
        });
    });

    // Single Delete
    $(document).on('click', '.delete-btn', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Delete Comment?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Delete',
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ url("dashboard/blog-comments") }}/' + id,
                    type: 'DELETE',
                    data: { _token: csrfToken },
                    success: function() {
                        table.ajax.reload(null, false);
                        Swal.fire({ icon: 'success', title: 'Deleted!', timer: 1500, showConfirmButton: false });
                    }
                });
            }
        });
    });

    // Bulk Approve
    $('.btn-bulk-approve').on('click', function() {
        const ids = $('.row-checkbox:checked').map(function() { return $(this).val(); }).get();
        if (!ids.length) return;

        Swal.fire({
            title: `Approve ${ids.length} comments?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Approve All',
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('{{ route("blog-comments.bulk-approve") }}', { ids, _token: csrfToken }, function() {
                    table.ajax.reload(null, false);
                    updateBulk();
                    $('#select-all').prop('checked', false);
                    Swal.fire({ icon: 'success', title: 'Comments approved!', timer: 1500, showConfirmButton: false });
                });
            }
        });
    });

    // Bulk Delete
    $('.btn-bulk-delete').on('click', function() {
        const ids = $('.row-checkbox:checked').map(function() { return $(this).val(); }).get();
        if (!ids.length) return;

        Swal.fire({
            title: `Delete ${ids.length} comments?`,
            text: 'This action cannot be undone!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Delete All',
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('{{ route("blog-comments.bulk-delete") }}', { ids, _token: csrfToken }, function() {
                    table.ajax.reload(null, false);
                    updateBulk();
                    $('#select-all').prop('checked', false);
                    Swal.fire({ icon: 'success', title: 'Comments deleted!', timer: 1500, showConfirmButton: false });
                });
            }
        });
    });
});
</script>
@endpush
