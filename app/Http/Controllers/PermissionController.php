<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class PermissionController extends Controller
{
    /**
     * Display permissions using DataTables.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $permissions = Permission::query();

            return DataTables::of($permissions)
                ->addColumn('checkbox', function ($permission) {
                    return '<input type="checkbox" class="form-check-input bulk-checkbox" value="'.$permission->id.'">';
                })
                ->addColumn('actions', function ($permission) {
                    $editUrl = route('permissions.edit', $permission->id);
                    $deleteUrl = route('permissions.destroy', $permission->id);

                    return '
                        <a href="'.$editUrl.'" class="btn btn-sm btn-primary">
                             Edit
                        </a>
                        <form action="'.$deleteUrl.'" method="POST" style="display:inline-block;" onsubmit="return confirm(\'Are you sure?\')">
                            '.csrf_field().method_field('DELETE').'
                            <button type="submit" class="btn btn-sm btn-danger">
                                Delete
                            </button>
                        </form>
                    ';
                })
                ->rawColumns(['checkbox','actions'])
                ->make(true);
        }

        return view('content.permissions.index');
    }

    /**
     * Show the form for creating a new permission.
     */
    public function create()
    {
        return view('content.permissions.create');
    }

    /**
     * Store a new permission.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:255|unique:permissions,name',
            'label' => 'nullable|string|max:255',
        ]);

        Permission::create($request->only('name','label'));

        return redirect()->route('permissions.index')->with('success', 'Permission created successfully.');
    }

    /**
     * Show the form for editing the specified permission.
     */
    public function edit(Permission $permission)
    {
        return view('content.permissions.edit', compact('permission'));
    }

    /**
     * Update the specified permission.
     */
    public function update(Request $request, Permission $permission)
    {
        $request->validate([
            'name'  => 'required|string|max:255|unique:permissions,name,' . $permission->id,
            'label' => 'nullable|string|max:255',
        ]);

        $permission->update($request->only('name','label'));

        return redirect()->route('permissions.index')->with('success', 'Permission updated successfully.');
    }

    /**
     * Remove the specified permission.
     */
    public function destroy(Permission $permission)
    {
        $permission->delete();

        return redirect()->route('permissions.index')->with('success', 'Permission deleted successfully.');
    }

    /**
     * Bulk delete permissions.
     */
    public function bulkDelete(Request $request)
    {
        $ids = $request->ids;
        Permission::whereIn('id', $ids)->delete();

        return response()->json(['message' => 'Selected permissions deleted successfully.']);
    }
}
