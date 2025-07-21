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
    | Example: app_path('Handlers/Commands') would scan 'app/Handlers/Commands'.
    |
    */
    'handler_paths' => [
        app_path('Handlers'), // A common directory for all handlers
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
