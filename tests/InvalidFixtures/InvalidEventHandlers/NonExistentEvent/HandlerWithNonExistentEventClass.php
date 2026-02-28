<?php

namespace Tests\InvalidFixtures\InvalidEventHandlers\NonExistentEvent;

use Ignaciocastro0713\CqbusMediator\Attributes\Handlers\Notification;

#[Notification('NonExistentEvent')]
class HandlerWithNonExistentEventClass
{
    public function handle($event): void
    {
    }
}
