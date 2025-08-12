<?php

namespace Ignaciocastro0713\CqbusMediator\Decorators;

use Ignaciocastro0713\CqbusMediator\Exceptions\InvalidActionException;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;

class ActionDecorator
{
    protected Container $container;

    public function __construct(
        private readonly object $action,
        private readonly Route  $route
    ) {
        $this->container = Container::getInstance();
    }

    /**
     * Call the action's controller method, resolving dependencies and parameters.
     *
     * Precedence for parameter sources:
     *   1. Route parameters (for model binding, path args)
     *   2. Query string (GET parameters)
     *   3. Body (POST/PUT/PATCH parameters)
     * @throws InvalidActionException
     * @throws BindingResolutionException
     */
    public function __invoke(): mixed
    {
        $methodName = 'handle';
        $method = method_exists($this->action, $methodName)
            ? $methodName
            : throw new InvalidActionException($this->action, $methodName);

        $request = $this->container->make(Request::class);

        $parameters = $this->route->parameters();

        foreach ($request->query() as $key => $value) {
            $parameters[$key] = $parameters[$key] ?? $value;
        }

        foreach ($request->post() as $key => $value) {
            $parameters[$key] = $parameters[$key] ?? $value;
        }

        return $this->container->call([$this->action, $method], $parameters);
    }
}
