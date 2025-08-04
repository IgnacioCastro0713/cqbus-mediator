<?php

namespace Ignaciocastro0713\CqbusMediator;

use Ignaciocastro0713\CqbusMediator\Console\MakeMediatorHandlerCommand;
use Ignaciocastro0713\CqbusMediator\Console\MediatorCacheCommand;
use Ignaciocastro0713\CqbusMediator\Console\MediatorClearCommand;
use Ignaciocastro0713\CqbusMediator\Contracts\Mediator;
use Ignaciocastro0713\CqbusMediator\Discovery\DiscoveryHandler;
use Ignaciocastro0713\CqbusMediator\Services\MediatorService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use ReflectionException;

class MediatorServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     * @throws ReflectionException
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/mediator.php', 'mediator');

        $this->app->singleton(Mediator::class, MediatorService::class);

        $this->loadHandlers();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/mediator.php' => config_path('mediator.php'),
        ], 'mediator-config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeMediatorHandlerCommand::class,
                MediatorCacheCommand::class,
                MediatorClearCommand::class,
            ]);
        }
    }

    /**
     * Loads handlers from the cache or Scan directories or get dynamically handlers
     * @throws ReflectionException
     */
    private function loadHandlers(): void
    {
        $cachePath = $this->app->bootstrapPath('cache/mediator_handlers.php');

        if (File::exists($cachePath)) {
            $this->loadCachedHandlers();

            return;
        }

        $this->loadDynamicallyHandlers();
    }

    /**
     * Loads handlers from the cache file if it exists.
     * @throws ReflectionException
     */
    private function loadCachedHandlers(): void
    {
        $handlersMap = require $this->app->bootstrapPath('cache/mediator_handlers.php');

        foreach ($handlersMap as $requestClass => $handlerClass) {
            $this->app->bind("mediator.handler.$requestClass", $handlerClass);
        }
    }

    /**
     * Scan directories and discover handlers.
     */
    private function loadDynamicallyHandlers(): void
    {
        $paths = config('mediator.handler_paths', app_path());
        if (! is_array($paths)) {
            $paths = [$paths ?? app_path()];
        }

        $handlers = DiscoveryHandler::in(...$paths)->get();
        foreach ($handlers as $requestClass => $handlerClass) {
            $this->app->bind("mediator.handler.$requestClass", fn ($app) => $app->make($handlerClass));
        }
    }
}
