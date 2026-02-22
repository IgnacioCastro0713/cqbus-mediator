<?php

declare(strict_types=1);

namespace Ignaciocastro0713\CqbusMediator\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
readonly class RequestHandler
{
    public function __construct(public string $requestClass)
    {
    }
}
