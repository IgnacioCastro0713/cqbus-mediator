<?php

namespace Tests\Fixtures;

use Ignaciocastro0713\CqbusMediator\Attributes\Routing\Middleware;
use Ignaciocastro0713\CqbusMediator\Attributes\Routing\Prefix;
use Ignaciocastro0713\CqbusMediator\Attributes\Routing\Web;
use Ignaciocastro0713\CqbusMediator\Traits\AsAction;
use Illuminate\Routing\Router;

#[Web]
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
