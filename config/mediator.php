<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Handler Discovery Paths
    |--------------------------------------------------------------------------
    |
    | This handler_paths defines the directories where the MediatorService will scan
    | for command/request handlers. These paths are relative to your Laravel
    | application's base directory (typically the 'app' directory).
    |
    */
    'handler_paths' => app_path(),

    /*
    |--------------------------------------------------------------------------
    | Global Pipelines
    |--------------------------------------------------------------------------
    |
    | The global pipelines (middleware) that will be applied to every request
    | sent through the Mediator. Each class should have a handle($request, Closure $next) method.
    |
    | Example configuration:
    |  'pipelines' => [
    |      App\Pipelines\LoggingMiddleware::class,
    |  ]
    |
    | for more information: https://laravel.com/docs/helpers#pipeline
    */
    'pipelines' => [],
];
