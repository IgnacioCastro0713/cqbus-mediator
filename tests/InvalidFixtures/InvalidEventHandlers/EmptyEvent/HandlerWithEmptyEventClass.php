<?php

namespace Tests\InvalidFixtures\InvalidEventHandlers\EmptyEvent;

use Ignaciocastro0713\CqbusMediator\Attributes\Handlers\Notification;

#[Notification('')]
class HandlerWithEmptyEventClass
{
    public function handle($event): void
    {
    }
}
