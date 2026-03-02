@extends('layouts.app')
@section('title', 'Edit Role — ' . ($role->label ?? $role->name))

@section('content')

    @include('partials.alerts')

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1">
                <a href="{{ route('roles.index') }}" class="text-muted me-1"><i class="icon-base ti tabler-arrow-left icon-md"></i></a>
                Edit Role
            </h4>
            <p class="text-muted mb-0">Update role details and manage permissions</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('roles.index') }}" class="btn btn-label-secondary">
                <i class="icon-base ti tabler-x me-1"></i> Cancel
            </a>
            <button type="submit" form="role-form" class="btn btn-primary">
                <i class="icon-base ti tabler-device-floppy me-1"></i> Save Changes
            </button>
        </div>
    </div>

    <form method="POST" action="{{ route('roles.update', $role->id) }}" id="role-form">
        @csrf
        @method('PUT')

        <div class="row">
            {{-- Left Column --}}
            <div class="col-xl-4 col-lg-5">

                {{-- Role Profile Card --}}
                <div class="card mb-4">
                    <div class="card-body text-center pt-4">
                        @php
                            $profileIcon = match($role->name) {
                                'superadmin'    => 'tabler-crown',
                                'admin'         => 'tabler-shield-check',
                                'support_agent' => 'tabler-headset',
                                'seller'        => 'tabler-building-store',
                                'customer'      => 'tabler-user',
                                default         => 'tabler-users',
                            };
                            $profileColor = match($role->name) {
                                'superadmin'    => 'danger',
                                'admin'         => 'warning',
                                'support_agent' => 'info',
                                'seller'        => 'primary',
                                'customer'      => 'success',
                                default         => 'secondary',
                            };
                        @endphp
                        <div class="avatar avatar-xl mx-auto mb-3 bg-label-{{ $profileColor }}">
                            <span class="avatar-initial rounded-circle fs-2">
                                <i class="icon-base ti {{ $profileIcon }}"></i>
                            </span>
                        </div>
                        <h5 class="mb-1">{{ $role->label ?? ucfirst($role->name) }}</h5>
                        <p class="text-muted mb-3"><code>{{ $role->name }}</code></p>
                        <div class="d-flex justify-content-center gap-2 mb-3 flex-wrap">
                            <span class="badge {{ $role->type === 'internal' ? 'bg-label-primary' : 'bg-label-warning' }}">{{ ucfirst($role->type) }}</span>
                            @if($role->is_system)
                                <span class="badge bg-label-danger"><i class="ti tabler-lock ti-xs me-1"></i>System</span>
                            @else
                                <span class="badge bg-label-secondary">Custom</span>
                            @endif
                        </div>
                    </div>
                    <div class="card-body border-top pt-3">
                        <ul class="list-unstyled mb-0">
                            <li class="d-flex justify-content-between py-2">
                                <span class="text-muted"><i class="icon-base ti tabler-hash me-2"></i>ID</span>
                                <span class="fw-medium">#{{ $role->id }}</span>
                            </li>
                            <li class="d-flex justify-content-between py-2">
                                <span class="text-muted"><i class="icon-base ti tabler-users me-2"></i>Users</span>
                                <span class="badge bg-label-info rounded-pill">{{ $role->users_count ?? $role->users()->count() }}</span>
                            </li>
                            <li class="d-flex justify-content-between py-2">
                                <span class="text-muted"><i class="icon-base ti tabler-key me-2"></i>Permissions</span>
                                <span class="badge bg-label-primary rounded-pill">{{ $role->permissions->count() }}</span>
                            </li>
                            <li class="d-flex justify-content-between py-2">
                                <span class="text-muted"><i class="icon-base ti tabler-calendar me-2"></i>Created</span>
                                <span class="fw-medium">{{ $role->created_at->format('M d, Y') }}</span>
                            </li>
                        </ul>
                    </div>
                </div>

                {{-- Role Type --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="icon-base ti tabler-category me-2"></i>Role Type</h5>
                    </div>
                    <div class="card-body">
                        @if($role->is_system)
                            <div class="d-flex align-items-start border rounded p-3 {{ $role->type === 'internal' ? 'border-primary bg-label-primary' : 'border-warning bg-label-warning' }}">
                                <i class="icon-base ti {{ $role->type === 'internal' ? 'tabler-building text-primary' : 'tabler-world text-warning' }} me-3 mt-1"></i>
                                <div>
                                    <span class="fw-semibold">{{ ucfirst($role->type) }}</span>
                                    <br><small class="text-muted">System role type cannot be changed.</small>
                                </div>
                            </div>
                        @else
                            <div class="d-flex flex-column gap-3">
                                <label class="d-flex align-items-start border rounded p-3 cursor-pointer type-label {{ old('type', $role->type) === 'internal' ? 'border-primary bg-label-primary' : '' }}" for="type-internal">
                                    <input type="radio" name="type" value="internal" id="type-internal"
                                        class="form-check-input me-3 mt-1 type-radio"
                                        {{ old('type', $role->type) === 'internal' ? 'checked' : '' }}>
                                    <div>
                                        <div class="d-flex align-items-center mb-1">
                                            <i class="icon-base ti tabler-building text-primary me-2"></i>
                                            <span class="fw-semibold">Internal</span>
                                        </div>
                                        <small class="text-muted">Admin panel access</small>
                                    </div>
                                </label>
                                <label class="d-flex align-items-start border rounded p-3 cursor-pointer type-label {{ old('type', $role->type) === 'external' ? 'border-warning bg-label-warning' : '' }}" for="type-external">
                                    <input type="radio" name="type" value="external" id="type-external"
                                        class="form-check-input me-3 mt-1 type-radio"
                                        {{ old('type', $role->type) === 'external' ? 'checked' : '' }}>
                                    <div>
                                        <div class="d-flex align-items-center mb-1">
                                            <i class="icon-base ti tabler-world text-warning me-2"></i>
                                            <span class="fw-semibold">External</span>
                                        </div>
                                        <small class="text-muted">API / Frontend only</small>
                                    </div>
                                </label>
                            </div>
                            @error('type')
                                <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>
                </div>

                {{-- Danger Zone --}}
                @if(!$role->is_system)
                <div class="card border border-danger mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0 text-danger"><i class="icon-base ti tabler-alert-triangle me-2"></i>Danger Zone</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">Permanently delete this role. Users with this role will lose associated access.</p>
                        <button type="button" class="btn btn-danger w-100" id="btn-delete-role"
                            data-url="{{ route('roles.destroy', $role->id) }}">
                            <i class="icon-base ti tabler-trash me-1"></i> Delete Role
                        </button>
                    </div>
                </div>
                @endif

            </div>

            {{-- Right Column --}}
            <div class="col-xl-8 col-lg-7">

                {{-- Role Details --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="icon-base ti tabler-shield me-2"></i>Role Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Role Name <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="icon-base ti tabler-shield"></i></span>
                                    <input type="text" name="name"
                                        class="form-control @error('name') is-invalid @enderror"
                                        value="{{ old('name', $role->name) }}" required>
                                </div>
                                @error('name')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Lowercase with underscores. Used in code and middleware.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Display Label</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="icon-base ti tabler-tag"></i></span>
                                    <input type="text" name="label"
                                        class="form-control @error('label') is-invalid @enderror"
                                        value="{{ old('label', $role->label) }}">
                                </div>
                                @error('label')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Human-friendly name shown in the admin UI.</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Permissions --}}
                @if($permissions->count())
                <div class="card mb-4">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0"><i class="icon-base ti tabler-key me-2"></i>Permissions</h5>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-label-secondary" id="perm-count">0 selected</span>
                            <button type="button" class="btn btn-sm btn-text-primary" id="check-all-perms">Select All</button>
                            <button type="button" class="btn btn-sm btn-text-secondary" id="uncheck-all-perms">Clear</button>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($role->type === 'external')
                        <div class="alert alert-warning d-flex align-items-start mb-3" role="alert">
                            <i class="icon-base ti tabler-alert-triangle me-2 mt-1"></i>
                            <small>This is an external role. Permissions are typically not needed — API access is controlled via route middleware. Only assign permissions if this role also needs admin panel access.</small>
                        </div>
                        @endif
                        <div class="row g-2">
                            @foreach($permissions as $permission)
                                @php
                                    $isChecked = in_array($permission->id, old('permissions', $role->permissions->pluck('id')->toArray()));
                                @endphp
                                <div class="col-xl-4 col-md-4 col-sm-6">
                                    <label class="d-flex align-items-center border rounded px-3 py-2 cursor-pointer perm-label" for="perm-{{ $permission->id }}" style="transition: all .15s ease;">
                                        <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                            id="perm-{{ $permission->id }}" class="form-check-input me-2 perm-checkbox"
                                            {{ $isChecked ? 'checked' : '' }}>
                                        <span class="small">{{ $permission->label ?? ucwords(str_replace('-', ' ', $permission->name)) }}</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

            </div>
        </div>

    </form>

@endsection

@push('page-css')
<style>
.perm-label:has(.perm-checkbox:checked) { border-color: #7367f0 !important; background: rgba(115,103,240,.08); }
</style>
@endpush

@push('page-js')
<script>
$(function () {
    $('.type-radio').on('change', function () {
        $('.type-label').removeClass('border-primary bg-label-primary border-warning bg-label-warning');
        var selected = $('input[name="type"]:checked');
        var label = selected.closest('.type-label');
        if (selected.val() === 'internal') {
            label.addClass('border-primary bg-label-primary');
        } else {
            label.addClass('border-warning bg-label-warning');
        }
    });

    function updatePermCount() {
        var count = $('.perm-checkbox:checked').length;
        $('#perm-count').text(count + ' selected');
    }

    $('.perm-checkbox').on('change', updatePermCount);
    $('#check-all-perms').on('click', function () { $('.perm-checkbox').prop('checked', true); updatePermCount(); });
    $('#uncheck-all-perms').on('click', function () { $('.perm-checkbox').prop('checked', false); updatePermCount(); });
    updatePermCount();

    $('#btn-delete-role').on('click', function () {
        var url = $(this).data('url');
        Swal.fire({
            title: 'Delete this role?',
            text: 'Users with this role will lose associated access. This cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete',
            confirmButtonColor: '#d33',
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $.ajax({
                url: url,
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function () {
                    window.location.href = '{{ route("roles.index") }}';
                },
                error: function (xhr) {
                    Swal.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Failed to delete', showConfirmButton: false, timer: 2000 });
                }
            });
        });
    });
});
</script>
@endpush
