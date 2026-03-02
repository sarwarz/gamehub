@extends('layouts.app')
@section('title', 'Users Management')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}">
<style>
.bulk-bar { background:#f0f2ff; border-radius:8px; animation:bulkSlide .3s ease; }
@keyframes bulkSlide { from { opacity:0; transform:translateY(-8px); } to { opacity:1; transform:translateY(0); } }
</style>
@endpush

@section('content')

    {{-- Page Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1"><i class="ti tabler-users me-2"></i>Users Management</h4>
            <p class="text-muted mb-0">Manage all users, roles, and account status</p>
        </div>
        <a href="{{ route('users.create') }}" class="btn btn-primary">
            <i class="ti tabler-plus me-1"></i> Add User
        </a>
    </div>

    {{-- Stats Cards --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3 bg-label-primary">
                            <i class="ti tabler-users fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ $stats['total'] }}</h5>
                            <small class="text-muted">Total Users</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3 bg-label-info">
                            <i class="ti tabler-user fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ $stats['customers'] }}</h5>
                            <small class="text-muted">Customers</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3 bg-label-warning">
                            <i class="ti tabler-building-store fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ $stats['sellers'] }}</h5>
                            <small class="text-muted">Sellers</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3 bg-label-danger">
                            <i class="ti tabler-shield fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ $stats['admins'] }}</h5>
                            <small class="text-muted">Admins</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- DataTable Card --}}
    <div class="card">
        {{-- Card Header --}}
        <div class="card-header pb-0">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <h5 class="mb-0"><i class="ti tabler-list-details me-2"></i>All Users</h5>
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-label-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#filter-collapse" aria-expanded="false">
                        <i class="ti tabler-filter me-1"></i> Filters
                    </button>
                </div>
            </div>

            {{-- Collapsible Filters --}}
            <div class="collapse mt-3" id="filter-collapse">
                <div class="row g-3 pb-3 border-bottom">
                    <div class="col-md-3 col-sm-6">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="ti tabler-user ti-xs"></i></span>
                            <select class="form-select" id="filter-role">
                                <option value="">All Roles</option>
                                <option value="customer">Customer</option>
                                <option value="seller">Seller</option>
                                <option value="admin">Admin</option>
                                <option value="superadmin">Super Admin</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="ti tabler-toggle-left ti-xs"></i></span>
                            <select class="form-select" id="filter-status">
                                <option value="">All Statuses</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="ti tabler-mail-check ti-xs"></i></span>
                            <select class="form-select" id="filter-verified">
                                <option value="">All Verification</option>
                                <option value="yes">Verified</option>
                                <option value="no">Unverified</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 d-flex gap-2">
                        <button class="btn btn-primary btn-sm flex-fill" id="btn-apply-filters">
                            <i class="ti tabler-search me-1"></i> Apply
                        </button>
                        <button class="btn btn-outline-secondary btn-sm flex-fill" id="btn-clear-filters">
                            <i class="ti tabler-filter-off me-1"></i> Clear
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bulk Actions Bar --}}
        <div class="card-body py-0">
            <div class="bulk-bar d-none py-2" id="bulk-bar">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary rounded-pill fs-6" id="bulk-count">0</span>
                        <span class="fw-medium" style="font-size:.85rem">users selected</span>
                    </div>
                    <button class="btn btn-sm btn-label-danger bulk-delete-btn" type="button">
                        <i class="ti tabler-trash me-1"></i> Delete Selected
                    </button>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="table-responsive">
            <table id="users-table" class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th width="40"><input type="checkbox" class="form-check-input" id="select-all"></th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Roles</th>
                        <th class="text-center">Status</th>
                        <th class="text-center" width="80">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

@endsection

@push('page-js')
<script>
$(function () {
    const csrfToken   = '{{ csrf_token() }}';
    const indexUrl     = '{{ route("users.index") }}';
    const bulkUrl      = '{{ route("users.bulk-delete") }}';

    let table = $('#users-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: indexUrl,
            data: function (d) {
                d.role     = $('#filter-role').val();
                d.status   = $('#filter-status').val();
                d.verified = $('#filter-verified').val();
            }
        },
        columns: [
            { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false, className: 'text-center' },
            { data: 'name', name: 'name' },
            { data: 'email', name: 'email' },
            { data: 'roles', name: 'roles', orderable: false, searchable: false },
            { data: 'status_badge', name: 'is_active', orderable: false, searchable: false, className: 'text-center' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' }
        ],
        order: [[1, 'asc']],
        drawCallback: function () {
            syncBulkBar();
        }
    });

    // Filters
    $('#btn-apply-filters').on('click', function () {
        table.ajax.reload();
    });
    $('#btn-clear-filters').on('click', function () {
        $('#filter-role, #filter-status, #filter-verified').val('');
        table.ajax.reload();
    });

    // Select All
    $('#select-all').on('change', function () {
        $('.bulk-checkbox').prop('checked', this.checked);
        syncBulkBar();
    });

    $(document).on('change', '.bulk-checkbox', function () {
        let total   = $('.bulk-checkbox').length;
        let checked = $('.bulk-checkbox:checked').length;
        $('#select-all').prop('checked', total === checked);
        syncBulkBar();
    });

    function syncBulkBar() {
        let count = $('.bulk-checkbox:checked').length;
        if (count > 0) {
            $('#bulk-bar').removeClass('d-none');
            $('#bulk-count').text(count);
        } else {
            $('#bulk-bar').addClass('d-none');
        }
    }

    // Bulk Delete
    $(document).on('click', '.bulk-delete-btn', function () {
        let ids = $('.bulk-checkbox:checked').map(function () { return $(this).val(); }).get();
        if (!ids.length) return;

        Swal.fire({
            title: 'Delete ' + ids.length + ' user(s)?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete',
            customClass: { confirmButton: 'btn btn-danger me-2', cancelButton: 'btn btn-outline-secondary' },
            buttonsStyling: false
        }).then(function (result) {
            if (result.isConfirmed) {
                $.ajax({
                    url: bulkUrl,
                    method: 'POST',
                    data: { ids: ids, _token: csrfToken },
                    success: function () {
                        Swal.fire({ icon: 'success', title: 'Deleted!', text: 'Selected users have been deleted.', timer: 1500, showConfirmButton: false });
                        table.ajax.reload();
                        $('#select-all').prop('checked', false);
                    },
                    error: function () {
                        Swal.fire({ icon: 'error', title: 'Error', text: 'Something went wrong.' });
                    }
                });
            }
        });
    });

    // Individual Delete
    $(document).on('click', '.delete-user-btn', function () {
        let url = $(this).data('url');

        Swal.fire({
            title: 'Delete this user?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete',
            customClass: { confirmButton: 'btn btn-danger me-2', cancelButton: 'btn btn-outline-secondary' },
            buttonsStyling: false
        }).then(function (result) {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    method: 'DELETE',
                    data: { _token: csrfToken },
                    success: function () {
                        Swal.fire({ icon: 'success', title: 'Deleted!', text: 'User has been deleted.', timer: 1500, showConfirmButton: false });
                        table.ajax.reload();
                    },
                    error: function () {
                        Swal.fire({ icon: 'error', title: 'Error', text: 'Something went wrong.' });
                    }
                });
            }
        });
    });
});
</script>
@endpush
