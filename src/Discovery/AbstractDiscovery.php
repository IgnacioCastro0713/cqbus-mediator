<?php

declare(strict_types=1);

namespace Ignaciocastro0713\CqbusMediator\Discovery;

use Ignaciocastro0713\CqbusMediator\Exceptions\InvalidRequestClassException;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use Spatie\StructureDiscoverer\Discover;

abstract readonly class AbstractDiscovery
{
    /**
     * @param array<string> $directories
     */
    final public function __construct(
        protected array $directories = []
    ) {
    }

    public static function in(string ...$directories): static
    {
        return new static(directories: $directories);
    }

    /**
     * Common logic to discover classes by a specific attribute.
     * Yields the handler class name and the instantiated attribute.
     *
     * @template T of object
     * @param class-string<T> $attributeClass
     * @return \Generator<string, T>
     */
    protected function discoverByAttribute(string $attributeClass): \Generator
    {
        $discoveredHandlers = Discover::in(...$this->directories)
            ->classes()
            ->withAttribute($attributeClass)
            ->get();

        foreach ($discoveredHandlers as $handlerClass) {
            try {
                /** @var class-string $handlerClassName */
                $handlerClassName = is_string($handlerClass) ? $handlerClass : $handlerClass->getFcqn();
                $reflection = new ReflectionClass($handlerClassName);

                if (! $reflection->isInstantiable()) {
                    continue;
                }

                $attributes = $reflection->getAttributes($attributeClass);
                // @codeCoverageIgnoreStart
                if (empty($attributes)) {
                    continue;
                }
                // @codeCoverageIgnoreEnd

                yield $handlerClassName => $attributes[0]->newInstance();

            } catch (ReflectionException|InvalidArgumentException $e) {
                report($e);

                continue;
            }
        }
    }

    /**
     * @throws InvalidRequestClassException
     */
    protected function ensureTargetClassExists(string $targetClass, string $handlerClass): void
    {
        if (! class_exists($targetClass)) {
            throw new InvalidRequestClassException($targetClass, $handlerClass);
        }
    }
}
