<?php

declare(strict_types=1);

namespace Ignaciocastro0713\CqbusMediator\Attributes\Routing;

use Attribute;
use Ignaciocastro0713\CqbusMediator\Contracts\RouteModifier;
use Ignaciocastro0713\CqbusMediator\Routing\RouteOptions;

#[Attribute(Attribute::TARGET_CLASS)]
readonly class Priority implements RouteModifier
{
    /**
     * @param int $priority The registration priority.
     * @param string $group The priority grouping context (e.g. 'users', 'billing').
     */
    public function __construct(public int $priority = 0, public string $group = '')
    {
    }

    public function modifyRoute(RouteOptions $options): void
    {
        $options->priority($this->priority, $this->group);
    }
}
