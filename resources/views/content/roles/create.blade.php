@extends('layouts.app')
@section('title', 'Create Role')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}" />
@endpush

@section('content')
<div class="app-ecommerce-role">

    @include('partials.alerts')

    <div class="card p-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Add New Role</h5>
            <a href="{{ route('roles.index') }}" class="btn btn-label-secondary">
                Back
            </a>
        </div>

        <div class="card-body border-top mt-3">
            <form method="POST" action="{{ route('roles.store') }}">
                @csrf

                <!-- Role Name -->
                <div class="mb-3">
                    <label class="form-label">Role Name</label>
                    <input type="text"
                        name="name"
                        class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name') }}"
                        placeholder="Enter role name" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Label -->
                <div class="mb-3">
                    <label class="form-label">Label (Optional)</label>
                    <input type="text"
                        name="label"
                        class="form-control @error('label') is-invalid @enderror"
                        value="{{ old('label') }}"
                        placeholder="Human friendly name e.g. Administrator">
                    @error('label')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Assign Permissions with Checkboxes -->
                <div class="mb-3">
                    <label class="form-label">Assign Permissions</label>
                    <div class="row">
                        @foreach($permissions as $permission)
                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input type="checkbox"
                                        class="form-check-input"
                                        name="permissions[]"
                                        value="{{ $permission->id }}"
                                        id="perm-{{ $permission->id }}"
                                        {{ (is_array(old('permissions')) && in_array($permission->id, old('permissions'))) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="perm-{{ $permission->id }}">
                                        {{ $permission->label ?? ucfirst($permission->name) }}
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Action Buttons -->
                <div>
                    <button type="submit" class="btn btn-primary">
                        <i class="menu-icon icon-base ti tabler-plus"></i> Create Role
                    </button>
                    <a href="{{ route('roles.index') }}" class="btn btn-label-danger">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
