<?php

declare(strict_types=1);

namespace Tests\Fixtures\EventHandlers;

use Ignaciocastro0713\CqbusMediator\Attributes\EventHandler;
use Tests\Fixtures\Events\UserRegisteredEvent;

#[EventHandler(UserRegisteredEvent::class, priority: 10)]
class SendWelcomeEmailHandler
{
    public function handle(UserRegisteredEvent $event): string
    {
        return "welcome_email_sent_to_{$event->email}";
    }
}
