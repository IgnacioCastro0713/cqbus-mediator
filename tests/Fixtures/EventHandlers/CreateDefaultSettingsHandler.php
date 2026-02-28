<?php

declare(strict_types=1);

namespace Tests\Fixtures\EventHandlers;

use Ignaciocastro0713\CqbusMediator\Attributes\Handlers\Notification;
use Tests\Fixtures\Events\UserRegisteredEvent;

#[Notification(UserRegisteredEvent::class, priority: 5)]
class CreateDefaultSettingsHandler
{
    public function handle(UserRegisteredEvent $event): string
    {
        return "settings_created_for_{$event->userId}";
    }
}
