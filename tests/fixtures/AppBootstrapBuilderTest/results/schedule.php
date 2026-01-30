<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->withSchedule(function (): void {
        Schedule::command('telescope:prune --set-hours=resolved_exception:1,completed_job:0.1 --hours=336')->environments('production')->daily()->timezone('America/New_York');

        Schedule::command('telescope:prune --set-hours=resolved_exception:12222');
    })->create();
