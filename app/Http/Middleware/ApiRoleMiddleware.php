<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiRoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        foreach ($roles as $role) {
            if (in_array($role, ['internal', 'external'])) {
                if ($user->roles()->where('type', $role)->exists()) {
                    return $next($request);
                }
            } else {
                if ($user->roles()->where('name', $role)->exists()) {
                    if ($role === 'seller') {
                        $seller = $user->seller;
                        if (!$seller || $seller->status !== 'active') {
                            return response()->json([
                                'status'  => false,
                                'message' => 'Your seller account is not active.',
                            ], 403);
                        }
                    }
                    return $next($request);
                }
            }
        }

        return response()->json([
            'status'  => false,
            'message' => 'Forbidden - You do not have the required role.',
        ], 403);
    }
}
