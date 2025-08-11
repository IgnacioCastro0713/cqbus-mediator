<?php

namespace Ignaciocastro0713\CqbusMediator\Traits;

/**
 * @method handle()
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
