<?php

use Ignaciocastro0713\CqbusMediator\Attributes\Handlers\CommandHandler;
use Ignaciocastro0713\CqbusMediator\Attributes\Handlers\QueryHandler;
use Ignaciocastro0713\CqbusMediator\Attributes\Handlers\RequestHandler;
use Ignaciocastro0713\CqbusMediator\Contracts\Mediator;
use Tests\Fixtures\Handlers\CreateUserCommand;
use Tests\Fixtures\Handlers\CreateUserCommandHandler;
use Tests\Fixtures\Handlers\GetUserQuery;
use Tests\Fixtures\Handlers\GetUserQueryHandler;

it('dispatches a command via #[CommandHandler]', function () {
    $mediator = app(Mediator::class);
    $result = $mediator->send(new CreateUserCommand('Alice'));

    expect($result)->toBe('user_created:Alice');
});

it('dispatches a query via #[QueryHandler]', function () {
    $mediator = app(Mediator::class);
    $result = $mediator->send(new GetUserQuery('42'));

    expect($result)->toBe('user_found:42');
});

it('CommandHandler is a subclass of RequestHandler', function () {
    expect(CommandHandler::class)->toExtend(RequestHandler::class);
});

it('QueryHandler is a subclass of RequestHandler', function () {
    expect(QueryHandler::class)->toExtend(RequestHandler::class);
});

it('CommandHandler handler is registered in discovery', function () {
    $paths = config('mediator.handler_paths', [app_path()]);
    $paths = is_array($paths) ? $paths : [$paths];
    $discovered = \Ignaciocastro0713\CqbusMediator\Discovery\MediatorDiscovery::discover($paths);

    expect($discovered['handlers'])->toHaveKey(CreateUserCommand::class)
        ->and($discovered['handlers'][CreateUserCommand::class])->toBe(CreateUserCommandHandler::class);
});

it('QueryHandler handler is registered in discovery', function () {
    $paths = config('mediator.handler_paths', [app_path()]);
    $paths = is_array($paths) ? $paths : [$paths];
    $discovered = \Ignaciocastro0713\CqbusMediator\Discovery\MediatorDiscovery::discover($paths);

    expect($discovered['handlers'])->toHaveKey(GetUserQuery::class)
        ->and($discovered['handlers'][GetUserQuery::class])->toBe(GetUserQueryHandler::class);
});
