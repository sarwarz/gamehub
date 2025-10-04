@extends('layouts.app')
@section('title', 'Users Management')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}" />
@endpush

@section('content')
<div class="app-ecommerce-user">
    @include('partials.alerts')

    <div class="card p-2">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Users</h5>
            <div>
                <button class="btn btn-danger" id="bulk-delete" data-url="{{ route('users.bulk-delete') }}">
                    <i class="menu-icon icon-base ti tabler-trash"></i> Delete Selected
                </button>
                <a href="{{ route('users.create') }}" class="btn btn-primary">
                    <i class="menu-icon icon-base ti tabler-plus"></i> Add User
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered" id="users-table">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="select-all" class="form-check-input"></th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Roles</th>
                        <th width="200">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
@endsection

@push('page-js')
<script>
let table = $('#users-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: '{{ route('users.index') }}',
    columns: [
        { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false },
        { data: 'name', name: 'name' },
        { data: 'email', name: 'email' },
        { data: 'roles', name: 'roles', orderable: false, searchable: false },
        { data: 'actions', name: 'actions', orderable: false, searchable: false }
    ]
});
$('#select-all').on('click', function () {
    $('.bulk-checkbox').prop('checked', this.checked);
});
</script>
@endpush
