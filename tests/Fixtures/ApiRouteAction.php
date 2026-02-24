<?php

namespace Tests\Fixtures;

use Ignaciocastro0713\CqbusMediator\Attributes\ApiRoute;
use Ignaciocastro0713\CqbusMediator\Traits\AsAction;
use Illuminate\Routing\Router;

#[ApiRoute]
class ApiRouteAction
{
    use AsAction;

    public static function route(Router $router): void
    {
        $router->get('/api-route-test', self::class);
    }

    public function handle(): string
    {
        return 'api-response';
    }
}
