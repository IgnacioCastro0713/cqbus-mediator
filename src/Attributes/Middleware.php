<?php

declare(strict_types=1);

namespace Ignaciocastro0713\CqbusMediator\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Middleware
{
    public array $middleware;

    public function __construct(string|array $middleware)
    {
        $this->middleware = (array) $middleware;
    }
}
