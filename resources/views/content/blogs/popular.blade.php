@extends('layouts.app')
@section('title', 'Popular Blogs')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}">
@endpush

@section('content')
<div class="app-ecommerce">

    @include('partials.alerts')

    <div class="card p-2">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Popular Blogs</h5>
                <small class="text-muted">
                    Blogs sorted by highest views
                </small>
            </div>
            <a class="btn btn-primary" href="{{ route('blogs.create') }}">
                Add Blog
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered align-middle" id="popular-blogs-table">
                <thead>
                    <tr>
                        <th width="40">
                            <input type="checkbox" id="select-all">
                        </th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Views</th>
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
let table = $('#popular-blogs-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: '{{ route('blogs.popular') }}',
    order: [[3, 'desc']], // views column
    columns: [
        { data: 'checkbox', orderable: false, searchable: false },
        { data: 'title_column', name: 'title' },
        { data: 'category', orderable: false, searchable: false },
        { data: 'views', name: 'views' },
        { data: 'status', orderable: false, searchable: false },
        { data: 'actions', orderable: false, searchable: false }
    ],
    columnDefs: [
        { targets: [0,2,3,4,5], className: 'text-center' }
    ]
});

/* SELECT ALL */
$('#select-all').on('click', function () {
    $('.row-checkbox').prop('checked', this.checked);
});
</script>
@endpush
