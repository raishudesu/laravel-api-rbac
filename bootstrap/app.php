<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'permission' => \App\Http\Middleware\CheckPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Unauthenticated',
                    'errors' => [
                        'auth' => ['Authentication required'],
                    ],
                ], 401);
            }
        });

        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        $exceptions->render(function (\Illuminate\Database\QueryException $e, $request) {
            if ($request->is('api/*')) {
                \Illuminate\Support\Facades\Log::error('Database query exception', [
                    'error' => $e->getMessage(),
                    'sql' => $e->getSql(),
                    'bindings' => $e->getBindings(),
                    'url' => $request->url(),
                    'method' => $request->method(),
                ]);

                return response()->json([
                    'message' => 'Database error occurred',
                    'errors' => [
                        'database' => ['A database error occurred. Please try again.'],
                    ],
                ], 500);
            }
        });

        $exceptions->render(function (\Exception $e, $request) {
            if ($request->is('api/*')) {
                \Illuminate\Support\Facades\Log::error('Unhandled exception in API', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'url' => $request->url(),
                    'method' => $request->method(),
                    'user_id' => auth()->id() ?? null,
                ]);

                return response()->json([
                    'message' => 'An unexpected error occurred',
                    'errors' => [
                        'system' => ['An unexpected error occurred. Please try again.'],
                    ],
                ], 500);
            }
        });
    })->create();
