<?php

namespace Ignaciocastro0713\CqbusMediator\Traits;

use Illuminate\Routing\Router;

/**
 * @method handle()
 * @method route(Router $router)
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
