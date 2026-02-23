<?php

namespace Tests\InvalidFixtures\InvalidHandlers\EmptyRequest;

use Ignaciocastro0713\CqbusMediator\Attributes\RequestHandler;

#[RequestHandler('')]
class HandlerWithEmptyRequestClass
{
    public function handle($request): void
    {
    }
}
