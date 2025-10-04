@extends('layouts.app')
@section('title', 'Edit User')

@section('content')
<div class="card p-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5>Edit User</h5>
        <a href="{{ route('users.index') }}" class="btn btn-label-secondary">Back</a>
    </div>

    <div class="card-body border-top mt-3">
        <form method="POST" action="{{ route('users.update', $user->id) }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name',$user->name) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email',$user->email) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Password (Leave blank to keep current)</label>
                <input type="password" name="password" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="password_confirmation" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">Assign Roles</label>
                <div class="row">
                    @foreach($roles as $role)
                        <div class="col-md-4">
                            <div class="form-check">
                                <input type="checkbox" name="roles[]" value="{{ $role->id }}" id="role-{{ $role->id }}"
                                    class="form-check-input"
                                    {{ in_array($role->id, $user->roles->pluck('id')->toArray()) ? 'checked' : '' }}>
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
                                <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" id="perm-{{ $permission->id }}"
                                    class="form-check-input"
                                    {{ in_array($permission->id, $user->permissions->pluck('id')->toArray()) ? 'checked' : '' }}>
                                <label for="perm-{{ $permission->id }}" class="form-check-label">{{ $permission->label ?? $permission->name }}</label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>


            <button type="submit" class="btn btn-primary">Update</button>
            <a href="{{ route('users.index') }}" class="btn btn-label-danger">Cancel</a>
        </form>
    </div>
</div>
@endsection
