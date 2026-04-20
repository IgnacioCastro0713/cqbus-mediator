<?php

declare(strict_types=1);

namespace Tests\Fixtures\Handlers;

class CreateUserCommand
{
    public function __construct(public readonly string $name = 'John') {}
}
