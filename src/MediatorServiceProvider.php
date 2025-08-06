<?php

namespace Ignaciocastro0713\CqbusMediator;

use Ignaciocastro0713\CqbusMediator\Console\MakeMediatorHandlerCommand;
use Ignaciocastro0713\CqbusMediator\Console\MediatorCacheCommand;
use Ignaciocastro0713\CqbusMediator\Console\MediatorClearCommand;
use Ignaciocastro0713\CqbusMediator\Contracts\Mediator;
use Ignaciocastro0713\CqbusMediator\Services\MediatorService;
use Illuminate\Support\ServiceProvider;

class MediatorServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/mediator.php', 'mediator');

        $this->app->singleton(Mediator::class, MediatorService::class);
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
}
