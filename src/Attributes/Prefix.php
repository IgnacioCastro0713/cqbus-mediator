<?php

declare(strict_types=1);

namespace Ignaciocastro0713\CqbusMediator\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Prefix
{
    public function __construct(public string $prefix)
    {
    }
}
