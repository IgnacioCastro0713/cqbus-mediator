<?php

declare(strict_types=1);

namespace Tests\Fixtures\Events;

class UserRegisteredEvent
{
    public function __construct(
        public readonly string $userId,
        public readonly string $email
    ) {
    }
}
