<?php

declare(strict_types=1);

namespace Tests\Fixtures\EventHandlers;

use Ignaciocastro0713\CqbusMediator\Attributes\Handlers\Notification;
use Tests\Fixtures\Events\EventForInvalidHandler;

/**
 * This handler is missing the handle method for testing InvalidHandlerException
 */
#[Notification(EventForInvalidHandler::class)]
class EventHandlerWithoutHandleMethod
{
    // Missing handle method intentionally
}
