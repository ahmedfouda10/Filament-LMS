<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);

        $middleware->statefulApi();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // $exceptions->shouldRenderJsonWhen(function ($request) {
        //     return $request->is('api/*') || $request->expectsJson();
        // });

        // $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, $request) {
        //     if ($request->is('api/*') || $request->expectsJson()) {
        //         return response()->json([
        //             'message' => $e->getMessage() ?: 'An error occurred.',
        //         ], $e->getStatusCode());
        //     }
        // });

        // $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) {
        //     if ($request->is('api/*') || $request->expectsJson()) {
        //         return response()->json([
        //             'message' => 'Unauthenticated.',
        //         ], 401);
        //     }
        // });

        // $exceptions->render(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e, $request) {
        //     if ($request->is('api/*') || $request->expectsJson()) {
        //         return response()->json([
        //             'message' => 'Resource not found.',
        //         ], 404);
        //     }
        // });

        // $exceptions->render(function (\Illuminate\Validation\ValidationException $e, $request) {
        //     if ($request->is('api/*') || $request->expectsJson()) {
        //         return response()->json([
        //             'message' => 'The given data was invalid.',
        //             'errors' => $e->errors(),
        //         ], 422);
        //     }
        // });
    })->create();
