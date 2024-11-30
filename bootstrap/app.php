<?php

use App\Http\Middleware\IsAdminMiddelware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Http\Middleware\Authenticate;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'auth.jwt' => Authenticate::class,
            'auth.admin' => IsAdminMiddelware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            Log::error('Unhandled Exception', [
                'message' => $e->getMessage(),
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'An unexpected error occurred. Please try again later.',
                ], 500);
            }
        });
    })->create();
