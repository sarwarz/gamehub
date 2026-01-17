@extends('layouts.app')
@section('title', 'Blog Categories')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}">
@endpush

@section('content')
<div class="app-ecommerce">

    @include('partials.alerts')

    <div class="card p-2">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Blog Categories</h5>
            <div>
                <button class="btn btn-danger" id="bulk-delete"
                        data-url="{{ route('blog-categories.bulk-delete') }}">
                    Delete Selected
                </button>
                <a class="btn btn-primary" href="{{ route('blog-categories.create') }}">
                    Add Category
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered align-middle" id="categories-table">
                <thead>
                    <tr>
                        <th width="40"><input type="checkbox" id="select-all"></th>
                        <th>Category</th>
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
let table = $('#categories-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: '{{ route('blog-categories.index') }}',
    columns: [
        { data: 'checkbox', orderable: false, searchable: false },
        { data: 'name_column', name: 'name' },
        { data: 'status', orderable: false, searchable: false },
        { data: 'actions', orderable: false, searchable: false }
    ],
    columnDefs: [
        { targets: [0,2,3], className: 'text-center' }
    ]
});

/* ============================
   SINGLE DELETE (SweetAlert)
============================ */
$(document).on('click', '.delete-btn', function () {
    let id = $(this).data('id');

    Swal.fire({
        title: 'Are you sure?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ route('blog-categories.destroy', ':id') }}".replace(':id', id),
                type: 'POST',
                data: {
                    _method: 'DELETE',
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function () {
                    Swal.fire('Deleted!', '', 'success');
                    table.ajax.reload(null, false);
                },
                error: function (xhr) {
                    Swal.fire(
                        'Error',
                        xhr.status === 403
                            ? 'Permission denied'
                            : 'Delete failed',
                        'error'
                    );
                }
            });
        }
    });
});


/* ============================
   BULK DELETE (SweetAlert)
============================ */
$('#bulk-delete').on('click', function () {
    let ids = $('.row-checkbox:checked').map(function () {
        return $(this).val();
    }).get();

    if (!ids.length) {
        Swal.fire({
            icon: 'info',
            title: 'No selection',
            text: 'Please select at least one category.'
        });
        return;
    }

    Swal.fire({
        title: 'Delete selected categories?',
        text: 'This action cannot be undone!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#7367f0',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete all!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: $('#bulk-delete').data('url'),
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}',
                    ids: ids
                },
                success: function () {
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: 'Selected categories deleted.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                    table.ajax.reload(null, false);
                },
                error: function () {
                    Swal.fire(
                        'Error!',
                        'Bulk delete failed.',
                        'error'
                    );
                }
            });
        }
    });
});

/* ============================
   SELECT ALL
============================ */
$('#select-all').on('click', function () {
    $('.row-checkbox').prop('checked', this.checked);
});
</script>
@endpush

