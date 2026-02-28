<?php

namespace Tests\InvalidFixtures\InvalidEventHandlers\AbstractHandler;

use Ignaciocastro0713\CqbusMediator\Attributes\Handlers\EventHandler;
use Tests\Fixtures\Events\UserRegisteredEvent;

#[EventHandler(UserRegisteredEvent::class)]
abstract class AbstractEventHandler
{
    abstract public function handle(UserRegisteredEvent $event): void;
}
