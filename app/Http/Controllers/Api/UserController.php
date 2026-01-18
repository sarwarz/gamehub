<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group User
 *
 * APIs for authenticated user information.
 */
class UserController extends Controller
{
    /**
     * Get authenticated user
     *
     * Retrieve authenticated user's profile,
     * roles, permissions, wallet, and seller info.
     *
     * @authenticated
     */
    public function me(Request $request)
    {
        $user = $request->user()->load([
            'profile',
            'wallet',
            'seller',
            'roles',
            'permissions',
        ]);

        return response()->json([
            'status' => true,
            'data'   => [
                'id'        => $user->id,
                'name'      => $user->name,
                'email'     => $user->email,
                'avatar'    => $user->avatar,
                'is_seller' => (bool) $user->seller,
                'roles'     => $user->roles->pluck('name'),
                'permissions' => $user->permissions->pluck('name'),
                'wallet'    => $user->wallet,
                'profile'   => $user->profile,
            ],
        ]);
    }
}
