@extends('layouts.app')
@section('title', 'Roles Management')

@section('content')

    {{-- Page Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1"><i class="ti tabler-shield me-2"></i>Roles</h4>
            <p class="text-muted mb-0">Manage user roles and their permissions</p>
        </div>
        <a href="{{ route('roles.create') }}" class="btn btn-primary">
            <i class="ti tabler-plus me-1"></i> Add Role
        </a>
    </div>

    {{-- Stats --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-sm-6 mb-3 mb-xl-0">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3 bg-label-primary">
                            <i class="ti tabler-shield fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ $stats['total'] }}</h5>
                            <small class="text-muted">Total Roles</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-3 mb-xl-0">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3 bg-label-success">
                            <i class="ti tabler-building fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ $stats['internal'] }}</h5>
                            <small class="text-muted">Internal Roles</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-3 mb-xl-0">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3 bg-label-warning">
                            <i class="ti tabler-world fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ $stats['external'] }}</h5>
                            <small class="text-muted">External Roles</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-3 mb-xl-0">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3 bg-label-info">
                            <i class="ti tabler-users fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ $stats['total_users'] }}</h5>
                            <small class="text-muted">Total Users</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- DataTable Card --}}
    <div class="card">
        <div class="card-header pb-0">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0"><i class="ti tabler-list me-2"></i>All Roles</h5>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-sm btn-label-primary role-filter active" data-type="">All</button>
                    <button type="button" class="btn btn-sm btn-label-primary role-filter" data-type="internal">Internal</button>
                    <button type="button" class="btn btn-sm btn-label-primary role-filter" data-type="external">External</button>
                </div>
            </div>
        </div>

        {{-- Bulk Actions Bar --}}
        <div class="card-body py-0">
            <div class="bulk-bar d-none py-2" id="bulk-bar">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary rounded-pill fs-6" id="bulk-count">0</span>
                        <span class="fw-medium" style="font-size:.85rem">roles selected</span>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <button class="btn btn-sm btn-label-danger bulk-delete-btn" type="button">
                            <i class="ti tabler-trash me-1"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="table-responsive">
            <table id="roles-table" class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th width="40"><input type="checkbox" class="form-check-input" id="select-all"></th>
                        <th>Name</th>
                        <th>Label</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Permissions</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('page-css')
<style>
.bulk-bar { background:#f0f2ff; border-radius:8px; animation:bulkSlide .3s ease; }
@keyframes bulkSlide { from { opacity:0; transform:translateY(-8px); } to { opacity:1; transform:translateY(0); } }
</style>
@endpush

@push('page-js')
<script>
$(function() {
    let currentType = '';

    const table = $('#roles-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("roles.index") }}',
            data: function(d) {
                d.type = currentType;
            }
        },
        order: [[1, 'asc']],
        pageLength: 25,
        columns: [
            { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false },
            { data: 'name', name: 'name' },
            { data: 'label', name: 'label' },
            { data: 'type_badge', name: 'type', orderable: true, searchable: false },
            { data: 'system_badge', name: 'is_system', orderable: true, searchable: false },
            { data: 'permissions', name: 'permissions', orderable: false, searchable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        language: {
            emptyTable: '<div class="py-4 text-center"><i class="ti tabler-shield-off ti-xl text-muted mb-2 d-block"></i><span class="text-muted">No roles found</span></div>'
        }
    });

    $('.role-filter').on('click', function() {
        $('.role-filter').removeClass('active');
        $(this).addClass('active');
        currentType = $(this).data('type');
        table.ajax.reload();
    });

    $('#select-all').on('change', function() {
        $('.bulk-checkbox').prop('checked', this.checked);
        updateBulkBar();
    });

    $(document).on('change', '.bulk-checkbox', function() {
        updateBulkBar();
    });

    function getSelectedIds() {
        return $('.bulk-checkbox:checked').map(function() { return $(this).val(); }).get();
    }

    function updateBulkBar() {
        const ids = getSelectedIds();
        if (ids.length > 0) {
            $('#bulk-bar').removeClass('d-none');
            $('#bulk-count').text(ids.length);
        } else {
            $('#bulk-bar').addClass('d-none');
        }
        $('#select-all').prop('checked', $('.bulk-checkbox').length > 0 && $('.bulk-checkbox:not(:checked)').length === 0);
    }

    $(document).on('click', '.bulk-delete-btn', function() {
        const ids = getSelectedIds();
        if (!ids.length) return;

        Swal.fire({
            title: 'Delete Selected Roles?',
            text: ids.length + ' role(s) will be permanently deleted.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete',
            customClass: { confirmButton: 'btn btn-danger me-3', cancelButton: 'btn btn-label-secondary' },
            buttonsStyling: false
        }).then(r => {
            if (!r.isConfirmed) return;
            $.ajax({
                url: '{{ route("roles.bulk-delete") }}',
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}', ids: ids },
                success: function() {
                    table.ajax.reload(null, false);
                    $('#bulk-bar').addClass('d-none');
                    Swal.fire({ icon: 'success', title: 'Roles deleted', showConfirmButton: false, timer: 1500, timerProgressBar: true });
                },
                error: function() {
                    Swal.fire({ icon: 'error', title: 'Failed to delete', showConfirmButton: false, timer: 1500 });
                }
            });
        });
    });

    $(document).on('click', '.delete-btn', function(e) {
        e.preventDefault();
        const url = $(this).data('url');
        Swal.fire({
            title: 'Delete Role?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete',
            customClass: { confirmButton: 'btn btn-danger me-3', cancelButton: 'btn btn-label-secondary' },
            buttonsStyling: false
        }).then(r => {
            if (!r.isConfirmed) return;
            $.ajax({
                url: url,
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function() {
                    table.ajax.reload(null, false);
                    Swal.fire({ icon: 'success', title: 'Role deleted', showConfirmButton: false, timer: 1500, timerProgressBar: true });
                },
                error: function() {
                    Swal.fire({ icon: 'error', title: 'Failed', showConfirmButton: false, timer: 1500 });
                }
            });
        });
    });
});
</script>
@endpush
