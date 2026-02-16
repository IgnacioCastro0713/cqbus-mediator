<?php

declare(strict_types=1);

namespace Ignaciocastro0713\CqbusMediator\Traits;

use Illuminate\Routing\Router;

/**
 * @method handle()
 * @method static void route(Router $router)
 */
trait AsAction
{
    public function __invoke(mixed ...$arguments)
    {
    }

    /**
     * Enables controller middleware on the action.
     */
    public function getMiddleware(): array
    {
        return [];
    }
}
