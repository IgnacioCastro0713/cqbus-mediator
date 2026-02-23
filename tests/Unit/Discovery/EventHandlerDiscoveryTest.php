<?php

use Ignaciocastro0713\CqbusMediator\Discovery\EventHandlerDiscovery;
use Ignaciocastro0713\CqbusMediator\Exceptions\InvalidRequestClassException;

it('skips event handlers with empty event class', function () {
    $discovery = EventHandlerDiscovery::in(__DIR__ . '/../../InvalidFixtures/InvalidEventHandlers/EmptyEvent');
    $handlers = $discovery->get();

    expect($handlers)->not->toHaveKey('');
});

it('throws exception for event handlers with non-existent event class', function () {
    $discovery = EventHandlerDiscovery::in(__DIR__ . '/../../InvalidFixtures/InvalidEventHandlers/NonExistentEvent');

    expect(fn () => $discovery->get())
        ->toThrow(InvalidRequestClassException::class);
});

it('skips abstract event handlers', function () {
    $discovery = EventHandlerDiscovery::in(__DIR__ . '/../../InvalidFixtures/InvalidEventHandlers/AbstractHandler');
    $handlers = $discovery->get();

    // AbstractEventHandler uses UserRegisteredEvent::class which exists
    // But the handler is abstract, so it should be skipped.
    expect($handlers)->toBeEmpty();
});
