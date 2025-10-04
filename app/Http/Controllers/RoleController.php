<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class RoleController extends Controller
{
   /**
     * Display roles using DataTables (AJAX).
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $roles = Role::with('permissions');

            return DataTables::of($roles)
                ->addColumn('checkbox', function ($role) {
                    return '<input type="checkbox" class="form-check-input bulk-checkbox" value="'.$role->id.'">';
                })
                ->addColumn('permissions', function ($role) {
                    if ($role->permissions->count() > 0) {
                        return '<span class="badge bg-label-info">'. $role->permissions->pluck('name')->implode('</span> <span class="badge bg-label-info">') .'</span>';
                    }
                    return '<span class="badge bg-label-secondary">No Permissions</span>';
                })
                ->addColumn('actions', function ($role) {
                    $editUrl   = route('roles.edit', $role->id);
                    $deleteUrl = route('roles.destroy', $role->id);

                    return '
                        <a href="'.$editUrl.'" class="btn btn-sm btn-warning">Edit</a>
                        <button class="btn btn-sm btn-danger btn-delete" data-url="'.$deleteUrl.'">Delete</button>
                    ';
                })
                ->rawColumns(['checkbox','permissions','actions'])
                ->make(true);
        }

        return view('content.roles.index'); // Blade view for DataTable
    }


    public function create()
    {
        // Pass all permissions to the create page
        $permissions = Permission::all();
        return view('content.roles.create', compact('permissions'));
    }

    /**
     * Store a new role in database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:255|unique:roles,name',
            'label' => 'nullable|string|max:255',
            'permissions'   => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        // Create role
        $role = Role::create([
            'name'  => $request->name,
            'label' => $request->label,
        ]);

        // Assign permissions if provided
        if ($request->filled('permissions')) {
            $role->permissions()->sync($request->permissions);
        }

        return redirect()
            ->route('roles.index')
            ->with('success', 'Role created successfully with permissions.');
    }


    public function edit(Role $role)
    {
        $permissions = Permission::all();
        return view('content.roles.edit', compact('role', 'permissions'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name'  => 'required|string|max:255|unique:roles,name,' . $role->id,
            'label' => 'nullable|string|max:255',
            'permissions'   => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->update([
            'name'  => $request->name,
            'label' => $request->label,
        ]);

        // Sync permissions
        $role->permissions()->sync($request->permissions ?? []);

        return redirect()->route('roles.index')->with('success', 'Role updated successfully.');
    }


    /**
     * Delete role.
     */
    public function destroy(Role $role)
    {
        $role->delete();
        return response()->json(['message' => 'Role deleted successfully']);
    }

    /**
     * Assign permissions to a role.
     */
    public function assignPermission(Request $request, Role $role)
    {
        $request->validate([
            'permissions'   => 'required|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->permissions()->sync($request->permissions);

        return response()->json([
            'message' => 'Permissions updated successfully',
            'role'    => $role->load('permissions'),
        ]);
    }
}
