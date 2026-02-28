<?php

declare(strict_types=1);

namespace Ignaciocastro0713\CqbusMediator\Discovery;

use Ignaciocastro0713\CqbusMediator\Attributes\Handlers\EventHandler;
use Ignaciocastro0713\CqbusMediator\Exceptions\InvalidRequestClassException;

readonly class EventHandlerDiscovery extends AbstractDiscovery
{
    /**
     * Discovers all event handlers and maps them by event class.
     * Multiple handlers can handle the same event.
     *
     * @return array<string, array<array{handler: string, priority: int}>>
     * @throws InvalidRequestClassException
     */
    public function get(): array
    {
        $eventHandlersMap = [];

        foreach ($this->discoverByAttribute(EventHandler::class) as $handlerClass => $attribute) {
            /** @var EventHandler $attribute */
            $eventClass = $attribute->eventClass;

            if (empty($eventClass)) {
                continue;
            }

            $this->ensureTargetClassExists($eventClass, $handlerClass);

            if (! isset($eventHandlersMap[$eventClass])) {
                $eventHandlersMap[$eventClass] = [];
            }

            $eventHandlersMap[$eventClass][] = [
                'handler' => $handlerClass,
                'priority' => $attribute->priority,
            ];
        }

        // Sort handlers by priority (higher first)
        foreach ($eventHandlersMap as $eventClass => $handlers) {
            usort($handlers, fn (array $a, array $b): int => $b['priority'] <=> $a['priority']);
            $eventHandlersMap[$eventClass] = $handlers;
        }

        return $eventHandlersMap;
    }
}
