<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $roles = Role::with('permissions');

            if ($request->filled('type')) {
                $roles->where('type', $request->type);
            }

            return DataTables::of($roles)
                ->addColumn('checkbox', function ($role) {
                    if ($role->is_system) {
                        return '<input type="checkbox" class="form-check-input" disabled title="System role">';
                    }
                    return '<input type="checkbox" class="form-check-input bulk-checkbox" value="'.$role->id.'">';
                })
                ->addColumn('type_badge', function ($role) {
                    if ($role->type === 'internal') {
                        return '<span class="badge bg-label-primary">Internal</span>';
                    }
                    return '<span class="badge bg-label-warning">External</span>';
                })
                ->addColumn('system_badge', function ($role) {
                    if ($role->is_system) {
                        return '<span class="badge bg-label-danger"><i class="ti tabler-lock ti-xs me-1"></i>System</span>';
                    }
                    return '<span class="badge bg-label-secondary">Custom</span>';
                })
                ->addColumn('permissions', function ($role) {
                    if ($role->permissions->count() > 0) {
                        return '<span class="badge bg-label-info">'. $role->permissions->pluck('name')->implode('</span> <span class="badge bg-label-info">') .'</span>';
                    }
                    return '<span class="badge bg-label-secondary">No Permissions</span>';
                })
                ->addColumn('actions', function ($role) {
                    $editBtn = '<a href="'.route('roles.edit', $role->id).'" class="btn btn-icon btn-sm btn-label-primary" title="Edit">
                            <i class="ti tabler-pencil ti-xs"></i>
                        </a>';

                    $deleteBtn = '';
                    if (!$role->is_system) {
                        $deleteBtn = '<button type="button" class="btn btn-icon btn-sm btn-label-danger delete-btn" data-url="'.route('roles.destroy', $role->id).'" title="Delete">
                            <i class="ti tabler-trash ti-xs"></i>
                        </button>';
                    }

                    return '<div class="d-flex align-items-center justify-content-center gap-1">'.$editBtn.$deleteBtn.'</div>';
                })
                ->rawColumns(['checkbox','type_badge','system_badge','permissions','actions'])
                ->make(true);
        }

        $stats = [
            'total'       => Role::count(),
            'internal'    => Role::internal()->count(),
            'external'    => Role::external()->count(),
            'total_users' => \App\Models\User::count(),
        ];

        return view('content.roles.index', compact('stats'));
    }

    public function create()
    {
        $permissions = Permission::all();
        return view('content.roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255|unique:roles,name',
            'label'         => 'nullable|string|max:255',
            'type'          => 'required|in:internal,external',
            'permissions'   => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create([
            'name'      => $request->name,
            'label'     => $request->label,
            'type'      => $request->type,
            'is_system' => false,
        ]);

        if ($request->filled('permissions')) {
            $role->permissions()->sync($request->permissions);
        }

        return redirect()
            ->route('roles.index')
            ->with('success', 'Role created successfully with permissions.');
    }

    public function edit(Role $role)
    {
        $role->loadCount('users');
        $permissions = Permission::all();
        return view('content.roles.edit', compact('role', 'permissions'));
    }

    public function update(Request $request, Role $role)
    {
        $rules = [
            'name'          => 'required|string|max:255|unique:roles,name,' . $role->id,
            'label'         => 'nullable|string|max:255',
            'permissions'   => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ];

        if (!$role->is_system) {
            $rules['type'] = 'required|in:internal,external';
        }

        $request->validate($rules);

        $data = [
            'name'  => $request->name,
            'label' => $request->label,
        ];

        if (!$role->is_system && $request->filled('type')) {
            $data['type'] = $request->type;
        }

        $role->update($data);
        $role->permissions()->sync($request->permissions ?? []);

        return redirect()->route('roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        if ($role->is_system) {
            return response()->json(['message' => 'System roles cannot be deleted.'], 403);
        }

        $role->delete();
        return response()->json(['message' => 'Role deleted successfully']);
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->ids;
        $systemCount = Role::whereIn('id', $ids)->where('is_system', true)->count();

        if ($systemCount > 0) {
            return response()->json(['message' => 'System roles cannot be deleted. Remove them from the selection.'], 403);
        }

        Role::whereIn('id', $ids)->where('is_system', false)->delete();

        return response()->json(['message' => 'Selected roles deleted successfully.']);
    }

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
