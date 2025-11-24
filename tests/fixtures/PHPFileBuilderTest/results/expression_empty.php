<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->use([HandleCors::class, CheckForMaintenanceMode::class, ValidatePostSize::class, TrimStrings::class, ConvertEmptyStringsToNull::class, AutoDocMiddleware::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (HttpException $exception, Request $request) {
            return $request->expectsJson() ? response()->json(['error' => $exception->getMessage()], $exception->getStatusCode()) : null;
        });

        $exceptions->render(function (ExpectationFailedException $exception) {
            throw $exception;
        });
    })->create();
