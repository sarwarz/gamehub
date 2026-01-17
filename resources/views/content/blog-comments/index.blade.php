@extends('layouts.app')
@section('title', 'Blog Comments')

@section('content')
<div class="app-ecommerce">

<div class="card p-2">
    <div class="card-header">
        <h5 class="mb-0">Blog Comments</h5>
        <small class="text-muted">Moderate user comments</small>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered" id="comments-table">
            <thead>
                <tr>
                    <th>Blog</th>
                    <th>User</th>
                    <th>Comment</th>
                    <th>Status</th>
                    <th width="150">Actions</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

</div>
@endsection

@push('page-js')
<script>
let table = $('#comments-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: '{{ route('blog-comments.index') }}',
    columns: [
        { data: 'blog' },
        { data: 'user' },
        { data: 'comment' },
        { data: 'status' },
        { data: 'actions' }
    ]
});

/* Approve */
$(document).on('click', '.approve-btn', function () {
    $.ajax({
        url: "{{ url('dashboard/blog-comments') }}/" + $(this).data('id') + "/approve",
        type: 'PUT',
        data: { _token: '{{ csrf_token() }}' },
        success: () => table.ajax.reload(null,false)
    });
});

/* Delete */
$(document).on('click', '.delete-btn', function () {
    let id = $(this).data('id');

    if (!confirm('Delete comment?')) return;

    $.ajax({
        url: "{{ url('dashboard/blog-comments') }}/" + id,
        type: 'DELETE',
        data: { _token: '{{ csrf_token() }}' },
        success: () => table.ajax.reload(null,false)
    });
});
</script>
@endpush
