<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use Ignaciocastro0713\CqbusMediator\Attributes\ApiRoute;
use Ignaciocastro0713\CqbusMediator\Attributes\Name;
use Ignaciocastro0713\CqbusMediator\Traits\AsAction;
use Illuminate\Routing\Router;

#[ApiRoute]
#[Name('api.named.')]
class NamedAction
{
    use AsAction;

    public function handle(): string
    {
        return 'named response';
    }

    public static function route(Router $router): void
    {
        $router->get('named-route', self::class)->name('index');
    }
}
