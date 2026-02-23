<?php

use Ignaciocastro0713\CqbusMediator\Discovery\EventHandlerDiscovery;
use Ignaciocastro0713\CqbusMediator\Discovery\HandlerDiscovery;

it('ignores handlers that cannot be reflected (e.g. not autoloaded)', function () {
    // This folder is NOT autoloaded in composer.json
    $path = __DIR__ . '/../../NonAutoloadedFixtures/ReflectionFailure';

    // HandlerDiscovery
    $discovery = HandlerDiscovery::in($path);
    $handlers = $discovery->get();

    // Should be empty because ReflectionClass fails and catch block continues
    expect($handlers)->toBeEmpty();
});

it('ignores event handlers that cannot be reflected (e.g. not autoloaded)', function () {
    // This folder is NOT autoloaded in composer.json
    $path = __DIR__ . '/../../NonAutoloadedFixtures/ReflectionFailure';

    // EventHandlerDiscovery
    $discovery = EventHandlerDiscovery::in($path);
    $handlers = $discovery->get();

    // Should be empty because ReflectionClass fails and catch block continues
    expect($handlers)->toBeEmpty();
});
