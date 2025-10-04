@extends('layouts.app')
@section('title', 'Create User')

@section('content')
<div class="card p-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5>Add New User</h5>
        <a href="{{ route('users.index') }}" class="btn btn-label-secondary">Back</a>
    </div>

    <div class="card-body border-top mt-3">
        <form method="POST" action="{{ route('users.store') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="password_confirmation" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Assign Roles</label>
                <div class="row">
                    @foreach($roles as $role)
                        <div class="col-md-4">
                            <div class="form-check">
                                <input type="checkbox" name="roles[]" value="{{ $role->id }}" id="role-{{ $role->id }}" class="form-check-input">
                                <label for="role-{{ $role->id }}" class="form-check-label">{{ $role->label ?? $role->name }}</label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Assign Specific Permissions</label>
                <div class="row">
                    @foreach($permissions as $permission)
                        <div class="col-md-4">
                            <div class="form-check">
                                <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" id="perm-{{ $permission->id }}" class="form-check-input">
                                <label for="perm-{{ $permission->id }}" class="form-check-label">{{ $permission->label ?? $permission->name }}</label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>


            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ route('users.index') }}" class="btn btn-label-danger">Cancel</a>
        </form>
    </div>
</div>
@endsection
