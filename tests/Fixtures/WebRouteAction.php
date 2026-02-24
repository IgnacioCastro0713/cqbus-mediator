<?php

namespace Tests\Fixtures;

use Ignaciocastro0713\CqbusMediator\Attributes\WebRoute;
use Ignaciocastro0713\CqbusMediator\Traits\AsAction;
use Illuminate\Routing\Router;

#[WebRoute]
class WebRouteAction
{
    use AsAction;

    public static function route(Router $router): void
    {
        $router->get('/web-route-test', self::class);
    }

    public function handle(): string
    {
        return 'web-response';
    }
}
