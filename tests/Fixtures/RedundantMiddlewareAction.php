<?php

namespace Tests\Fixtures;

use Ignaciocastro0713\CqbusMediator\Attributes\Pipelines\SkipGlobalPipelines;
use Ignaciocastro0713\CqbusMediator\Attributes\Routing\Api;
use Ignaciocastro0713\CqbusMediator\Attributes\Routing\Middleware;
use Ignaciocastro0713\CqbusMediator\Traits\AsAction;
use Illuminate\Routing\Router;

#[Api]
#[Middleware(['api', 'guest'])]
#[SkipGlobalPipelines] // Not a RouteModifier, should be ignored in loop
class RedundantMiddlewareAction
{
    use AsAction;

    public static function route(Router $router): void
    {
        $router->get('/redundant-middleware', self::class);
    }

    public function handle(): string
    {
        return 'ok';
    }
}
