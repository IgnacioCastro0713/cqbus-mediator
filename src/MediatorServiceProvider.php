<?php

declare(strict_types=1);

namespace Ignaciocastro0713\CqbusMediator;

use Ignaciocastro0713\CqbusMediator\Console\CacheCommand;
use Ignaciocastro0713\CqbusMediator\Console\ClearCommand;
use Ignaciocastro0713\CqbusMediator\Console\ListCommand;
use Ignaciocastro0713\CqbusMediator\Console\MakeEventHandlerCommand;
use Ignaciocastro0713\CqbusMediator\Console\MakeHandlerCommand;
use Ignaciocastro0713\CqbusMediator\Contracts\Mediator;
use Ignaciocastro0713\CqbusMediator\Services\MediatorService;
use Ignaciocastro0713\CqbusMediator\Support\ActionDecoratorManager;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\ServiceProvider;
use ReflectionException;

class MediatorServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/mediator.php', 'mediator');

        $this->app->singleton(Mediator::class, MediatorService::class);

        $this->app->singleton(ActionDecoratorManager::class);
    }

    /**
     * Bootstrap any application services.
     * @throws BindingResolutionException|ReflectionException
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/mediator.php' => config_path('mediator.php'),
        ], 'mediator-config');

        $this->app->make(ActionDecoratorManager::class)->boot();

        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeHandlerCommand::class,
                MakeEventHandlerCommand::class,
                CacheCommand::class,
                ClearCommand::class,
                ListCommand::class,
            ]);
        }
    }
}
