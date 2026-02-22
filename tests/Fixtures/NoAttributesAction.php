<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use Ignaciocastro0713\CqbusMediator\Traits\AsAction;
use Illuminate\Routing\Router;

/**
 * Action without any Prefix or Middleware attributes.
 * Used to test the branch where action is registered without a group.
 */
class NoAttributesAction
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

