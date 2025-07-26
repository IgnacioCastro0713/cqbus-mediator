<?php

namespace Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Ignaciocastro0713\CqbusMediator\MediatorServiceProvider;
use Illuminate\Foundation\Application;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            MediatorServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app): void
    {

        $packageRoot = realpath(__DIR__ . '/../..');
        $app->instance('path.base', $packageRoot);

        $app['config']->set('mediator.handler_paths', [
            realpath(__DIR__ . '/../Fixtures/Handlers'),
        ]);
        $app['config']->set('mediator.pipelines', []);
    }
}
