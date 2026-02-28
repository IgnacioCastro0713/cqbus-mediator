<?php

declare(strict_types=1);

namespace Ignaciocastro0713\CqbusMediator\Attributes\Routing;

use Attribute;
use Ignaciocastro0713\CqbusMediator\Contracts\RouteModifier;
use Ignaciocastro0713\CqbusMediator\Routing\RouteOptions;

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

    public function modifyRoute(RouteOptions $options): void
    {
        $options->addMiddleware($this->middleware);
    }
}
