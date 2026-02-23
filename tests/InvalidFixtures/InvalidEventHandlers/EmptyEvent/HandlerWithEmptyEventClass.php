<?php

namespace Tests\InvalidFixtures\InvalidEventHandlers\EmptyEvent;

use Ignaciocastro0713\CqbusMediator\Attributes\EventHandler;

#[EventHandler('')]
class HandlerWithEmptyEventClass
{
    public function handle($event): void
    {
    }
}
