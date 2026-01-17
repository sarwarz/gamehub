<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\Permission;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display users list with DataTable
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $users = User::with('roles');

            return DataTables::of($users)
                ->addColumn('checkbox', function ($user) {
                    return $user->is_super_admin
                        ? '<input type="checkbox" class="form-check-input" disabled>'
                        : '<input type="checkbox" class="form-check-input bulk-checkbox" value="'.$user->id.'">';
                })
                ->addColumn('roles', function ($user) {
                    $roles = $user->roles->pluck('name')->map(fn($r) =>
                        '<span class="badge bg-label-info">'.ucfirst($r).'</span>'
                    )->implode(' ');

                    if ($user->is_super_admin) {
                        $roles .= ' <span class="badge bg-label-danger">Super Admin</span>';
                    }

                    return $roles ?: '<span class="badge bg-label-secondary">No Role</span>';
                })
                ->addColumn('actions', function ($user) {
                    if ($user->is_super_admin) {
                        return '
                            <button class="btn btn-sm btn-secondary" disabled>
                                Protected
                            </button>
                        ';
                    }

                    $editUrl   = route('users.edit', $user->id);
                    $deleteUrl = route('users.destroy', $user->id);

                    return '
                        <a href="'.$editUrl.'" class="btn btn-sm btn-primary me-1">
                            Edit
                        </a>
                        <form action="'.$deleteUrl.'" method="POST" style="display:inline-block;" 
                            onsubmit="return confirm(\'Are you sure you want to delete this user?\')">
                            '.csrf_field().method_field('DELETE').'
                            <button type="submit" class="btn btn-sm btn-danger">
                                 Delete
                            </button>
                        </form>
                    ';
                })
                ->rawColumns(['checkbox','roles','actions'])
                ->make(true);
        }

        return view('content.users.index');
    }



    /**
     * Show create form
     */
    public function create()
    {
        $roles = Role::all();
        $permissions = Permission::all();
        return view('content.users.create', compact('roles','permissions'));
    }

    /**
     * Store new user
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|max:255|unique:users',
            'password'    => 'required|string|min:8|confirmed',
            'roles'       => 'nullable|array',
            'roles.*'     => 'exists:roles,id',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Assign roles
        if ($request->filled('roles')) {
            $user->roles()->sync($request->roles);
        }

        // Assign user-specific permissions
        if ($request->filled('permissions')) {
            $user->permissions()->sync($request->permissions);
        }

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }


    /**
     * Show edit form
     */
   public function edit(User $user)
    {
        $roles = Role::all();
        $permissions = Permission::all();
        return view('content.users.edit', compact('user','roles','permissions'));
    }


    /**
     * Update user
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|max:255|unique:users,email,' . $user->id,
            'password'    => 'nullable|string|min:8|confirmed',
            'roles'       => 'nullable|array',
            'roles.*'     => 'exists:roles,id',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $data = [
            'name'  => $request->name,
            'email' => $request->email,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        // Sync roles
        $user->roles()->sync($request->roles ?? []);

        // Sync user-specific permissions
        $user->permissions()->sync($request->permissions ?? []);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }


    /**
     * Delete user
     */
    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }

    /**
     * Bulk delete
     */
    public function bulkDelete(Request $request)
    {
        $ids = $request->ids;
        User::whereIn('id', $ids)->delete();

        return response()->json(['message' => 'Selected users deleted successfully.']);
    }
    /**
     * Display customers list with DataTable
     */
    public function customer(Request $request)
    {
        if ($request->ajax()) {

            $customers = User::query()
                ->where('is_seller', false)
                ->whereDoesntHave('roles', function ($q) {
                    $q->where('name', 'admin');
                });

            return DataTables::of($customers)

                ->addColumn('customer', function ($user) {
                    return '
                        <div class="d-flex align-items-center gap-2">
                            <img src="'.asset($user->avatar ?? 'assets/img/avatars/default.png').'"
                                 class="rounded-circle"
                                 width="40">
                            <div>
                                <strong>'.$user->name.'</strong><br>
                                <small class="text-muted">'.$user->email.'</small>
                            </div>
                        </div>
                    ';
                })

                ->addColumn('status', function ($user) {
                    return $user->is_active
                        ? '<span class="badge bg-success">Active</span>'
                        : '<span class="badge bg-danger">Inactive</span>';
                })

                ->addColumn('wallet_balance', function ($user) {
                    return number_format(optional($user->wallet)->balance ?? 0, 2);
                })

                ->addColumn('verified', function ($user) {
                    return $user->is_verified
                        ? '<span class="badge bg-success">Verified</span>'
                        : '<span class="badge bg-warning">Unverified</span>';
                })

                ->addColumn('actions', function ($user) {
                    return '
                        <a href="'.route('users.edit', $user).'"
                           class="btn btn-sm btn-warning">
                            Edit
                        </a>
                    ';
                })

                ->rawColumns(['customer', 'status', 'verified', 'actions'])
                ->make(true);
        }

        return view('content.users.customer');
    }


}
