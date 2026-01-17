@extends('layouts.app')
@section('title', 'Pages')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}">
@endpush

@section('content')
<div class="app-ecommerce">

    @include('partials.alerts')

    <div class="card p-2">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Pages</h5>
            <div>
                <button class="btn btn-danger" id="bulk-delete"
                        data-url="{{ route('pages.bulk-delete') }}">
                    Delete Selected
                </button>
                <a class="btn btn-primary" href="{{ route('pages.create') }}">
                    Add Page
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered align-middle" id="pages-table">
                <thead>
                    <tr>
                        <th width="40">
                            <input type="checkbox" id="select-all">
                        </th>
                        <th>Page</th>
                        <th>Menu</th>
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
$('#pages-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: '{{ route('pages.index') }}',
    columns: [
        { data: 'checkbox', orderable: false, searchable: false },
        { data: 'title_column', name: 'title' },
        { data: 'menu', orderable: false, searchable: false },
        { data: 'status', orderable: false, searchable: false },
        { data: 'actions', orderable: false, searchable: false }
    ],
    columnDefs: [
        { targets: [0,2,3,4], className: 'text-center' }
    ]
});

$('#select-all').on('click', function () {
    $('.row-checkbox').prop('checked', this.checked);
});
</script>
@endpush
