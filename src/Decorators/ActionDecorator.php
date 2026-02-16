<?php

namespace Ignaciocastro0713\CqbusMediator\Decorators;

use Ignaciocastro0713\CqbusMediator\Exceptions\InvalidActionException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;

class ActionDecorator
{
    private const HANDLE_METHOD = 'handle';

    public function __construct(
        private readonly object $action,
        private readonly Route  $route,
        private readonly Container $container
    ) {
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
        return array_merge(
            $this->route->parameters(),
            $request->query(),
            $request->post()
        );
    }
}
