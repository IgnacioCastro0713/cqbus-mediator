<?php

use Ignaciocastro0713\CqbusMediator\Discovery\HandlerDiscovery;
use Ignaciocastro0713\CqbusMediator\Exceptions\InvalidRequestClassException;

it('skips handlers with empty request class', function () {
    $discovery = HandlerDiscovery::in(__DIR__ . '/../../InvalidFixtures/InvalidHandlers/EmptyRequest');
    $handlers = $discovery->get();

    expect($handlers)->not->toHaveKey('');
});

it('throws exception for handlers with non-existent request class', function () {
    $discovery = HandlerDiscovery::in(__DIR__ . '/../../InvalidFixtures/InvalidHandlers/NonExistentRequest');

    expect(fn () => $discovery->get())
        ->toThrow(InvalidRequestClassException::class);
});

it('skips abstract handlers', function () {
    $discovery = HandlerDiscovery::in(__DIR__ . '/../../InvalidFixtures/InvalidHandlers/AbstractHandler');
    $handlers = $discovery->get();

    // AbstractHandler uses BasicRequest::class which exists
    // But the handler is abstract, so it should be skipped.
    expect($handlers)->toBeEmpty();
});
