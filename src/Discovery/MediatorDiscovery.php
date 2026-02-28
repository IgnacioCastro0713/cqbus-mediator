<?php

declare(strict_types=1);

namespace Ignaciocastro0713\CqbusMediator\Discovery;

use Ignaciocastro0713\CqbusMediator\Attributes\Handlers\Notification;
use Ignaciocastro0713\CqbusMediator\Attributes\Handlers\RequestHandler;
use Ignaciocastro0713\CqbusMediator\Constants\MediatorConstants;
use Ignaciocastro0713\CqbusMediator\Exceptions\InvalidRequestClassException;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use Spatie\StructureDiscoverer\Data\DiscoveredAttribute;
use Spatie\StructureDiscoverer\Data\DiscoveredClass;
use Spatie\StructureDiscoverer\Data\DiscoveredStructure;
use Spatie\StructureDiscoverer\Discover;

final class MediatorDiscovery
{
    /** @var array<string, array{handlers: array<string, string>, notifications: array<string, array<array{handler: string, priority: int}>>, actions: array<int|string, mixed>}> */
    private static array $cache = [];

    /**
     * Perform a single-pass discovery over the given directories and classify the found structures.
     *
     * @param array<string> $directories
     * @return array{handlers: array<string, string>, notifications: array<string, array<array{handler: string, priority: int}>>, actions: array<int|string, mixed>}
     * @throws InvalidRequestClassException
     */
    public static function discover(array $directories): array
    {
        $cacheKey = implode('|', $directories);

        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        $discovered = [
            'handlers' => [],
            'notifications' => [],
            'actions' => [],
        ];

        // Single pass AST scanning to pre-filter viable classes
        $structures = Discover::in(...$directories)
            ->classes()
            ->custom(function (DiscoveredStructure $structure) {
                if (! $structure instanceof DiscoveredClass) {
                    return false;
                }

                if ($structure->isAbstract) {
                    return false;
                }

                $attributes = collect($structure->attributes);

                $hasRequestHandler = $attributes->contains(
                    fn (DiscoveredAttribute $attr) => $attr->class === MediatorConstants::ATTRIBUTE_REQUEST_HANDLER
                );

                if ($hasRequestHandler) {
                    return true;
                }

                $hasEventHandler = $attributes->contains(
                    fn (DiscoveredAttribute $attr) => $attr->class === MediatorConstants::ATTRIBUTE_NOTIFICATION
                );

                if ($hasEventHandler) {
                    return true;
                }

                return $attributes->contains(
                    fn (DiscoveredAttribute $attr) => $attr->class === MediatorConstants::ATTRIBUTE_API_ROUTE || $attr->class === MediatorConstants::ATTRIBUTE_WEB_ROUTE
                );
            })
            ->get();

        foreach ($structures as $structure) {
            $className = is_string($structure) ? $structure : $structure->getFcqn();

            if (! class_exists($className)) {
                continue;
            }

            try {
                $reflection = new ReflectionClass($className);


                if (! $reflection->isInstantiable()) {
                    continue;
                }

                self::discoverHandlers($reflection, $className, $discovered);
                self::discoverNotifications($reflection, $className, $discovered);
                self::discoverActions($className, $discovered);

            } catch (ReflectionException | InvalidArgumentException) {
                continue;
            }
        }

        // Sort EventHandlers by priority
        foreach ($discovered['notifications'] as $eventClass => $handlers) {
            usort($handlers, fn (array $a, array $b): int => $b['priority'] <=> $a['priority']);
            $discovered['notifications'][$eventClass] = $handlers;
        }

        return self::$cache[$cacheKey] = $discovered;
    }

    /**
     * @param ReflectionClass<object> $reflection
     * @param string $className
     * @param array{handlers: array<string, string>, notifications: array<string, array<array{handler: string, priority: int}>>, actions: array<int|string, mixed>} &$discovered
     * @throws InvalidRequestClassException
     */
    private static function discoverHandlers(ReflectionClass $reflection, string $className, array &$discovered): void
    {
        $attributes = $reflection->getAttributes(MediatorConstants::ATTRIBUTE_REQUEST_HANDLER);
        if (! empty($attributes)) {
            /** @var RequestHandler $attr */
            $attr = $attributes[0]->newInstance();
            $requestClass = $attr->requestClass;

            if (! empty($requestClass)) {
                self::ensureTargetClassExists($requestClass, $className);
                $discovered['handlers'][$requestClass] = $className;
            }
        }
    }

    /**
     * @param ReflectionClass<object> $reflection
     * @param string $className
     * @param array{handlers: array<string, string>, notifications: array<string, array<array{handler: string, priority: int}>>, actions: array<int|string, mixed>} &$discovered
     * @throws InvalidRequestClassException
     */
    private static function discoverNotifications(ReflectionClass $reflection, string $className, array &$discovered): void
    {
        $attributes = $reflection->getAttributes(MediatorConstants::ATTRIBUTE_NOTIFICATION);
        if (! empty($attributes)) {
            /** @var Notification $attr */
            $attr = $attributes[0]->newInstance();
            $eventClass = $attr->eventClass;

            if (! empty($eventClass)) {
                self::ensureTargetClassExists($eventClass, $className);
                $discovered['notifications'][$eventClass][] = [
                    'handler' => $className,
                    'priority' => $attr->priority,
                ];
            }
        }
    }

    /**
     * @param string $className
     * @param array{handlers: array<string, string>, notifications: array<string, array<array{handler: string, priority: int}>>, actions: array<int|string, mixed>} &$discovered
     */
    private static function discoverActions(string $className, array &$discovered): void
    {
        if (in_array(MediatorConstants::ACTION_TRAIT, class_uses_recursive($className), true)
            && method_exists($className, MediatorConstants::ROUTE_METHOD)
            && (new ReflectionMethod($className, MediatorConstants::ROUTE_METHOD))->isStatic()
        ) {
            $discovered['actions'][] = $className;
        }
    }

    /**
     * @throws InvalidRequestClassException
     */
    private static function ensureTargetClassExists(string $targetClass, string $handlerClass): void
    {
        if (! class_exists($targetClass)) {
            throw new InvalidRequestClassException($targetClass, $handlerClass);
        }
    }

    public static function clearCache(): void
    {
        self::$cache = [];
    }
}
