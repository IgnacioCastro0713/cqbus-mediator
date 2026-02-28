<?php

declare(strict_types=1);

namespace Tests\Fixtures\EventHandlers;

use Ignaciocastro0713\CqbusMediator\Attributes\Handlers\Notification;
use Tests\Fixtures\Events\UserRegisteredEvent;

#[Notification(UserRegisteredEvent::class, priority: 10)]
class SendWelcomeEmailHandler
{
    public function handle(UserRegisteredEvent $event): string
    {
        return "welcome_email_sent_to_{$event->email}";
    }
}
