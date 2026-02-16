<?php

namespace Tests\Fixtures;

use Ignaciocastro0713\CqbusMediator\Attributes\Middleware;
use Ignaciocastro0713\CqbusMediator\Traits\AsAction;
use Illuminate\Routing\Router;

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
