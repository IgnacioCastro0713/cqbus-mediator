<?php

declare(strict_types=1);

namespace Ignaciocastro0713\CqbusMediator\Attributes;

use Attribute;

/**
 * Marks a class as an event handler that will be invoked when the specified event is published.
 * Unlike RequestHandler, multiple classes can handle the same event.
 */
#[Attribute(Attribute::TARGET_CLASS)]
readonly class EventHandler
{
    /**
     * @param class-string $eventClass The event class this handler responds to
     * @param int $priority Higher priority handlers are executed first (default: 0)
     */
    public function __construct(
        public string $eventClass,
        public int $priority = 0
    ) {
    }
}
