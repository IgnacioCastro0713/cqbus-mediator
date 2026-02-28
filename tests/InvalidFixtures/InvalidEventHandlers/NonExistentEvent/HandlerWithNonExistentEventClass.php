<?php

namespace Tests\InvalidFixtures\InvalidEventHandlers\NonExistentEvent;

use Ignaciocastro0713\CqbusMediator\Attributes\Handlers\EventHandler;

#[EventHandler('NonExistentEvent')]
class HandlerWithNonExistentEventClass
{
    public function handle($event): void
    {
    }
}
