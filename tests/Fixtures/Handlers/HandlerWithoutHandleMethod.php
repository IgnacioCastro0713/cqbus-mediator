<?php

declare(strict_types=1);

namespace Tests\Fixtures\Handlers;

use Ignaciocastro0713\CqbusMediator\Attributes\RequestHandler;

#[RequestHandler(RequestForInvalidHandler::class)]
class HandlerWithoutHandleMethod
{
    // Missing handle() method intentionally
}
