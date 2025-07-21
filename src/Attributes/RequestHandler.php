<?php

namespace Ignaciocastro0713\CqbusMediator\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class RequestHandler
{
    public function __construct(
        public string $requestClass
    ) {}
}
