<?php

namespace Ignaciocastro0713\CqbusMediator;

use Ignaciocastro0713\CqbusMediator\Attributes\RequestHandler;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use Spatie\StructureDiscoverer\Discover;

class HandlerDiscovery
{
    /**
     * Retrieves the list of handler paths from the configuration.
     *
     * This function returns an array of directory paths where the mediator
     * should look for handler classes. By default, it uses the
     * application's root directory if no custom paths are configured.
     *
     * @return array The array of handler paths.
     */
    public static function getHandlerPaths(): array
    {
        $defaultPath = app_path();
        $paths = config('mediator.handler_paths') ?? $defaultPath;

        if (! is_array($paths)) {
            $paths = [$paths];
        }

        if (is_array($paths) && empty($paths)) {
            $paths = [$defaultPath];
        }

        return array_unique($paths);
    }

    /**
     * Finds all handler classes with the RequestHandler attribute in the given paths.
     *
     * @param array $paths
     * @return array
     */
    public static function discoverHandlers(array $paths): array
    {
        return Discover::in(...$paths)
            ->classes()
            ->withAttribute(RequestHandler::class)
            ->get();
    }

    /**
     * Extracts the request class name from a handler class using the RequestHandler attribute.
     *
     * @param string $handlerClass
     * @return string|null
     */
    public static function getRequestClass(string $handlerClass): ?string
    {
        try {
            $reflection = new ReflectionClass($handlerClass);

            if (! $reflection->isInstantiable()) {
                return null;
            }

            $attributes = $reflection->getAttributes(RequestHandler::class);
            if (empty($attributes)) {
                return null;
            }

            $requestHandlerAttribute = $attributes[0]->newInstance();
            $requestClass = $requestHandlerAttribute->requestClass;

            if (empty($requestClass)) {
                return null;
            }

            return $requestClass;
        } catch (ReflectionException|InvalidArgumentException $e) {
            report($e);

            return null;
        }
    }
}
