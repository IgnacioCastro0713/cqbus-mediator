<?php

namespace Tests\NonAutoloadedFixtures\ReflectionFailure;

use Ignaciocastro0713\CqbusMediator\Attributes\Handlers\Notification;

#[Notification('SomeEvent')]
class TestReflectionEventHandler
{
    public function handle($event)
    {
    }
}
