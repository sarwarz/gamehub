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
            $query = User::with('roles');

            if ($request->filled('role')) {
                $query->whereHas('roles', fn($q) => $q->where('name', $request->role));
            }
            if ($request->filled('status')) {
                $query->where('is_active', $request->status === 'active');
            }
            if ($request->filled('verified')) {
                if ($request->verified === 'yes') $query->whereNotNull('email_verified_at');
                else $query->whereNull('email_verified_at');
            }

            return DataTables::of($query)
                ->addColumn('checkbox', function ($user) {
                    return $user->isSuperAdmin()
                        ? '<input type="checkbox" class="form-check-input" disabled title="Protected account">'
                        : '<input type="checkbox" class="form-check-input bulk-checkbox" value="'.$user->id.'">';
                })
                ->addColumn('roles', function ($user) {
                    $roles = $user->roles
                    ->pluck('name')
                    ->map(function ($role) {
                        $class = match ($role) {
                            'superadmin' => 'bg-label-danger',
                            'admin'      => 'bg-label-warning',
                            'seller'     => 'bg-label-primary',
                            'customer'   => 'bg-label-success',
                            default      => 'bg-label-info',
                        };

                        return '<span class="badge ' . $class . '">' . ucfirst($role) . '</span>';
                    })
                    ->implode(' ');


                    return $roles ?: '<span class="badge bg-label-secondary">No Role</span>';
                })
                ->addColumn('status_badge', function ($user) {
                    if ($user->is_active) {
                        return '<span class="badge bg-label-success">Active</span>';
                    }
                    return '<span class="badge bg-label-danger">Inactive</span>';
                })
                ->addColumn('actions', function ($user) {
                    $editUrl   = route('users.edit', $user->id);
                    $editBtn = '<a href="'.$editUrl.'" class="btn btn-icon btn-sm btn-label-primary" title="Edit">
                            <i class="ti tabler-pencil ti-xs"></i>
                        </a>';

                    $deleteBtn = '';
                    if (!$user->isSuperAdmin()) {
                        $deleteUrl = route('users.destroy', $user->id);
                        $deleteBtn = '<button type="button" class="btn btn-icon btn-sm btn-label-danger delete-user-btn" data-url="'.$deleteUrl.'" title="Delete">
                            <i class="ti tabler-trash ti-xs"></i>
                        </button>';
                    }

                    return '<div class="d-flex align-items-center justify-content-center gap-1">'.$editBtn.$deleteBtn.'</div>';
                })
                ->rawColumns(['checkbox','roles','status_badge','actions'])
                ->make(true);
        }

        $stats = [
            'total'     => User::count(),
            'customers' => User::whereHas('roles', fn($q) => $q->where('name', 'customer'))->count(),
            'sellers'   => User::where(function ($q) {
                $q->whereHas('roles', fn($r) => $r->where('name', 'seller'))
                  ->orWhereHas('seller');
            })->count(),
            'admins'    => User::whereHas('roles', fn($q) => $q->whereIn('name', ['admin', 'superadmin']))->count(),
            'verified'  => User::whereNotNull('email_verified_at')->count(),
            'active'    => User::where('is_active', true)->count(),
        ];

        return view('content.users.index', compact('stats'));
    }



    /**
     * Show create form
     */
    public function create()
    {
        $roles = auth()->user()->isSuperAdmin()
            ? Role::all()
            : Role::where('name', '!=', 'superadmin')->get();
        $permissions = Permission::all();
        return view('content.users.create', compact('roles','permissions'));
    }

    /**
     * Store new user
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'username'      => 'nullable|string|max:255|unique:users,username',
            'email'         => 'required|email|max:255|unique:users',
            'password'      => 'required|string|min:8|confirmed',
            'is_active'     => 'required|boolean',
            'is_verified'   => 'required|boolean',
            'email_verified'=> 'required|boolean',
            'roles'         => 'nullable|array',
            'roles.*'       => 'exists:roles,id',
            'permissions'   => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $user = User::create([
            'name'              => $request->name,
            'username'          => $request->username,
            'email'             => $request->email,
            'password'          => $request->password,
            'is_active'         => $request->boolean('is_active'),
            'is_verified'       => $request->boolean('is_verified'),
            'email_verified_at' => $request->boolean('email_verified') ? now() : null,
        ]);

        if ($request->filled('roles')) {
            $requestedRoles = $request->roles;
            if (!auth()->user()->isSuperAdmin()) {
                $superadminRoleId = Role::where('name', 'superadmin')->value('id');
                $requestedRoles = array_filter($requestedRoles, fn($id) => (int) $id !== $superadminRoleId);
            }
            $user->roles()->sync($requestedRoles);
        }

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
        $user->load(['roles', 'permissions', 'wallet', 'seller', 'profile', 'addresses']);
        $roles = auth()->user()->isSuperAdmin()
            ? Role::all()
            : Role::where('name', '!=', 'superadmin')->get();
        $permissions = Permission::all();
        return view('content.users.edit', compact('user', 'roles', 'permissions'));
    }

    /**
     * Update user
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'username'        => 'nullable|string|max:255|unique:users,username,' . $user->id,
            'email'           => 'required|email|max:255|unique:users,email,' . $user->id,
            'password'        => 'nullable|string|min:8|confirmed',
            'is_active'       => 'required|boolean',
            'is_verified'     => 'required|boolean',
            'email_verified'  => 'required|boolean',
            'roles'           => 'nullable|array',
            'roles.*'         => 'exists:roles,id',
            'permissions'     => 'nullable|array',
            'permissions.*'   => 'exists:permissions,id',
        ]);

        $data = [
            'name'        => $request->name,
            'username'    => $request->username,
            'email'       => $request->email,
            'is_active'   => $request->boolean('is_active'),
            'is_verified' => $request->boolean('is_verified'),
        ];

        if ($request->boolean('email_verified') && !$user->email_verified_at) {
            $data['email_verified_at'] = now();
        } elseif (!$request->boolean('email_verified')) {
            $data['email_verified_at'] = null;
        }

        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        if ($user->isSuperAdmin()) {
            $superadminRoleId = Role::where('name', 'superadmin')->value('id');
            $requestedRoles = $request->input('roles', []);

            if (!in_array($superadminRoleId, $requestedRoles)) {
                $requestedRoles[] = $superadminRoleId;
            }

            $user->roles()->sync($requestedRoles);
            $data['is_active'] = true;
        } elseif ($request->has('roles')) {
            $superadminRoleId = Role::where('name', 'superadmin')->value('id');
            $requestedRoles = array_filter($request->roles, fn($id) => (int) $id !== $superadminRoleId);
            $user->roles()->sync($requestedRoles);
        } else {
            $user->roles()->detach();
        }

        $user->permissions()->sync($request->input('permissions', []));

        $user->update($data);

        return redirect()
            ->route('users.edit', $user->id)
            ->with('success', 'User updated successfully.');
    }



    /**
     * Delete user
     */
    public function destroy(User $user)
    {
        if ($user->isSuperAdmin()) {
            return back()->with('error', 'Super Admin account cannot be deleted.');
        }

        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }

    /**
     * Bulk delete
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer|exists:users,id',
        ]);

        $ids = $request->ids;

        $protectedCount = User::whereIn('id', $ids)
            ->whereHas('roles', fn($q) => $q->where('name', 'superadmin'))
            ->count();

        if ($protectedCount > 0) {
            return response()->json(['message' => 'Selection contains a Super Admin account which cannot be deleted.'], 403);
        }

        User::whereIn('id', $ids)->delete();

        return response()->json(['message' => 'Selected users deleted successfully.']);
    }
    /**
     * Display customers list with DataTable
     */
    public function customer(Request $request)
    {
        if ($request->ajax()) {

            $customers = User::with(['roles', 'wallet'])
            ->whereHas('roles', function ($q) {
                $q->where('name', 'customer');
            });



            return DataTables::of($customers)

                ->addColumn('customer', function ($user) {
                    $initials = collect(explode(' ', $user->name))->map(fn($w) => strtoupper($w[0] ?? ''))->take(2)->implode('');
                    return '
                        <div class="d-flex align-items-center gap-2">
                            <span class="avatar avatar-sm bg-label-primary rounded-circle d-flex align-items-center justify-content-center" style="width:38px;height:38px;font-size:.8rem;font-weight:600;">
                                '.$initials.'
                            </span>
                            <div>
                                <strong>'.$user->name.'</strong><br>
                                <small class="text-muted">'.$user->email.'</small>
                            </div>
                        </div>
                    ';
                })

                ->addColumn('roles', function ($user) {

                    if ($user->roles->isEmpty()) {
                        return '<span class="badge bg-secondary">No Role</span>';
                    }

                    return $user->roles->pluck('name')->map(function ($role) {

                        $class = match ($role) {
                            'superadmin' => 'bg-label-danger',
                            'admin'      => 'bg-label-warning',
                            'seller'     => 'bg-label-primary',
                            'customer'   => 'bg-label-success',
                            default      => 'bg-label-info',
                        };

                        return '<span class="badge ' . $class . ' me-1">' . ucfirst($role) . '</span>';
                    })->implode(' ');
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
                        <div class="d-flex align-items-center justify-content-center gap-1">
                            <a href="'.route('users.edit', $user).'" class="btn btn-icon btn-sm btn-label-primary" title="Edit">
                                <i class="ti tabler-pencil ti-xs"></i>
                            </a>
                        </div>
                    ';
                })

                ->rawColumns(['customer', 'status','roles', 'verified', 'actions'])
                ->make(true);
        }

        return view('content.users.customer');
    }


}
