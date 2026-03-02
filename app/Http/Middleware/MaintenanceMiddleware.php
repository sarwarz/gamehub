<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Setting;
use Symfony\Component\HttpFoundation\Response;

class MaintenanceMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $maintenance = Setting::group('maintenance');

        if (empty($maintenance['enabled'])) {
            return $next($request);
        }

        $allowedIps = $maintenance['allowed_ips'] ?? [];
        if (is_string($allowedIps)) {
            $allowedIps = array_filter(array_map('trim', explode(',', $allowedIps)));
        }

        if (in_array($request->ip(), $allowedIps)) {
            return $next($request);
        }

        if (auth()->check() && auth()->user()->roles()->whereIn('name', ['superadmin', 'admin'])->exists()) {
            return $next($request);
        }

        $message = $maintenance['message'] ?? 'We are currently performing maintenance. Please check back soon.';
        $expectedBack = $maintenance['expected_back'] ?? null;

        if ($request->is('api/*') || $request->expectsJson()) {
            return response()->json([
                'status'        => false,
                'message'       => $message,
                'expected_back' => $expectedBack,
            ], 503);
        }

        abort(503, $message);
    }
}
