<?php

namespace Tests\InvalidFixtures\InvalidEventHandlers\AbstractHandler;

use Ignaciocastro0713\CqbusMediator\Attributes\Handlers\Notification;
use Tests\Fixtures\Events\UserRegisteredEvent;

#[Notification(UserRegisteredEvent::class)]
abstract class AbstractEventHandler
{
    abstract public function handle(UserRegisteredEvent $event): void;
}
