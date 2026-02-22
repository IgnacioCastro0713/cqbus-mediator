<?php

declare(strict_types=1);

namespace Tests\Fixtures\EventHandlers;

use Ignaciocastro0713\CqbusMediator\Attributes\EventHandler;
use Tests\Fixtures\Events\UserRegisteredEvent;

#[EventHandler(UserRegisteredEvent::class, priority: 1)]
class LogUserRegistrationHandler
{
    public function handle(UserRegisteredEvent $event): string
    {
        return "logged_registration_{$event->userId}";
    }
}
