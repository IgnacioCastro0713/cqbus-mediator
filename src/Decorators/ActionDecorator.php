<?php

namespace Ignaciocastro0713\CqbusMediator\Decorators;

use Ignaciocastro0713\CqbusMediator\Exceptions\InvalidHandlerException;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;

class ActionDecorator
{
    protected object $action;
    protected Route $route;
    protected Container $container;

    public function __construct(object $action, Route $route)
    {
        $this->action = $action;
        $this->route = $route;
        $this->container = Container::getInstance();
    }

    /**
     * Call the action's controller method, resolving dependencies and parameters.
     *
     * Precedence for parameter sources:
     *   1. Route parameters (for model binding, path args)
     *   2. Query string (GET parameters)
     *   3. Body (POST/PUT/PATCH parameters)
     * @throws InvalidHandlerException
     * @throws BindingResolutionException
     */
    public function __invoke(): mixed
    {
        $method = method_exists($this->action, 'handle')
            ? 'handle'
            : throw new InvalidHandlerException("Class '" . get_class($this->action) . "' must have a 'handle' method.");

        /** @var Request $request */
        $request = $this->container->make(Request::class);
        $parameters = $this->route->parameters();

        foreach ($request->query() as $key => $value) {
            if (! array_key_exists($key, $parameters)) {
                $parameters[$key] = $value;
            }
        }

        foreach ($request->post() as $key => $value) {
            if (! array_key_exists($key, $parameters)) {
                $parameters[$key] = $value;
            }
        }

        return $this->container->call([$this->action, $method], $parameters);
    }
}
