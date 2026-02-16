<?php

namespace Tests\Fixtures;

use Ignaciocastro0713\CqbusMediator\Attributes\Middleware;
use Ignaciocastro0713\CqbusMediator\Attributes\Prefix;
use Ignaciocastro0713\CqbusMediator\Traits\AsAction;
use Illuminate\Routing\Router;

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
