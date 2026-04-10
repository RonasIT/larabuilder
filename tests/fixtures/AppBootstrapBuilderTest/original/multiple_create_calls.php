<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;

$factory = ModelFactory::build()->create();

return Application::configure(basePath: dirname(__DIR__))
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })->create();
