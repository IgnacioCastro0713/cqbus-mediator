<?php

declare(strict_types=1);

namespace Ignaciocastro0713\CqbusMediator\Attributes\Routing;

use Attribute;
use Ignaciocastro0713\CqbusMediator\Contracts\RouteModifier;
use Ignaciocastro0713\CqbusMediator\Routing\RouteOptions;

#[Attribute(Attribute::TARGET_CLASS)]
readonly class Prefix implements RouteModifier
{
    public function __construct(public string $prefix)
    {
    }

    public function modifyRoute(RouteOptions $options): void
    {
        $options->addPrefix($this->prefix);
    }
}
