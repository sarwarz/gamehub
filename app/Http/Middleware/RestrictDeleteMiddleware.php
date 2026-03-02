<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictDeleteMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $isDeleteRequest = $request->isMethod('DELETE');
        $isBulkDelete = str_contains($request->path(), 'bulk-delete');

        if ($isDeleteRequest || $isBulkDelete) {
            $user = $request->user();

            if (!$user || !$user->canDelete()) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'You do not have permission to delete records.',
                    ], 403);
                }

                abort(403, 'You do not have permission to delete records.');
            }
        }

        return $next($request);
    }
}
