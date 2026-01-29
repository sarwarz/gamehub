<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    public function handle(Request $request, Closure $next, string $permission)
    {

        $user = auth()->user();

        // 1️⃣ Not logged in
        if (! $user) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Unauthenticated.'], Response::HTTP_UNAUTHORIZED)
                : response()->view('errors.401', [], Response::HTTP_UNAUTHORIZED);
        }

        // 2️⃣ Super Admin bypass
        if ($user->roles()->where('name', 'superadmin')->exists()) {
            return $next($request);
        }

        // 3️⃣ Exact permission
        if ($user->hasPermission($permission)) {
            return $next($request);
        }

        // 4️⃣ Route-based fallback (safe)
        $routeName = optional($request->route())->getName();

        if ($routeName) {
            $parent = str($routeName)->before('.')->toString();

            if ($user->hasPermission($parent)) {
                return $next($request);
            }
        }

        // 5️⃣ Forbidden (403)
        return $request->expectsJson()
            ? response()->json(
                ['message' => 'You do not have permission to access this resource.'],
                Response::HTTP_FORBIDDEN
            )
            : response()->view('errors.403', [], Response::HTTP_FORBIDDEN);
    }
}
