<?php

use Ignaciocastro0713\CqbusMediator\Exceptions\MissingRouteAttributeException;

it('can instantiate MissingRouteAttributeException', function () {
    $exception = new MissingRouteAttributeException('App\Actions\Test');

    expect($exception->getMessage())->toContain('App\Actions\Test')
        ->and($exception->actionClass)->toBe('App\Actions\Test');
});
