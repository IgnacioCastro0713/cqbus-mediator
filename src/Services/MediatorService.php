<?php

namespace Ignaciocastro0713\CqbusMediator\Services;

use Ignaciocastro0713\CqbusMediator\Contracts\Mediator;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Pipeline\Pipeline;
use InvalidArgumentException;

class MediatorService implements Mediator
{
    /**
     * Constructor.
     *
     * @param Container $container
     */
    public function __construct(private readonly Container $container)
    {
    }

    /**
     * Retrieves the list of pipeline classes from the configuration.
     *
     * This function returns an array of pipeline classes that requests will
     * be passed through before reaching their handler. By default, it returns
     * an empty array if no pipelines are configured.
     *
     * @return array The array of pipeline class names.
     */
    private function getPipelines(): array
    {
        return config('mediator.pipelines') ?? [];
    }

    /**
     * Send a request through the registered pipelines and then to the handler.
     *
     * This method resolves the handler for the request using the container,
     * optionally passes the request through any configured pipelines,
     * and finally calls the handler's "handle" method.
     *
     * @param object $request The request object to handle.
     * @return mixed The result of the handler's "handle" method.
     * @throws InvalidArgumentException if no handler is found or handler is missing "handle" method.
     * @throws BindingResolutionException if the handler cannot be resolved from the container.
     */
    public function send(object $request): mixed
    {
        $requestClass = get_class($request);
        $handlerBindingKey = "mediator.handler.$requestClass";

        if (! $this->container->bound($handlerBindingKey)) {
            throw new InvalidArgumentException("No handler registered for request: $requestClass");
        }

        $handler = $this->container->make($handlerBindingKey);

        if (! method_exists($handler, 'handle')) {
            throw new InvalidArgumentException("Handler '" . get_class($handler) . "' must have a 'handle' method.");
        }

        $pipelines = $this->getPipelines();

        if (! empty($pipelines)) {
            return $this->container->make(Pipeline::class)
                ->send($request)
                ->through($pipelines)
                ->then(fn ($processedRequest) => $handler->handle($processedRequest));
        }

        return $handler->handle($request);
    }
}
