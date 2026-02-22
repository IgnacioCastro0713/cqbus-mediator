<?php

declare(strict_types=1);

namespace Ignaciocastro0713\CqbusMediator\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
readonly class Middleware
{
    /** @var array<string> */
    public array $middleware;

    /**
     * @param string|array<string> $middleware
     */
    public function __construct(string|array $middleware)
    {
        $this->middleware = (array) $middleware;
    }
}
