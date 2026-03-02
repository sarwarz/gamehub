<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $permissions = Permission::withCount('roles');

            return DataTables::of($permissions)
                ->addColumn('checkbox', function ($permission) {
                    return '<input type="checkbox" class="form-check-input bulk-checkbox" value="'.$permission->id.'">';
                })
                ->addColumn('roles_count', function ($permission) {
                    if ($permission->roles_count > 0) {
                        return '<span class="badge bg-label-info">'.$permission->roles_count.' role(s)</span>';
                    }
                    return '<span class="badge bg-label-secondary">None</span>';
                })
                ->addColumn('actions', function ($permission) {
                    return '<div class="d-flex align-items-center justify-content-center gap-1">
                        <a href="'.route('permissions.edit', $permission->id).'" class="btn btn-icon btn-sm btn-label-primary" title="Edit">
                            <i class="ti tabler-pencil ti-xs"></i>
                        </a>
                        <button type="button" class="btn btn-icon btn-sm btn-label-danger delete-btn" data-url="'.route('permissions.destroy', $permission->id).'" title="Delete">
                            <i class="ti tabler-trash ti-xs"></i>
                        </button>
                    </div>';
                })
                ->rawColumns(['checkbox','roles_count','actions'])
                ->make(true);
        }

        $stats = [
            'total'    => Permission::count(),
            'assigned' => Permission::has('roles')->count(),
            'orphaned' => Permission::doesntHave('roles')->count(),
        ];

        return view('content.permissions.index', compact('stats'));
    }

    public function create()
    {
        $roles = Role::internal()->get();
        $existingPermissions = Permission::orderBy('name')->get();
        return view('content.permissions.create', compact('roles', 'existingPermissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:255|unique:permissions,name',
            'label'   => 'nullable|string|max:255',
            'roles'   => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ]);

        $permission = Permission::create($request->only('name', 'label'));

        if ($request->filled('roles')) {
            $permission->roles()->sync($request->roles);
        }

        return redirect()->route('permissions.index')->with('success', 'Permission created successfully.');
    }

    public function edit(Permission $permission)
    {
        $permission->load(['roles', 'users']);
        $roles = Role::internal()->get();
        return view('content.permissions.edit', compact('permission', 'roles'));
    }

    public function update(Request $request, Permission $permission)
    {
        $request->validate([
            'name'    => 'required|string|max:255|unique:permissions,name,' . $permission->id,
            'label'   => 'nullable|string|max:255',
            'roles'   => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ]);

        $permission->update($request->only('name', 'label'));
        $permission->roles()->sync($request->input('roles', []));

        return redirect()->route('permissions.edit', $permission->id)->with('success', 'Permission updated successfully.');
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();
        return response()->json(['message' => 'Permission deleted successfully.']);
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->ids;
        Permission::whereIn('id', $ids)->delete();
        return response()->json(['message' => 'Selected permissions deleted successfully.']);
    }
}
