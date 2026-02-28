<?php

namespace Tests\NonAutoloadedFixtures\ReflectionFailure;

use Ignaciocastro0713\CqbusMediator\Attributes\Handlers\RequestHandler;

#[RequestHandler('SomeRequest')]
class TestReflectionHandler
{
    public function handle($request)
    {
    }
}
