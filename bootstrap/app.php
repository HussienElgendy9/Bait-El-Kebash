<?php

use App\Http\Middleware\IsAdmin;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();
        $middleware->alias([
            'admin' => IsAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(fn ($request, $e) => $request->is('api/*') || $request->expectsJson());
        $exceptions->render(function (UniqueConstraintViolationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => 'A record with these unique details already exists.'], 409);
            }
        });
        $exceptions->render(function (QueryException $e, Request $request) {
            if ($request->is('api/*') && (in_array($e->errorInfo[1] ?? null, [1451, 787], true) || ($e->errorInfo[0] ?? null) === '23503' || str_contains($e->getMessage(), 'FOREIGN KEY constraint failed'))) {
                return response()->json(['message' => 'This record is referenced by other data and cannot be removed.'], 409);
            }
        });
    })->create();
