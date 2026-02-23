<?php

namespace Tests\InvalidFixtures\InvalidHandlers\NonExistentRequest;

use Ignaciocastro0713\CqbusMediator\Attributes\RequestHandler;

#[RequestHandler('NonExistentRequest')]
class HandlerWithNonExistentRequestClass
{
    public function handle($request): void
    {
    }
}
