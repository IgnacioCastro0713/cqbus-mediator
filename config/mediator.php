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
    | Example: 'handler_paths' => app_path('Features'), would scan 'app/Features/Commands'. or
    |          'handler_paths' => [app_path('Features'), app_path('UseCases')]
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
    | Example Pipeline definition:
    | class LoggingPipeline
    | {
    |     public function handle($request, Closure $next)
    |     {
    |         Log::info('Handling Request pipeline', ['request' => $request]);
    |
    |         $response = $next($request);
    |
    |         Log::info('Handled Request pipeline', ['request' => $request]);
    |
    |         return $response;
    |     }
    | }
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
