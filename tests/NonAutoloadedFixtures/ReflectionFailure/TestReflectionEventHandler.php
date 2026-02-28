<?php

namespace Tests\NonAutoloadedFixtures\ReflectionFailure;

use Ignaciocastro0713\CqbusMediator\Attributes\Handlers\EventHandler;

#[EventHandler('SomeEvent')]
class TestReflectionEventHandler
{
    public function handle($event)
    {
    }
}
