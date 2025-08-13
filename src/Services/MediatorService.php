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
use Spatie\StructureDiscoverer\Data\DiscoveredStructure;

class MediatorService implements Mediator
{
    /** @var array<string, DiscoveredStructure|string> Maps request class names to handler class names. */
    private array $handlers = [];
    private const HANDLE_METHOD = 'handle';

    /**
     * MediatorService constructor.
     *
     * @param Application $app
     * @throws ReflectionException
     */
    public function __construct(private readonly Application $app)
    {
        $this->loadHandlers();
    }

    /**
     * Loads handlers from the cache or Scan directories or get dynamic handlers
     * @throws ReflectionException
     */
    private function loadHandlers(): void
    {
        $cacheHandlersPath = $this->app->bootstrapPath('cache/mediator_handlers.php');
        if (File::exists($cacheHandlersPath)) {
            $this->handlers = require $cacheHandlersPath;

            return;
        }

        $this->handlers = DiscoverHandler::in(...Config::handlerPaths())->get();
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

        if (! method_exists($handler, self::HANDLE_METHOD)) {
            throw new InvalidHandlerException("Handler '" . get_class($handler) . "' must have a '" . self::HANDLE_METHOD . "' method.");
        }

        $pipelines = Config::pipelines();

        if (! empty($pipelines)) {
            $pipeline = $this->app->make(Pipeline::class);

            return $pipeline
                ->send($request)
                ->through($pipelines)
                ->then(fn ($processedRequest) => $handler->handle($processedRequest));
        }

        return $handler->handle($request);
    }
}
