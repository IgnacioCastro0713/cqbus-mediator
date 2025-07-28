<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Handler Discovery Paths
    |--------------------------------------------------------------------------
    |
    | This array defines the directories where the MediatorService will scan
    | for command/request handlers. These paths are relative to your Laravel
    | application's base directory (typically the 'app' directory).
    |
    | Example: app_path('Features/Commands') would scan 'app/Features/Commands'.
    |
    */
    'handler_paths' => [
        app_path('Features'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Global Pipelines
    |--------------------------------------------------------------------------
    |
    | The global pipelines (middleware) that will be applied to every request
    | sent through the Mediator. Each class should have a handle($request, Closure $next) method.
    |
    | Example:
    |   App\Pipelines\LoggingMiddleware::class,
    |   App\Pipelines\AuthMiddleware::class,
    */
    'pipelines' => [
    ],
];
