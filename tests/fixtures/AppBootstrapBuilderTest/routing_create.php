<?php

use Illuminate\Foundation\Application;

$factory = ModelFactory::build()->create();

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        health: '/up',
    )->create();
