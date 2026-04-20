<?php

declare(strict_types=1);

namespace Ignaciocastro0713\CqbusMediator\Contracts;

use Ignaciocastro0713\CqbusMediator\Support\PublishResults;

interface Mediator
{
    /**
     * Send a request to its single registered handler.
     *
     * @param object $request The request object to handle
     * @return mixed The result of the handler
     */
    public function send(object $request): mixed;

    /**
     * Publish an event to all registered notifications.
     * Unlike send(), multiple handlers can respond to the same event.
     *
     * @param object $event The event object to publish
     * @return PublishResults Results from all handlers, keyed by handler class name
     */
    public function publish(object $event): PublishResults;
}
