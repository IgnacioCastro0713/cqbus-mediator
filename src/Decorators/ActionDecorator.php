<?php

declare(strict_types=1);

namespace Ignaciocastro0713\CqbusMediator\Decorators;

use Ignaciocastro0713\CqbusMediator\Constants\MediatorConstants;
use Ignaciocastro0713\CqbusMediator\Exceptions\InvalidActionException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;

class ActionDecorator
{
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
        $method = method_exists($this->action, MediatorConstants::HANDLE_METHOD)
            ? MediatorConstants::HANDLE_METHOD
            : throw new InvalidActionException($this->action, MediatorConstants::HANDLE_METHOD);

        $request = $this->container->make(Request::class);

        $parameters = $this->resolveParameters($request);

        return $this->container->call([$this->action, $method], $parameters);
    }

    /**
     * Resolve parameters from route, query, and body with correct precedence.
     *
     * Security Note: Route parameters are merged last to ensure they strictly override
     * any user input (Query/Body) with the same name. This prevents parameter
     * injection attacks where a user might try to spoof a route ID.
     *
     * @param Request $request
     * @return array<string|int, mixed>
     */
    private function resolveParameters(Request $request): array
    {
        /** @var array<string|int, mixed> $query */
        $query = $request->query();

        /** @var array<string|int, mixed> $post */
        $post = $request->post();

        return array_merge(
            $query,
            $post,
            $this->route->parameters()
        );
    }
}
