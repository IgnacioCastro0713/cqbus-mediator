<?php

namespace Tests\Fixtures;

use Ignaciocastro0713\CqbusMediator\Attributes\Routing\Api;
use Ignaciocastro0713\CqbusMediator\Attributes\Routing\Middleware;
use Ignaciocastro0713\CqbusMediator\Attributes\Routing\Prefix;
use Ignaciocastro0713\CqbusMediator\Traits\AsAction;
use Illuminate\Routing\Router;

#[Api]
#[Prefix('api/v1')]
#[Middleware('guest')]
class AttributeAction
{
    use AsAction;

    public static function route(Router $router): void
    {
        $router->get('/attribute-test', static::class);
    }

    public function handle()
    {
        return response()->json(['status' => 'ok']);
    }
}
