<?php

namespace Tests\Fixtures;

use Ignaciocastro0713\CqbusMediator\Attributes\Routing\Api;
use Ignaciocastro0713\CqbusMediator\Attributes\Routing\Prefix;
use Ignaciocastro0713\CqbusMediator\Traits\AsAction;
use Illuminate\Routing\Router;

#[Api]
#[Prefix('v2/users')]
class ApiWithCustomPrefixAction
{
    use AsAction;

    public static function route(Router $router): void
    {
        $router->get('/test', self::class);
    }

    public function handle(): string
    {
        return 'api-v2-users-response';
    }
}
