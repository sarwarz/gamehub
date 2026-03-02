<?php

use Illuminate\Foundation\Application;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\ApiRoleMiddleware;
use App\Http\Middleware\PermissionMiddleware;
use App\Http\Middleware\MaintenanceMiddleware;
use App\Http\Middleware\RestrictDeleteMiddleware;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Http\Middleware\HandleCors;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(HandleCors::class);

        $middleware->alias([
            'role'            => RoleMiddleware::class,
            'api.role'        => ApiRoleMiddleware::class,
            'permission'      => PermissionMiddleware::class,
            'maintenance'     => MaintenanceMiddleware::class,
            'restrict.delete' => RestrictDeleteMiddleware::class,
        ]);

        $middleware->api(prepend: [
            'throttle:api',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        $exceptions->render(function (ValidationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status'  => false,
                    'message' => $e->getMessage(),
                    'errors'  => $e->errors(),
                ], 422);
            }
        });

        $exceptions->render(function (AuthenticationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Unauthenticated.',
                ], 401);
            }
        });

        $exceptions->render(function (AuthorizationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status'  => false,
                    'message' => $e->getMessage() ?: 'Forbidden.',
                ], 403);
            }
        });

        $exceptions->render(function (ModelNotFoundException $e, $request) {
            if ($request->is('api/*')) {
                $model = class_basename($e->getModel());
                return response()->json([
                    'status'  => false,
                    'message' => "{$model} not found.",
                ], 404);
            }
        });

        $exceptions->render(function (NotFoundHttpException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status'  => false,
                    'message' => 'API endpoint not found.',
                ], 404);
            }
        });

        $exceptions->render(function (MethodNotAllowedHttpException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Method not allowed.',
                ], 405);
            }
        });

        $exceptions->render(function (\Throwable $e, $request) {
            if ($request->is('api/*')) {
                $message = config('app.debug')
                    ? $e->getMessage()
                    : 'Something went wrong. Please try again later.';

                return response()->json([
                    'status'  => false,
                    'message' => $message,
                ], 500);
            }
        });

    })->create();
