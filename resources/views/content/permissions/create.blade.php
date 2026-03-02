@extends('layouts.app')
@section('title', 'Create Permission')

@section('content')

    @include('partials.alerts')

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1">
                <a href="{{ route('permissions.index') }}" class="text-muted me-1"><i class="icon-base ti tabler-arrow-left icon-md"></i></a>
                Add New Permission
            </h4>
            <p class="text-muted mb-0">Create a new permission and assign it to roles</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('permissions.index') }}" class="btn btn-label-secondary">
                <i class="icon-base ti tabler-x me-1"></i> Cancel
            </a>
            <button type="submit" form="permission-form" class="btn btn-primary">
                <i class="icon-base ti tabler-plus me-1"></i> Create Permission
            </button>
        </div>
    </div>

    <form method="POST" action="{{ route('permissions.store') }}" id="permission-form">
        @csrf

        <div class="row">
            {{-- Left Column --}}
            <div class="col-xl-4 col-lg-5">

                {{-- Naming Convention Guide --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="icon-base ti tabler-info-circle me-2"></i>Naming Guide</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">Permission names should use lowercase kebab-case and match the admin panel section they protect.</p>
                        <ul class="list-unstyled mb-0">
                            <li class="d-flex align-items-center py-2 border-bottom">
                                <code class="me-auto">products</code>
                                <small class="text-muted">Manage Products</small>
                            </li>
                            <li class="d-flex align-items-center py-2 border-bottom">
                                <code class="me-auto">blog-categories</code>
                                <small class="text-muted">Blog Categories</small>
                            </li>
                            <li class="d-flex align-items-center py-2 border-bottom">
                                <code class="me-auto">support-tickets</code>
                                <small class="text-muted">Support Tickets</small>
                            </li>
                            <li class="d-flex align-items-center py-2">
                                <code class="me-auto">settings</code>
                                <small class="text-muted">Settings Panel</small>
                            </li>
                        </ul>
                    </div>
                </div>

                {{-- Current Permissions --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="icon-base ti tabler-list-check me-2"></i>Existing Permissions</h5>
                    </div>
                    <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                        <div class="d-flex flex-wrap gap-1">
                            @foreach($existingPermissions as $perm)
                                <span class="badge bg-label-secondary">{{ $perm->name }}</span>
                            @endforeach
                        </div>
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
                                    <input type="text" name="name" id="permission-name"
                                        class="form-control @error('name') is-invalid @enderror"
                                        value="{{ old('name') }}"
                                        placeholder="e.g. manage-reports" required>
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
                                    <input type="text" name="label" id="permission-label"
                                        class="form-control @error('label') is-invalid @enderror"
                                        value="{{ old('label') }}"
                                        placeholder="e.g. Manage Reports">
                                </div>
                                @error('label')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Human-friendly name shown in the admin UI.</div>
                            </div>
                        </div>

                        {{-- Live Preview --}}
                        <div class="mt-4 p-3 rounded border bg-light">
                            <small class="text-muted d-block mb-2">Preview</small>
                            <div class="d-flex align-items-center gap-3">
                                <div>
                                    <span class="text-muted small">Name:</span>
                                    <code id="preview-name">—</code>
                                </div>
                                <div>
                                    <span class="text-muted small">Label:</span>
                                    <span class="badge bg-label-info" id="preview-label">—</span>
                                </div>
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
                        <div class="alert alert-info d-flex align-items-start mb-3" role="alert">
                            <i class="icon-base ti tabler-info-circle me-2 mt-1"></i>
                            <small>Select which roles should have this permission. Superadmin always bypasses permission checks regardless of assignment.</small>
                        </div>
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
                                    $isExternal = $role->type === 'external';
                                @endphp
                                <div class="col-md-6 col-lg-4">
                                    <label class="d-block border rounded p-3 cursor-pointer role-label {{ $isChecked ? 'border-'.$roleColor.' bg-label-'.$roleColor : '' }} {{ $isExternal ? 'opacity-75' : '' }}"
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
    $('#permission-name').on('input', function () {
        var val = $(this).val().toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9\-]/g, '');
        $(this).val(val);
        $('#preview-name').text(val || '—');

        if (!$('#permission-label').data('manual')) {
            var label = val.split('-').map(function(w) { return w.charAt(0).toUpperCase() + w.slice(1); }).join(' ');
            $('#permission-label').val(label);
            $('#preview-label').text(label || '—');
        }
    });

    $('#permission-label').on('input', function () {
        $(this).data('manual', true);
        $('#preview-label').text($(this).val() || '—');
    });

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
});
</script>
@endpush
