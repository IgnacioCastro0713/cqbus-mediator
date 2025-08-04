<?php

namespace Tests;

use Ignaciocastro0713\CqbusMediator\MediatorServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        $app['config']->set('mediator.handler_paths', [__DIR__ . '/../Unit']);

        return [
            MediatorServiceProvider::class,
        ];
    }
}
