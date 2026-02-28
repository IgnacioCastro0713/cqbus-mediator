<?php

namespace Tests\Fixtures;

use Ignaciocastro0713\CqbusMediator\Attributes\Routing\Api;
use Ignaciocastro0713\CqbusMediator\Attributes\Routing\Middleware;
use Ignaciocastro0713\CqbusMediator\Traits\AsAction;
use Illuminate\Routing\Router;

#[Api]
#[Middleware('api')]
class NoPrefixAction
{
    use AsAction;

    public static function route(Router $router): void
    {
        $router->get('/root-api', static::class);
    }

    public function handle()
    {
    }
}
