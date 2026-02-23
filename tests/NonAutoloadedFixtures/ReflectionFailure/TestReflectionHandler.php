<?php

namespace Tests\NonAutoloadedFixtures\ReflectionFailure;

use Ignaciocastro0713\CqbusMediator\Attributes\RequestHandler;

#[RequestHandler('SomeRequest')]
class TestReflectionHandler
{
    public function handle($request)
    {
    }
}
