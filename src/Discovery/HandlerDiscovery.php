<?php

declare(strict_types=1);

namespace Ignaciocastro0713\CqbusMediator\Discovery;

use Ignaciocastro0713\CqbusMediator\Attributes\RequestHandler;
use Ignaciocastro0713\CqbusMediator\Exceptions\InvalidRequestClassException;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use Spatie\StructureDiscoverer\Discover;

readonly class HandlerDiscovery
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
     * @return array<string, string>
     * @throws InvalidRequestClassException
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
                /** @var class-string $handlerClassName */
                $handlerClassName = is_string($handlerClass) ? $handlerClass : $handlerClass->getFcqn();
                $reflection = new ReflectionClass($handlerClassName);

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

                if (! class_exists($requestClass)) {
                    throw new InvalidRequestClassException($requestClass, $handlerClassName);
                }

                $handlersMap[$requestClass] = $handlerClassName;
            } catch (ReflectionException|InvalidArgumentException $e) {
                report($e);

                continue;
            }
        }

        return $handlersMap;
    }
}
