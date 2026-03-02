<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @group User
 *
 * APIs for retrieving the authenticated user's account information.
 */
class UserController extends Controller
{
    /**
     * Get current user
     *
     * Retrieve the authenticated user's full account details including
     * profile, wallet balance, seller account status, roles, and permissions.
     *
     * @authenticated
     *
     * @response 200 {"status":true,"message":"User fetched successfully","data":{"id":1,"name":"John Doe","email":"john@example.com","avatar":null,"is_seller":false,"roles":["customer"],"permissions":[],"wallet":{"id":1,"balance":"150.00","is_active":true},"profile":{"id":1,"first_name":"John","last_name":"Doe","phone":"+1234567890"}}}
     */
    public function me(Request $request): JsonResponse
    {
        try {
            $user = $request->user()->load([
                'profile',
                'wallet',
                'seller',
                'roles',
                'permissions',
            ]);

            return $this->success([
                'id'          => $user->id,
                'name'        => $user->name,
                'email'       => $user->email,
                'avatar'      => $user->profile?->avatar,
                'is_active'   => $user->is_active,
                'is_verified' => $user->is_verified,
                'is_seller'   => (bool) $user->seller,
                'seller_id'   => $user->seller?->id,
                'roles'       => $user->roles->pluck('name'),
                'permissions' => $user->permissions->pluck('name'),
                'wallet'      => $user->wallet ? [
                    'id'        => $user->wallet->id,
                    'balance'   => $user->wallet->balance,
                    'is_active' => $user->wallet->is_active,
                ] : null,
                'profile'     => $user->profile,
                'member_since' => $user->created_at?->toISOString(),
            ], 'User fetched successfully');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch user.', 500);
        }
    }
}
