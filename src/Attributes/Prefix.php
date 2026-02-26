<?php

declare(strict_types=1);

namespace Ignaciocastro0713\CqbusMediator\Attributes;

use Attribute;
use Ignaciocastro0713\CqbusMediator\Contracts\RouteModifier;

#[Attribute(Attribute::TARGET_CLASS)]
readonly class Prefix implements RouteModifier
{
    public function __construct(public string $prefix)
    {
    }

    public function modifyRoute(array &$options): void
    {
        $options['prefix'] = trim(($options['prefix'] ?? '') . '/' . $this->prefix, '/');
    }
}
