<?php

namespace Tests\Fixtures;

use Ignaciocastro0713\CqbusMediator\Attributes\Routing\Api;
use Ignaciocastro0713\CqbusMediator\Attributes\Routing\Middleware;
use Ignaciocastro0713\CqbusMediator\Traits\AsAction;
use Illuminate\Routing\Router;

#[Api]
#[Middleware('auth:sanctum')]
class ApiWithCustomMiddlewareAction
{
    use AsAction;

    public static function route(Router $router): void
    {
        $router->get('/test-api-auth', self::class);
    }

    public function handle(): string
    {
        return 'api-auth-response';
    }
}
