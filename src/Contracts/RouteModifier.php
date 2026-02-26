<?php

declare(strict_types=1);

namespace Ignaciocastro0713\CqbusMediator\Contracts;

interface RouteModifier
{
    /**
     * Modifies the route options array.
     *
     * @param array<string, mixed> $options
     */
    public function modifyRoute(array &$options): void;
}
