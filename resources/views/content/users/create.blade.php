@extends('layouts.app')
@section('title', 'Create User')

@section('content')

    @include('partials.alerts')

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1">
                <a href="{{ route('users.index') }}" class="text-muted me-1"><i class="icon-base ti tabler-arrow-left icon-md"></i></a>
                Create New User
            </h4>
            <p class="text-muted mb-0">Add a new user account with roles and permissions</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('users.index') }}" class="btn btn-label-secondary">
                <i class="icon-base ti tabler-x me-1"></i> Cancel
            </a>
            <button type="submit" form="user-form" class="btn btn-primary">
                <i class="icon-base ti tabler-user-plus me-1"></i> Create User
            </button>
        </div>
    </div>

    <form method="POST" action="{{ route('users.store') }}" id="user-form">
        @csrf

        <div class="row">
            {{-- Left Column --}}
            <div class="col-xl-4 col-lg-5">

                {{-- Account Type Quick Select --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="icon-base ti tabler-user-scan me-2"></i>Account Type</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">Quick-select a preset or customize roles below.</p>
                        <div class="d-flex flex-column gap-2">
                            <button type="button" class="btn btn-label-warning text-start preset-btn" data-preset="admin">
                                <i class="icon-base ti tabler-shield-check me-2"></i> Admin <span class="text-muted ms-auto small">(Full panel access)</span>
                            </button>
                            <button type="button" class="btn btn-label-info text-start preset-btn" data-preset="support">
                                <i class="icon-base ti tabler-headset me-2"></i> Support Agent <span class="text-muted ms-auto small">(Limited panel)</span>
                            </button>
                            <button type="button" class="btn btn-label-primary text-start preset-btn" data-preset="seller">
                                <i class="icon-base ti tabler-building-store me-2"></i> Seller <span class="text-muted ms-auto small">(API seller + buyer)</span>
                            </button>
                            <button type="button" class="btn btn-label-success text-start preset-btn" data-preset="customer">
                                <i class="icon-base ti tabler-user me-2"></i> Customer <span class="text-muted ms-auto small">(API buyer only)</span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Account Status --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="icon-base ti tabler-shield-check me-2"></i>Account Status</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                            <div>
                                <h6 class="mb-0">Active Account</h6>
                                <small class="text-muted">User can login and access the platform</small>
                            </div>
                            <div class="form-check form-switch mb-0">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1" class="form-check-input"
                                    style="width:3rem;height:1.5rem;" checked>
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                            <div>
                                <h6 class="mb-0">Verified (KYC)</h6>
                                <small class="text-muted">Skip identity verification</small>
                            </div>
                            <div class="form-check form-switch mb-0">
                                <input type="hidden" name="is_verified" value="0">
                                <input type="checkbox" name="is_verified" value="1" class="form-check-input"
                                    style="width:3rem;height:1.5rem;">
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="mb-0">Email Verified</h6>
                                <small class="text-muted">Mark email as pre-verified</small>
                            </div>
                            <div class="form-check form-switch mb-0">
                                <input type="hidden" name="email_verified" value="0">
                                <input type="checkbox" name="email_verified" value="1" class="form-check-input"
                                    style="width:3rem;height:1.5rem;">
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Right Column --}}
            <div class="col-xl-8 col-lg-7">

                {{-- Personal Information --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="icon-base ti tabler-user-edit me-2"></i>Personal Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name') }}" placeholder="John Doe" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text">@</span>
                                    <input type="text" name="username" class="form-control @error('username') is-invalid @enderror"
                                        value="{{ old('username') }}" placeholder="johndoe">
                                </div>
                                @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email') }}" placeholder="john@example.com" required>
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Password --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="icon-base ti tabler-lock me-2"></i>Password</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Password <span class="text-danger">*</span></label>
                                <div class="input-group input-group-merge">
                                    <input type="password" name="password" id="password"
                                        class="form-control @error('password') is-invalid @enderror"
                                        placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" required>
                                    <span class="input-group-text cursor-pointer toggle-password" data-target="password">
                                        <i class="icon-base ti tabler-eye-off"></i>
                                    </span>
                                </div>
                                @error('password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                <div class="input-group input-group-merge">
                                    <input type="password" name="password_confirmation" id="password_confirmation"
                                        class="form-control"
                                        placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" required>
                                    <span class="input-group-text cursor-pointer toggle-password" data-target="password_confirmation">
                                        <i class="icon-base ti tabler-eye-off"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Roles --}}
                <div class="card mb-4">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0"><i class="icon-base ti tabler-shield me-2"></i>Roles</h5>
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
                                    $isChecked = is_array(old('roles')) && in_array($role->id, old('roles'));
                                    $typeLabel = $role->type === 'internal' ? 'Internal' : 'External';
                                    $typeBg = $role->type === 'internal' ? 'bg-label-primary' : 'bg-label-warning';
                                @endphp
                                <div class="col-md-6 col-lg-4">
                                    <label class="d-block border rounded p-3 cursor-pointer role-label {{ $isChecked ? 'border-'.$roleColor.' bg-label-'.$roleColor : '' }}"
                                        for="role-{{ $role->id }}" style="transition: all .2s ease;">
                                        <div class="d-flex align-items-center mb-2">
                                            <input type="checkbox" name="roles[]" value="{{ $role->id }}" id="role-{{ $role->id }}"
                                                class="form-check-input me-2 role-checkbox" data-color="{{ $roleColor }}" data-name="{{ $role->name }}"
                                                {{ $isChecked ? 'checked' : '' }}>
                                            <i class="icon-base ti {{ $roleIcon }} text-{{ $roleColor }} me-1"></i>
                                            <span class="fw-medium">{{ $role->label ?? ucfirst($role->name) }}</span>
                                        </div>
                                        <div class="d-flex align-items-center gap-1">
                                            <span class="badge {{ $typeBg }}" style="font-size:.65rem">{{ $typeLabel }}</span>
                                            @if($role->is_system)
                                                <span class="badge bg-label-danger" style="font-size:.65rem"><i class="ti tabler-lock" style="font-size:.55rem"></i> System</span>
                                            @endif
                                        </div>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Direct Permissions --}}
                @if($permissions->count())
                <div class="card mb-4">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0"><i class="icon-base ti tabler-key me-2"></i>Direct Permissions</h5>
                        <div>
                            <button type="button" class="btn btn-sm btn-text-primary" id="check-all-perms">Select All</button>
                            <button type="button" class="btn btn-sm btn-text-secondary" id="uncheck-all-perms">Deselect All</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info d-flex align-items-center mb-3" role="alert">
                            <i class="icon-base ti tabler-info-circle me-2"></i>
                            <small>Direct permissions override role permissions. Only needed for special cases where a user needs extra access beyond their role.</small>
                        </div>
                        <div class="row g-2">
                            @foreach($permissions as $permission)
                                <div class="col-xl-4 col-md-4 col-sm-6">
                                    <div class="form-check">
                                        <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                            id="perm-{{ $permission->id }}" class="form-check-input perm-checkbox"
                                            {{ (is_array(old('permissions')) && in_array($permission->id, old('permissions'))) ? 'checked' : '' }}>
                                        <label for="perm-{{ $permission->id }}" class="form-check-label">
                                            {{ $permission->label ?? ucfirst($permission->name) }}
                                        </label>
                                    </div>
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

