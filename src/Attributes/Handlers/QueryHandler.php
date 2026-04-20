<?php

declare(strict_types=1);

namespace Ignaciocastro0713\CqbusMediator\Attributes\Handlers;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
readonly class QueryHandler extends RequestHandler
{
    // Semantic alias for RequestHandler — use for queries that only read state.
}
