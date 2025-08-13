<?php

namespace Ignaciocastro0713\CqbusMediator\Decorators;

use Ignaciocastro0713\CqbusMediator\Exceptions\InvalidActionException;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;

class ActionDecorator
{
    private const HANDLE_METHOD = 'handle';
    private Container $container;

    public function __construct(
        private readonly object $action,
        private readonly Route  $route
    ) {
        $this->container = Container::getInstance();
    }

    /**
     * Call the action's controller method, resolving dependencies and parameters.
     * @throws InvalidActionException
     * @throws BindingResolutionException
     */
    public function __invoke(): mixed
    {
        $method = method_exists($this->action, self::HANDLE_METHOD)
            ? self::HANDLE_METHOD
            : throw new InvalidActionException($this->action, self::HANDLE_METHOD);

        $request = $this->container->make(Request::class);

        $parameters = $this->resolveParameters($request);

        return $this->container->call([$this->action, $method], $parameters);
    }

    /**
     *  Resolve parameters from route, query, and body with correct precedence.
     * @param Request $request
     * @return array<string|int, mixed>
     */
    private function resolveParameters(Request $request): array
    {
        $parameters = $this->route->parameters();

        foreach ($request->query() as $key => $value) {
            $parameters[$key] = $parameters[$key] ?? $value;
        }

        foreach ($request->post() as $key => $value) {
            $parameters[$key] = $parameters[$key] ?? $value;
        }

        return $parameters;
    }
}
