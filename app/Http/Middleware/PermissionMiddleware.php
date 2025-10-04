<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PermissionMiddleware
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        $user = auth()->user();

        // Super Admin bypass
        if ($user && $user->is_super_admin) {
            return $next($request);
        }

        if (!$user) {
            abort(403, 'Unauthorized - You are not logged in.');
        }

        // If user has the exact permission
        if ($user->hasPermission($permission)) {
            return $next($request);
        }

        // If user has parent permission that matches route name (e.g. products.*)
        $routeName = $request->route()->getName(); // e.g. products.index
        $parent = explode('.', $routeName)[0];     // products

        if ($user->hasPermission($parent)) {
            return $next($request);
        }

        abort(403, 'Unauthorized - You do not have the required permission.');
    }
}
