<?php

use Ignaciocastro0713\CqbusMediator\Facades\Mediator;
use Tests\Fixtures\Events\UserRegisteredEvent;
use Tests\Fixtures\Handlers\BasicRequest;

it('can fake the mediator and assert requests were sent', function () {
    Mediator::fake();

    $request = new BasicRequest('John Doe');
    Mediator::send($request);

    Mediator::assertSent(BasicRequest::class);
    Mediator::assertSent(function (BasicRequest $req) {
        return $req->name === 'John Doe';
    });

    Mediator::assertNotSent(UserRegisteredEvent::class);
});

it('can fake the mediator and assert events were published', function () {
    Mediator::fake();

    $event = new UserRegisteredEvent('1', 'john@example.com');
    Mediator::publish($event);

    Mediator::assertPublished(UserRegisteredEvent::class);
    Mediator::assertPublished(function (UserRegisteredEvent $e) {
        return $e->userId === '1';
    });

    Mediator::assertNotPublished(BasicRequest::class);
});

it('can assert nothing was sent or published', function () {
    Mediator::fake();

    Mediator::assertNothingSent();
    Mediator::assertNothingPublished();
});

it('swaps the instance in the container when faked', function () {
    $original = app(\Ignaciocastro0713\CqbusMediator\Contracts\Mediator::class);

    Mediator::fake();

    $faked = app(\Ignaciocastro0713\CqbusMediator\Contracts\Mediator::class);

    expect($faked)->toBeInstanceOf(\Ignaciocastro0713\CqbusMediator\Support\MediatorFake::class)
        ->and($faked)->not->toBe($original);
});
