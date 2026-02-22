<?php

declare(strict_types=1);

namespace Ignaciocastro0713\CqbusMediator\Discovery;

use Ignaciocastro0713\CqbusMediator\Attributes\RequestHandler;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use Spatie\StructureDiscoverer\Data\DiscoveredStructure;
use Spatie\StructureDiscoverer\Discover;

readonly class DiscoverHandler
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
     * Extracts the request class name from a handler class using the RequestHandler attribute.
     *
     * @return array<string, DiscoveredStructure|string>
     */
    public function get(): array
    {
        $discoveredHandlers = Discover::in(...$this->directories)
            ->classes()
            ->withAttribute(RequestHandler::class)
            ->get();

        $handlersMap = [];

        foreach ($discoveredHandlers as $handlerClass) {
            try {
                $reflection = new ReflectionClass($handlerClass);

                if (! $reflection->isInstantiable()) {
                    continue;
                }

                $attributes = $reflection->getAttributes(RequestHandler::class);
                if (empty($attributes)) {
                    continue;
                }

                $requestHandlerAttribute = $attributes[0]->newInstance();
                $requestClass = $requestHandlerAttribute->requestClass;

                if (empty($requestClass)) {
                    continue;
                }

                $handlersMap[$requestClass] = $handlerClass;
            } catch (ReflectionException|InvalidArgumentException $e) {
                report($e);

                continue;
            }
        }

        return $handlersMap;
    }
}
