@extends('layouts.app')

@section('title', 'Blog Categories')

@push('page-css')
<style>
.category-stats .avatar {
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
            <h4 class="mb-1"><i class="ti tabler-category me-2"></i>Blog Categories</h4>
            <p class="text-muted mb-0">Manage blog post categories</p>
        </div>
        <a class="btn btn-primary" href="{{ route('blog-categories.create') }}">
            <i class="ti tabler-plus me-1"></i> Add Category
        </a>
    </div>

    {{-- Stats --}}
    <div class="row mb-4 category-stats">
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3 bg-label-primary">
                            <i class="ti tabler-category fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ $stats['total'] }}</h5>
                            <small class="text-muted">Total Categories</small>
                        </div>
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
            <button class="btn btn-sm btn-danger btn-bulk-delete" type="button">
                <i class="ti tabler-trash me-1"></i> Delete Selected
            </button>
        </div>
    </div>

    {{-- DataTable Card --}}
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
            <h5 class="mb-0"><i class="ti tabler-list me-2"></i>All Categories</h5>
        </div>
        <div class="table-responsive">
            <table id="categories-table" class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th width="40"><input type="checkbox" class="form-check-input" id="select-all"></th>
                        <th>Category</th>
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

    const table = $('#categories-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("blog-categories.index") }}',
        columns: [
            { data: 'checkbox', orderable: false, searchable: false },
            { data: 'name_column', name: 'name' },
            { data: 'status', orderable: false, searchable: false },
            { data: 'actions', orderable: false, searchable: false }
        ],
        columnDefs: [
            { targets: [0, 2, 3], className: 'text-center' }
        ],
        pageLength: 15,
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

    // Single Delete
    $(document).on('click', '.delete-btn', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Delete Category?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Delete',
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('blog-categories.destroy', ':id') }}".replace(':id', id),
                    type: 'POST',
                    data: { _method: 'DELETE', _token: csrfToken },
                    success: function() {
                        table.ajax.reload(null, false);
                        Swal.fire({ icon: 'success', title: 'Deleted!', timer: 1500, showConfirmButton: false });
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.status === 403 ? 'Permission denied' : 'Delete failed', 'error');
                    }
                });
            }
        });
    });

    // Bulk Delete
    $('.btn-bulk-delete').on('click', function() {
        const ids = $('.row-checkbox:checked').map(function() { return $(this).val(); }).get();
        if (!ids.length) return;

        Swal.fire({
            title: `Delete ${ids.length} categories?`,
            text: 'This action cannot be undone!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, delete all!',
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("blog-categories.bulk-delete") }}',
                    type: 'DELETE',
                    data: { ids, _token: csrfToken },
                    success: function() {
                        table.ajax.reload(null, false);
                        updateBulk();
                        $('#select-all').prop('checked', false);
                        Swal.fire({ icon: 'success', title: 'Deleted!', text: 'Selected categories deleted.', timer: 1500, showConfirmButton: false });
                    },
                    error: function() {
                        Swal.fire('Error!', 'Bulk delete failed.', 'error');
                    }
                });
            }
        });
    });
});
</script>
@endpush
