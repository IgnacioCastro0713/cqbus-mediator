<?php

declare(strict_types=1);

namespace Ignaciocastro0713\CqbusMediator\Routing;

use Closure;

/**
 * Fluent builder for configuring Laravel route options.
 *
 * @method self domain(string $domain)
 * @method self withTrashed(bool $withTrashed = true)
 * @method self fallback()
 * @method self scopeBindings()
 * @method self withoutScopedBindings()
 * @method self missing(Closure|string $missing)
 * @method self block()
 * @method self noBlock()
 */
class RouteOptions
{
    /**
     * @param array<string, mixed> $options
     */
    public function __construct(private array $options = [])
    {
    }

    /**
     * Add middleware to the route.
     *
     * @param string|array<string> $middleware
     */
    public function addMiddleware(string|array $middleware): self
    {
        $existing = (array)($this->options['middleware'] ?? []);
        $new = is_array($middleware) ? $middleware : [$middleware];

        $this->options['middleware'] = array_values(array_unique(array_merge($existing, $new)));

        return $this;
    }

    /**
     * Remove middleware from the route.
     *
     * @param string|array<string> $middleware
     */
    public function withoutMiddleware(string|array $middleware): self
    {
        $existing = (array) ($this->options['withoutMiddleware'] ?? []);
        $new = is_array($middleware) ? $middleware : [$middleware];

        $this->options['withoutMiddleware'] = array_values(array_unique(array_merge($existing, $new)));

        return $this;
    }

    /**
     * Add excluded middleware to the route (alias/internal variation of withoutMiddleware).
     *
     * @param string|array<string> $middleware
     */
    public function excludedMiddleware(string|array $middleware): self
    {
        $existing = (array) ($this->options['excluded_middleware'] ?? []);
        $new = is_array($middleware) ? $middleware : [$middleware];

        $this->options['excluded_middleware'] = array_values(array_unique(array_merge($existing, $new)));

        return $this;
    }

    /**
     * Add a prefix to the route.
     */
    public function addPrefix(string $prefix): self
    {
        $current = $this->options['prefix'] ?? '';
        $this->options['prefix'] = trim($current . '/' . trim($prefix, '/'), '/');

        return $this;
    }

    /**
     * Set the name for the route. Appends safely to existing names (e.g., from groups).
     */
    public function name(string $name): self
    {
        $current = rtrim((string)($this->options['as'] ?? ''), '.');
        $this->options['as'] = ($current !== '' ? $current . '.' : '') . ltrim($name, '.');

        return $this;
    }

    /**
     * Alias for name().
     */
    public function as(string $name): self
    {
        return $this->name($name);
    }

    /**
     * Add route constraints.
     *
     * @param array<string, string>|string $name
     * @param string|null $expression
     * @return RouteOptions
     */
    public function where(array|string $name, ?string $expression = null): self
    {
        /** @var array<string, string> $wheres */
        $wheres = $this->options['where'] ?? [];

        if (is_array($name)) {
            $this->options['where'] = array_merge($wheres, $name);
        } else {
            $wheres[$name] = (string)$expression;
            $this->options['where'] = $wheres;
        }

        return $this;
    }

    /**
     * Add route default values.
     *
     * @param string $key
     * @param mixed $value
     * @return RouteOptions
     */
    public function defaults(string $key, mixed $value): self
    {
        $this->options['defaults'] ??= [];
        $this->options['defaults'][$key] = $value;

        return $this;
    }

    /**
     * Check if a route option exists.
     */
    public function has(string $key): bool
    {
        return isset($this->options[$key]);
    }

    /**
     * Get a route option.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->options[$key] ?? $default;
    }

    /**
     * Magic method to handle all other route modifiers natively supported by Laravel.
     *
     * @param array<int, mixed> $parameters
     */
    public function __call(string $method, array $parameters): self
    {
        if (count($parameters) > 0) {
            $this->options[$method] = count($parameters) === 1 ? $parameters[0] : $parameters;
        } else {
            $this->options[$method] = true;
        }

        return $this;
    }

    /**
     * Get the compiled array of options.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->options;
    }
}
