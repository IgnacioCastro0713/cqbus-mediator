<?php

declare(strict_types=1);

namespace Ignaciocastro0713\CqbusMediator\Attributes;

use Attribute;
use Ignaciocastro0713\CqbusMediator\Contracts\RouteModifier;

#[Attribute(Attribute::TARGET_CLASS)]
readonly class Middleware implements RouteModifier
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

    public function modifyRoute(array &$options): void
    {
        $options['middleware'] = array_merge((array) ($options['middleware'] ?? []), $this->middleware);
    }
}
