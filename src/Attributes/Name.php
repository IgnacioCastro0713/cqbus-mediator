<?php

declare(strict_types=1);

namespace Ignaciocastro0713\CqbusMediator\Attributes;

use Attribute;
use Ignaciocastro0713\CqbusMediator\Contracts\RouteModifier;

#[Attribute(Attribute::TARGET_CLASS)]
readonly class Name implements RouteModifier
{
    public function __construct(
        public string $name
    ) {
    }

    public function modifyRoute(array &$options): void
    {
        $options['as'] = $this->name;
    }
}
