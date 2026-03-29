<?php

namespace Tests\Fixtures;

use Ignaciocastro0713\CqbusMediator\Attributes\Routing\Api;
use Ignaciocastro0713\CqbusMediator\Traits\AsAction;
use Illuminate\Routing\Router;

#[Api]
class PatternAction
{
    use AsAction;

    public static function route(Router $router): void
    {
        $router->get('pattern/{id}', self::class);
    }

    public function handle(string $id)
    {
        return ['id' => $id];
    }
}
