<?php

namespace Tests\Fixtures;

use Ignaciocastro0713\CqbusMediator\Attributes\Routing\Api;
use Ignaciocastro0713\CqbusMediator\Attributes\Routing\Priority;
use Ignaciocastro0713\CqbusMediator\Traits\AsAction;
use Illuminate\Routing\Router;

#[Api]
#[Priority(500, 'context')]
class PriorityArrayAction
{
    use AsAction;

    public static function route(Router $router): void
    {
        $router->get('test-priority-array', self::class);
    }

    public function handle()
    {
        return ['action' => 'array'];
    }
}
