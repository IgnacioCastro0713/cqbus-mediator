<?php

declare(strict_types=1);

namespace Ignaciocastro0713\CqbusMediator\Attributes;

use Attribute;
use Ignaciocastro0713\CqbusMediator\Contracts\RouteModifier;

#[Attribute(Attribute::TARGET_CLASS)]
class WebRoute implements RouteModifier
{
    public function __construct()
    {
    }

    public function modifyRoute(array &$options): void
    {
        $options['middleware'] = array_merge((array) ($options['middleware'] ?? []), ['web']);
    }
}