@push('page-js')
<script>
$(function () {
    var roleMap = {};
    @foreach($roles as $role)
        roleMap['{{ $role->name }}'] = '{{ $role->id }}';
    @endforeach

    var presets = {
        admin:    ['admin'],
        support:  ['support_agent'],
        seller:   ['seller', 'customer'],
        customer: ['customer']
    };

    function updateRoleCount() {
        var count = $('.role-checkbox:checked').length;
        $('#role-count').text(count + ' selected');
    }

    $('.preset-btn').on('click', function () {
        var preset = $(this).data('preset');
        var names = presets[preset] || [];

        $('.role-checkbox').each(function () {
            var name = $(this).data('name');
            var shouldCheck = names.indexOf(name) !== -1;
            $(this).prop('checked', shouldCheck).trigger('change');
        });

        updateRoleCount();
    });

    $('.toggle-password').on('click', function () {
        var target = $('#' + $(this).data('target'));
        var icon = $(this).find('i');
        if (target.attr('type') === 'password') {
            target.attr('type', 'text');
            icon.removeClass('tabler-eye-off').addClass('tabler-eye');
        } else {
            target.attr('type', 'password');
            icon.removeClass('tabler-eye').addClass('tabler-eye-off');
        }
    });

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

    $('#check-all-perms').on('click', function () { $('.perm-checkbox').prop('checked', true); });
    $('#uncheck-all-perms').on('click', function () { $('.perm-checkbox').prop('checked', false); });

    updateRoleCount();
});
</script>
@endpush
