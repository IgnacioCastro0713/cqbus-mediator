<?php

namespace Tests\NonAutoloadedFixtures\ReflectionFailure;

use Ignaciocastro0713\CqbusMediator\Attributes\EventHandler;

#[EventHandler('SomeEvent')]
class TestReflectionEventHandler
{
    public function handle($event)
    {
    }
}
