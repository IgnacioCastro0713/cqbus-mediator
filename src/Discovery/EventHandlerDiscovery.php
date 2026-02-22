<?php

declare(strict_types=1);

namespace Ignaciocastro0713\CqbusMediator\Discovery;

use Ignaciocastro0713\CqbusMediator\Attributes\EventHandler;
use Ignaciocastro0713\CqbusMediator\Exceptions\InvalidRequestClassException;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use Spatie\StructureDiscoverer\Discover;

readonly class EventHandlerDiscovery
{
    /**
     * @param array<string> $directories
     */
    public function __construct(
        private array $directories = []
    ) {
    }

    public static function in(string ...$directories): self
    {
        return new self(directories: $directories);
    }

    /**
     * Discovers all event handlers and maps them by event class.
     * Multiple handlers can handle the same event.
     *
     * @return array<string, array<array{handler: string, priority: int}>>
     */
    public function get(): array
    {
        $discoveredHandlers = Discover::in(...$this->directories)
            ->classes()
            ->withAttribute(EventHandler::class)
            ->get();

        $eventHandlersMap = [];

        foreach ($discoveredHandlers as $handlerClass) {
            try {
                /** @var class-string $handlerClassName */
                $handlerClassName = is_string($handlerClass) ? $handlerClass : $handlerClass->getFcqn();
                $reflection = new ReflectionClass($handlerClassName);

                if (! $reflection->isInstantiable()) {
                    continue;
                }

                $attributes = $reflection->getAttributes(EventHandler::class);
                if (empty($attributes)) {
                    continue;
                }

                /** @var EventHandler $eventHandlerAttribute */
                $eventHandlerAttribute = $attributes[0]->newInstance();
                $eventClass = $eventHandlerAttribute->eventClass;

                if (empty($eventClass)) {
                    continue;
                }

                if (! class_exists($eventClass)) {
                    throw new InvalidRequestClassException($eventClass, $handlerClassName);
                }

                if (! isset($eventHandlersMap[$eventClass])) {
                    $eventHandlersMap[$eventClass] = [];
                }

                $eventHandlersMap[$eventClass][] = [
                    'handler' => $handlerClassName,
                    'priority' => $eventHandlerAttribute->priority,
                ];
            } catch (ReflectionException|InvalidArgumentException $e) {
                report($e);

                continue;
            }
        }

        // Sort handlers by priority (higher first)
        foreach ($eventHandlersMap as $eventClass => $handlers) {
            usort($handlers, fn (array $a, array $b): int => $b['priority'] <=> $a['priority']);
            $eventHandlersMap[$eventClass] = $handlers;
        }

        return $eventHandlersMap;
    }
}
