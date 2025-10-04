@extends('layouts.app')
@section('title', 'Permissions Management')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}" />
@endpush

@section('content')
<div class="app-ecommerce-permission">

    @include('partials.alerts')

    <div class="card p-2">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Permissions</h5>
            <div>
                <button class="btn btn-danger" id="bulk-delete" data-url="{{ route('permissions.bulk-delete') }}">
                    <i class="menu-icon icon-base ti tabler-trash"></i> Delete Selected
                </button>

                <a href="{{ route('permissions.create') }}" class="btn btn-primary">
                    <i class="menu-icon icon-base ti tabler-plus"></i> Add Permission
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered" id="permissions-table">
                <thead>
                    <tr>
                        <th><input type="checkbox" class="form-check-input" id="select-all"></th>
                        <th>Name</th>
                        <th>Label</th>
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
let table = $('#permissions-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: '{{ route('permissions.index') }}',
    columns: [
        { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false },
        { data: 'name', name: 'name' },
        { data: 'label', name: 'label' },
        { data: 'actions', name: 'actions', orderable: false, searchable: false }
    ]
});

// Select/Deselect all
$('#select-all').on('click', function(){
    $('.bulk-checkbox').prop('checked', this.checked);
});
</script>
@endpush
