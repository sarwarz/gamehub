@extends('layouts.app')
@section('title', 'Blogs')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}">
@endpush

@section('content')
<div class="app-ecommerce">

    @include('partials.alerts')

    <div class="card p-2">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Blogs</h5>
            <a class="btn btn-primary" href="{{ route('blogs.create') }}">
                Add Blog
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered align-middle" id="blogs-table">
                <thead>
                    <tr>
                        <th width="40">
                            <input type="checkbox" id="select-all">
                        </th>
                        <th>Title</th>
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
let table = $('#blogs-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: '{{ route('blogs.index') }}',
    columns: [
        { data: 'checkbox', orderable: false, searchable: false },
        { data: 'title_column', name: 'title' },
        { data: 'category', orderable: false, searchable: false },
        { data: 'status', orderable: false, searchable: false },
        { data: 'actions', orderable: false, searchable: false }
    ],
    columnDefs: [
        { targets: [0,2,3,4], className: 'text-center' }
    ]
});

/* ============================
   SINGLE DELETE (SweetAlert)
============================ */
$(document).on('click', '.delete-btn', function () {
    let id = $(this).data('id');

    Swal.fire({
        title: 'Are you sure?',
        text: 'This blog post will be permanently deleted!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#7367f0',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ url('blogs') }}/" + id,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function () {
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: 'Blog post deleted successfully.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                    table.ajax.reload(null, false);
                },
                error: function (xhr) {
                    Swal.fire(
                        'Error!',
                        xhr.status === 403
                            ? 'You do not have permission.'
                            : 'Delete failed.',
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
