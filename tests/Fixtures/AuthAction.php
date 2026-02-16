<?php

namespace Tests\Fixtures;

use Ignaciocastro0713\CqbusMediator\Attributes\Middleware;
use Ignaciocastro0713\CqbusMediator\Attributes\Prefix;
use Ignaciocastro0713\CqbusMediator\Traits\AsAction;
use Illuminate\Routing\Router;

#[Prefix('secure')]
#[Middleware(['web', 'auth'])]
class AuthAction
{
    use AsAction;

    public static function route(Router $router): void
    {
        $router->post('/dashboard', static::class);
    }

    public function handle()
    {
    }
}
