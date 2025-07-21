<?php

namespace Ignaciocastro0713\CqbusMediator;

use Ignaciocastro0713\CqbusMediator\Contracts\Mediator;
use Ignaciocastro0713\CqbusMediator\Services\MediatorService;
use Illuminate\Support\ServiceProvider;

class MediatorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/mediator.php', 'mediator'
        );


        $this->app->singleton(Mediator::class, function ($app) {
            $mediator = $app->make(MediatorService::class);
            $mediator->scanHandlers();
            return $mediator;
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/mediator.php' => config_path('mediator.php'),
        ], 'mediator-config');
    }
}
