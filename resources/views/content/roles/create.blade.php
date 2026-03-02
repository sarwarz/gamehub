@extends('layouts.app')
@section('title', 'Create Role')

@section('content')

    @include('partials.alerts')

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1">
                <a href="{{ route('roles.index') }}" class="text-muted me-1"><i class="icon-base ti tabler-arrow-left icon-md"></i></a>
                Add New Role
            </h4>
            <p class="text-muted mb-0">Create a new role and assign permissions</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('roles.index') }}" class="btn btn-label-secondary">
                <i class="icon-base ti tabler-x me-1"></i> Cancel
            </a>
            <button type="submit" form="role-form" class="btn btn-primary">
                <i class="icon-base ti tabler-plus me-1"></i> Create Role
            </button>
        </div>
    </div>

    <form method="POST" action="{{ route('roles.store') }}" id="role-form">
        @csrf

        <div class="row">
            {{-- Left Column --}}
            <div class="col-xl-4 col-lg-5">

                {{-- Role Type --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="icon-base ti tabler-category me-2"></i>Role Type</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-column gap-3">
                            <label class="d-flex align-items-start border rounded p-3 cursor-pointer type-label {{ old('type', 'internal') === 'internal' ? 'border-primary bg-label-primary' : '' }}" for="type-internal">
                                <input type="radio" name="type" value="internal" id="type-internal"
                                    class="form-check-input me-3 mt-1 type-radio"
                                    {{ old('type', 'internal') === 'internal' ? 'checked' : '' }}>
                                <div>
                                    <div class="d-flex align-items-center mb-1">
                                        <i class="icon-base ti tabler-building text-primary me-2"></i>
                                        <span class="fw-semibold">Internal</span>
                                    </div>
                                    <small class="text-muted">Can access the admin panel. For staff roles like Admin, Editor, Support Agent.</small>
                                </div>
                            </label>
                            <label class="d-flex align-items-start border rounded p-3 cursor-pointer type-label {{ old('type') === 'external' ? 'border-warning bg-label-warning' : '' }}" for="type-external">
                                <input type="radio" name="type" value="external" id="type-external"
                                    class="form-check-input me-3 mt-1 type-radio"
                                    {{ old('type') === 'external' ? 'checked' : '' }}>
                                <div>
                                    <div class="d-flex align-items-center mb-1">
                                        <i class="icon-base ti tabler-world text-warning me-2"></i>
                                        <span class="fw-semibold">External</span>
                                    </div>
                                    <small class="text-muted">For API / Next.js frontend users. Cannot access admin panel.</small>
                                </div>
                            </label>
                        </div>
                        @error('type')
                            <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Info --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="icon-base ti tabler-info-circle me-2"></i>How Roles Work</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="d-flex align-items-start py-2 border-bottom">
                                <i class="icon-base ti tabler-shield-check text-primary me-2 mt-1"></i>
                                <small class="text-muted"><strong>Internal roles</strong> control admin panel access. Permissions determine which sections they can see.</small>
                            </li>
                            <li class="d-flex align-items-start py-2 border-bottom">
                                <i class="icon-base ti tabler-world text-warning me-2 mt-1"></i>
                                <small class="text-muted"><strong>External roles</strong> control API access. Route middleware determines which endpoints they can use.</small>
                            </li>
                            <li class="d-flex align-items-start py-2">
                                <i class="icon-base ti tabler-crown text-danger me-2 mt-1"></i>
                                <small class="text-muted"><strong>Superadmin</strong> always bypasses all permission and role checks automatically.</small>
                            </li>
                        </ul>
                    </div>
                </div>

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
                                    <input type="text" name="name" id="role-name"
                                        class="form-control @error('name') is-invalid @enderror"
                                        value="{{ old('name') }}"
                                        placeholder="e.g. content_editor" required>
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
                                    <input type="text" name="label" id="role-label"
                                        class="form-control @error('label') is-invalid @enderror"
                                        value="{{ old('label') }}"
                                        placeholder="e.g. Content Editor">
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
                        <div class="alert alert-info d-flex align-items-start mb-3" role="alert">
                            <i class="icon-base ti tabler-info-circle me-2 mt-1"></i>
                            <small>Permissions control which admin panel sections this role can access. External roles typically don't need permissions — their access is controlled via API middleware.</small>
                        </div>
                        <div class="row g-2">
                            @foreach($permissions as $permission)
                                <div class="col-xl-4 col-md-4 col-sm-6">
                                    <label class="d-flex align-items-center border rounded px-3 py-2 cursor-pointer perm-label" for="perm-{{ $permission->id }}" style="transition: all .15s ease;">
                                        <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                            id="perm-{{ $permission->id }}" class="form-check-input me-2 perm-checkbox"
                                            {{ (is_array(old('permissions')) && in_array($permission->id, old('permissions'))) ? 'checked' : '' }}>
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
    $('#role-name').on('input', function () {
        var val = $(this).val().toLowerCase().replace(/\s+/g, '_').replace(/[^a-z0-9_]/g, '');
        $(this).val(val);
        if (!$('#role-label').data('manual')) {
            var label = val.split('_').map(function(w) { return w.charAt(0).toUpperCase() + w.slice(1); }).join(' ');
            $('#role-label').val(label);
        }
    });

    $('#role-label').on('input', function () {
        $(this).data('manual', true);
    });

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
});
</script>
@endpush
