<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Http\Request;

$factory = ModelFactory::build()->create();

return Application::configure(basePath: dirname(__DIR__))
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (HttpException $exception, Request $request) {
            return ($request->expectsJson()) ? response()->json(['error' => $exception->getMessage()], $exception->getStatusCode()) : null;
        });
    })->create();
