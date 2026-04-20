<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withSchedule(function (): void {
        Schedule::command('telescope:prune --set-hours=resolved_exception:1,completed_job:0.1 --hours=336')->environments('production')->evenInMaintenanceMode()->daily()->timezone('America/New_York');

        Schedule::command('telescope:prune --set-hours=resolved_exception:12222');

        Schedule::command('emails:send')->daily()->when('fn () => User::count() > 0');
    })->create();
