<?php

declare(strict_types=1);

namespace Tests\Fixtures\EventHandlers;

use Ignaciocastro0713\CqbusMediator\Attributes\EventHandler;
use Tests\Fixtures\Events\EventForInvalidHandler;

/**
 * This handler is missing the handle method for testing InvalidHandlerException
 */
#[EventHandler(EventForInvalidHandler::class)]
class EventHandlerWithoutHandleMethod
{
    // Missing handle method intentionally
}
