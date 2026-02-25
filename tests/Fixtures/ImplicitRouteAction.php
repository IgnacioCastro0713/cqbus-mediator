<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use Ignaciocastro0713\CqbusMediator\Attributes\WebRoute;
use Ignaciocastro0713\CqbusMediator\Traits\AsAction;
use Illuminate\Routing\Router;

#[WebRoute]
class ImplicitRouteAction
{
    use AsAction;

    public static function route(Router $router): void
    {
        $router->get('/implicit-route-test');
    }

    public function handle(): string
    {
        return 'success_implicit';
    }
}
