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
    |  'global_pipelines' => [
    |      App\Pipelines\LoggingMiddleware::class,
    |  ]
    |
    | for more information: https://laravel.com/docs/helpers#pipeline
    */
    'global_pipelines' => [],

    /*
    |--------------------------------------------------------------------------
    | Request Pipelines
    |--------------------------------------------------------------------------
    |
    | These pipelines are applied specifically to Request Handlers (commands, queries),
    | but not to Notifications (events). They run after global_pipelines.
    |
    */
    'request_pipelines' => [],

    /*
    |--------------------------------------------------------------------------
    | Notification Pipelines
    |--------------------------------------------------------------------------
    |
    | These pipelines are applied specifically to Notification Handlers (events),
    | but not to Requests. They run after global_pipelines.
    |
    */
    'notification_pipelines' => [],

    /*
    |--------------------------------------------------------------------------
    | Route Priority Sorting Direction
    |--------------------------------------------------------------------------
    |
    | When actions are registered via the ActionDecoratorManager, they are
    | sorted based on the Priority attribute. By default, they are sorted
    | in descending ('desc') order (highest priority first). You can change
    | this behavior to 'asc' if you prefer lower priority routes first.
    |
    */
    'route_priority_direction' => 'desc',
];
