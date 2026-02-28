<?php

declare(strict_types=1);

namespace Tests\InvalidFixtures\InvalidActions;

use Ignaciocastro0713\CqbusMediator\Traits\AsAction;
use Illuminate\Routing\Router;

/**
 * Action without any routing attributes.
 * Used to verify that actions without base routing attributes are excluded from discovery.
 */
class NoRouteAttributeAction
{
    use AsAction;

    public static function route(Router $router): void
    {
        $router->get('/no-attributes-test', static::class);
    }

    public function handle(): array
    {
        return ['status' => 'ok'];
    }
}
