<?php

namespace Ignaciocastro0713\CqbusMediator\Services;

use Ignaciocastro0713\CqbusMediator\Config;
use Ignaciocastro0713\CqbusMediator\Contracts\Mediator;
use Ignaciocastro0713\CqbusMediator\Discovery\DiscoverHandler;
use Ignaciocastro0713\CqbusMediator\Exceptions\HandlerNotFoundException;
use Ignaciocastro0713\CqbusMediator\Exceptions\InvalidHandlerException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\File;
use ReflectionException;

class MediatorService implements Mediator
{
    /** @var array<string, string> */
    private array $handlers = [];

    /**
     * Constructor.
     *
     * @param Application $app
     * @throws ReflectionException
     */
    public function __construct(private readonly Application $app)
    {
        $this->loadHandlers();
    }

    /**
     * Loads handlers from the cache or Scan directories or get dynamically handlers
     * @throws ReflectionException
     */
    private function loadHandlers(): void
    {
        $cachePath = $this->app->bootstrapPath('cache/mediator_handlers.php');
        if (File::exists($cachePath)) {
            $this->handlers = require $cachePath;

            return;
        }

        $handlerPaths = Config::handlerPaths();
        $this->handlers = DiscoverHandler::in(...$handlerPaths)->get();
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
     * @throws BindingResolutionException if the handler cannot be resolved from the container.
     * @throws HandlerNotFoundException
     * @throws InvalidHandlerException
     */
    public function send(object $request): mixed
    {
        $requestClass = get_class($request);
        $handlerToBind = $this->handlers[$requestClass] ?? throw new HandlerNotFoundException($requestClass);
        $handler = $this->app->make($handlerToBind);

        if (! method_exists($handler, 'handle')) {
            throw new InvalidHandlerException("Handler '" . get_class($handler) . "' must have a 'handle' method.");
        }

        $pipelines = Config::pipelines();

        if (! empty($pipelines)) {
            return $this->app->make(Pipeline::class)
                ->send($request)
                ->through($pipelines)
                ->then(fn ($processedRequest) => $handler->handle($processedRequest));
        }

        return $handler->handle($request);
    }
}
