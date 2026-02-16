<?php

namespace Tests;

use Ignaciocastro0713\CqbusMediator\MediatorServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    /**
     * Fix for "Access to undeclared static property" error in Orchestra Testbench
     * when used with certain versions of Pest/PHPUnit.
     * @var mixed
     */
    public static $latestResponse = null;

    protected function getPackageProviders($app): array
    {
        $app['config']->set('mediator.handler_paths', __DIR__ . '/../Unit');

        return [
            MediatorServiceProvider::class,
        ];
    }
}
