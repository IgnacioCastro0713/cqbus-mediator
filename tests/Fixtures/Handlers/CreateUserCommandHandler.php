<?php

declare(strict_types=1);

namespace Tests\Fixtures\Handlers;

use Ignaciocastro0713\CqbusMediator\Attributes\Handlers\CommandHandler;

#[CommandHandler(CreateUserCommand::class)]
class CreateUserCommandHandler
{
    public function handle(CreateUserCommand $command): string
    {
        return 'user_created:' . $command->name;
    }
}
