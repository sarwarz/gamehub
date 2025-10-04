@extends('layouts.app')
@section('title', 'Roles Management')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}" />
@endpush

@section('content')
<div class="app-ecommerce-role">

    @include('partials.alerts')

    <!-- Role List Table -->
    <div class="card p-2">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Roles</h5>
            <div>
                <button class="btn btn-danger" id="bulk-delete" data-url="{{ route('roles.bulk-delete') }}">
                    <i class="menu-icon icon-base ti tabler-trash"></i> Delete Selected
                </button>

                <!-- ✅ Link to separate create page -->
                <a href="{{ route('roles.create') }}" class="btn btn-primary">
                    <i class="menu-icon icon-base ti tabler-plus"></i> Add Role
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered" id="roles-table">
                <thead>
                    <tr>
                        <th><input type="checkbox" class="form-check-input" id="select-all"></th>
                        <th>Name</th>
                        <th>Label</th>
                        <th>Permissions</th>
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
let table = $('#roles-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: '{{ route('roles.index') }}',
    columns: [
        { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false },
        { data: 'name', name: 'name' },
        { data: 'label', name: 'label' },
        { data: 'permissions', name: 'permissions', orderable: false, searchable: false },
        { data: 'actions', name: 'actions', orderable: false, searchable: false }
    ]
});

// Select/Deselect all checkboxes
$('#select-all').on('click', function () {
    $('.bulk-checkbox').prop('checked', this.checked);
});
</script>
@endpush
