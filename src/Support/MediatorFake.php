<?php

declare(strict_types=1);

namespace Ignaciocastro0713\CqbusMediator\Support;

use Ignaciocastro0713\CqbusMediator\Contracts\Mediator;
use Illuminate\Support\Collection;
use Ignaciocastro0713\CqbusMediator\Support\PublishResults;
use PHPUnit\Framework\Assert as PHPUnit;

class MediatorFake implements Mediator
{
    /** @var array<object> List of requests sent through the mediator. */
    private array $sent = [];

    /** @var array<object> List of events published through the mediator. */
    private array $published = [];

    /**
     * Fake the send method by just recording the request.
     */
    public function send(object $request): mixed
    {
        $this->sent[] = $request;

        return null;
    }

    /**
     * Fake the publish method by just recording the event.
     */
    public function publish(object $event): PublishResults
    {
        $this->published[] = $event;

        return new PublishResults();
    }

    /**
     * Assert if a request was sent based on a truth test.
     *
     * @param class-string|callable $request
     */
    public function assertSent(string|callable $request): void
    {
        PHPUnit::assertTrue(
            $this->sent($request)->count() > 0,
            "The expected [{$this->getRequestName($request)}] request was not sent."
        );
    }

    /**
     * Assert if a request was NOT sent based on a truth test.
     *
     * @param class-string|callable $request
     */
    public function assertNotSent(string|callable $request): void
    {
        PHPUnit::assertCount(
            0,
            $this->sent($request),
            "The unexpected [{$this->getRequestName($request)}] request was sent."
        );
    }

    /**
     * Assert if an event was published based on a truth test.
     *
     * @param class-string|callable $event
     */
    public function assertPublished(string|callable $event): void
    {
        PHPUnit::assertTrue(
            $this->published($event)->count() > 0,
            "The expected [{$this->getRequestName($event)}] event was not published."
        );
    }

    /**
     * Assert if an event was NOT published based on a truth test.
     *
     * @param class-string|callable $event
     */
    public function assertNotPublished(string|callable $event): void
    {
        PHPUnit::assertCount(
            0,
            $this->published($event),
            "The unexpected [{$this->getRequestName($event)}] event was published."
        );
    }

    /**
     * Assert that no requests were sent.
     */
    public function assertNothingSent(): void
    {
        PHPUnit::assertEmpty($this->sent, 'Requests were sent unexpectedly.');
    }

    /**
     * Assert that no events were published.
     */
    public function assertNothingPublished(): void
    {
        PHPUnit::assertEmpty($this->published, 'Events were published unexpectedly.');
    }

    /**
     * Get all of the requests matching a truth test.
     *
     * @param class-string|callable $request
     * @return Collection<int, object>
     */
    public function sent(string|callable $request): Collection
    {
        return collect($this->sent)->filter(
            fn (object $item) => is_callable($request) ? $request($item) : $item instanceof $request
        );
    }

    /**
     * Get all of the events matching a truth test.
     *
     * @param class-string|callable $event
     * @return Collection<int, object>
     */
    public function published(string|callable $event): Collection
    {
        return collect($this->published)->filter(
            fn (object $item) => is_callable($event) ? $event($item) : $item instanceof $event
        );
    }

    /**
     * Get the name of the request/event for error messages.
     *
     * @param class-string|callable $request
     */
    private function getRequestName(string|callable $request): string
    {
        return is_string($request) ? $request : 'callback';
    }
}
