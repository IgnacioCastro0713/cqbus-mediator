<?php

namespace Tests\InvalidFixtures\InvalidHandlers\EmptyRequest;

use Ignaciocastro0713\CqbusMediator\Attributes\Handlers\RequestHandler;

#[RequestHandler('')]
class HandlerWithEmptyRequestClass
{
    public function handle($request): void
    {
    }
}
