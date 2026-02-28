<?php

declare(strict_types=1);

namespace Ignaciocastro0713\CqbusMediator\Contracts;

use Ignaciocastro0713\CqbusMediator\Routing\RouteOptions;

interface RouteModifier
{
    /**
     * Modifies the route options via the fluent builder.
     */
    public function modifyRoute(RouteOptions $options): void;
}
