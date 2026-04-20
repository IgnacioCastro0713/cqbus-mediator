<?php

declare(strict_types=1);

namespace Ignaciocastro0713\CqbusMediator\Support;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;

/**
 * Typed result bag returned by Mediator::publish().
 *
 * Implements ArrayAccess, Countable, and IteratorAggregate so that existing
 * code using foreach, count(), and array-key access continues to work unchanged.
 *
 * @implements ArrayAccess<string, mixed>
 * @implements IteratorAggregate<string, mixed>
 */
final class PublishResults implements ArrayAccess, Countable, IteratorAggregate
{
    /** @param array<string, mixed> $results */
    public function __construct(private array $results = [])
    {
    }

    /** @return array<string, mixed> */
    public function all(): array
    {
        return $this->results;
    }

    public function get(string $handlerClass): mixed
    {
        return $this->results[$handlerClass] ?? null;
    }

    public function isEmpty(): bool
    {
        return empty($this->results);
    }

    public function count(): int
    {
        return count($this->results);
    }

    /** @return array<int, string> */
    public function handlerClasses(): array
    {
        return array_keys($this->results);
    }

    /** @return ArrayIterator<string, mixed> */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->results);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->results[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->results[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->results[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->results[$offset]);
    }
}
