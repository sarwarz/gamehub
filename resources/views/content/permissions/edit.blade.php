@extends('layouts.app')
@section('title', 'Edit Permission — ' . $permission->name)

@section('content')

    @include('partials.alerts')

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1">
                <a href="{{ route('permissions.index') }}" class="text-muted me-1"><i class="icon-base ti tabler-arrow-left icon-md"></i></a>
                Edit Permission
            </h4>
            <p class="text-muted mb-0">Update permission details and role assignments</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('permissions.index') }}" class="btn btn-label-secondary">
                <i class="icon-base ti tabler-x me-1"></i> Cancel
            </a>
            <button type="submit" form="permission-form" class="btn btn-primary">
                <i class="icon-base ti tabler-device-floppy me-1"></i> Save Changes
            </button>
        </div>
    </div>

    <form method="POST" action="{{ route('permissions.update', $permission->id) }}" id="permission-form">
        @csrf
        @method('PUT')

        <div class="row">
            {{-- Left Column --}}
            <div class="col-xl-4 col-lg-5">

                {{-- Permission Info --}}
                <div class="card mb-4">
                    <div class="card-body text-center pt-4">
                        <div class="avatar avatar-xl mx-auto mb-3 bg-label-info">
                            <span class="avatar-initial rounded-circle fs-2">
                                <i class="icon-base ti tabler-key"></i>
                            </span>
                        </div>
                        <h5 class="mb-1"><code>{{ $permission->name }}</code></h5>
                        <p class="text-muted mb-3">{{ $permission->label ?? ucwords(str_replace('-', ' ', $permission->name)) }}</p>
                        <div class="d-flex justify-content-center gap-3 mb-2">
                            <div class="text-center">
                                <h6 class="mb-0">{{ $permission->roles->count() }}</h6>
                                <small class="text-muted">Roles</small>
                            </div>
                            <div class="vr"></div>
                            <div class="text-center">
                                <h6 class="mb-0">{{ $permission->users->count() }}</h6>
                                <small class="text-muted">Direct Users</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body border-top pt-3">
                        <ul class="list-unstyled mb-0">
                            <li class="d-flex justify-content-between py-2">
                                <span class="text-muted"><i class="icon-base ti tabler-hash me-2"></i>ID</span>
                                <span class="fw-medium">#{{ $permission->id }}</span>
                            </li>
                            <li class="d-flex justify-content-between py-2">
                                <span class="text-muted"><i class="icon-base ti tabler-calendar me-2"></i>Created</span>
                                <span class="fw-medium">{{ $permission->created_at->format('M d, Y') }}</span>
                            </li>
                            <li class="d-flex justify-content-between py-2">
                                <span class="text-muted"><i class="icon-base ti tabler-clock me-2"></i>Updated</span>
                                <span class="fw-medium">{{ $permission->updated_at->diffForHumans() }}</span>
                            </li>
                        </ul>
                    </div>
                </div>

                {{-- Danger Zone --}}
                <div class="card border border-danger mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0 text-danger"><i class="icon-base ti tabler-alert-triangle me-2"></i>Danger Zone</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">Deleting this permission will remove it from all roles and users. This cannot be undone.</p>
                        <button type="button" class="btn btn-danger w-100" id="btn-delete-permission"
                            data-url="{{ route('permissions.destroy', $permission->id) }}">
                            <i class="icon-base ti tabler-trash me-1"></i> Delete Permission
                        </button>
                    </div>
                </div>

            </div>

            {{-- Right Column --}}
            <div class="col-xl-8 col-lg-7">

                {{-- Permission Details --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="icon-base ti tabler-key me-2"></i>Permission Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Permission Name <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="icon-base ti tabler-key"></i></span>
                                    <input type="text" name="name"
                                        class="form-control @error('name') is-invalid @enderror"
                                        value="{{ old('name', $permission->name) }}" required>
                                </div>
                                @error('name')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Lowercase, kebab-case. Used in code and middleware checks.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Display Label</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="icon-base ti tabler-tag"></i></span>
                                    <input type="text" name="label"
                                        class="form-control @error('label') is-invalid @enderror"
                                        value="{{ old('label', $permission->label) }}">
                                </div>
                                @error('label')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Human-friendly name shown in the admin UI.</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Assign to Roles --}}
                <div class="card mb-4">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0"><i class="icon-base ti tabler-shield me-2"></i>Assign to Roles</h5>
                        <span class="badge bg-label-secondary" id="role-count">0 selected</span>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach($roles as $role)
                                @php
                                    $roleIcon = match($role->name) {
                                        'superadmin'    => 'tabler-crown',
                                        'admin'         => 'tabler-shield-check',
                                        'support_agent' => 'tabler-headset',
                                        'seller'        => 'tabler-building-store',
                                        'customer'      => 'tabler-user',
                                        default         => 'tabler-users',
                                    };
                                    $roleColor = match($role->name) {
                                        'superadmin'    => 'danger',
                                        'admin'         => 'warning',
                                        'support_agent' => 'info',
                                        'seller'        => 'primary',
                                        'customer'      => 'success',
                                        default         => 'secondary',
                                    };
                                    $isChecked = in_array($role->id, old('roles', $permission->roles->pluck('id')->toArray()));
                                @endphp
                                <div class="col-md-6 col-lg-4">
                                    <label class="d-block border rounded p-3 cursor-pointer role-label {{ $isChecked ? 'border-'.$roleColor.' bg-label-'.$roleColor : '' }}"
                                        for="role-{{ $role->id }}" style="transition: all .2s ease;">
                                        <div class="d-flex align-items-center mb-1">
                                            <input type="checkbox" name="roles[]" value="{{ $role->id }}" id="role-{{ $role->id }}"
                                                class="form-check-input me-2 role-checkbox" data-color="{{ $roleColor }}"
                                                {{ $isChecked ? 'checked' : '' }}>
                                            <i class="icon-base ti {{ $roleIcon }} text-{{ $roleColor }} me-1"></i>
                                            <span class="fw-medium">{{ $role->label ?? ucfirst($role->name) }}</span>
                                        </div>
                                        <div class="d-flex align-items-center gap-1 ps-4">
                                            <span class="badge {{ $role->type === 'internal' ? 'bg-label-primary' : 'bg-label-warning' }}" style="font-size:.6rem">{{ ucfirst($role->type) }}</span>
                                            @if($role->name === 'superadmin')
                                                <span class="badge bg-label-secondary" style="font-size:.6rem">Auto-bypass</span>
                                            @endif
                                        </div>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </form>

@endsection

@push('page-js')
<script>
$(function () {
    function updateRoleCount() {
        var count = $('.role-checkbox:checked').length;
        $('#role-count').text(count + ' selected');
    }

    $('.role-checkbox').on('change', function () {
        var label = $(this).closest('.role-label');
        var color = $(this).data('color');
        if (this.checked) {
            label.addClass('border-' + color + ' bg-label-' + color);
        } else {
            label.removeClass('border-' + color + ' bg-label-' + color);
        }
        updateRoleCount();
    });

    updateRoleCount();

    $('#btn-delete-permission').on('click', function () {
        var url = $(this).data('url');
        Swal.fire({
            title: 'Delete this permission?',
            text: 'This will remove it from all roles and users. This cannot be undone.',
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
                    window.location.href = '{{ route("permissions.index") }}';
                },
                error: function () {
                    Swal.fire({ icon: 'error', title: 'Failed to delete', showConfirmButton: false, timer: 1500 });
                }
            });
        });
    });
});
</script>
@endpush
