<?php

declare(strict_types=1);

namespace Ignaciocastro0713\CqbusMediator\Services;

use Ignaciocastro0713\CqbusMediator\Constants\MediatorConstants;
use Ignaciocastro0713\CqbusMediator\Contracts\Mediator;
use Ignaciocastro0713\CqbusMediator\Discovery\DiscoverHandler;
use Ignaciocastro0713\CqbusMediator\Exceptions\HandlerNotFoundException;
use Ignaciocastro0713\CqbusMediator\Exceptions\InvalidHandlerException;
use Ignaciocastro0713\CqbusMediator\MediatorConfig;
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
    /** @var array<class-string> */
    private array $pipelines;

    /**
     * MediatorService constructor.
     *
     * @param Application $app
     * @throws ReflectionException
     */
    public function __construct(private readonly Application $app)
    {
        $this->loadHandlers();
        $this->pipelines = MediatorConfig::pipelines();
    }

    /**
     * Loads handlers from the unified cache file if available, otherwise scans directories.
     * Use 'php artisan mediator:cache' to generate the cache file for better performance.
     *
     * @throws ReflectionException
     */
    private function loadHandlers(): void
    {
        $cacheHandlersPath = $this->app->bootstrapPath('cache/mediator.php');

        if (File::exists($cacheHandlersPath)) {
            $cached = require $cacheHandlersPath;
            $this->handlers = $cached['handlers'] ?? [];

            return;
        }

        $this->handlers = DiscoverHandler::in(...MediatorConfig::handlerPaths())->get();
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
        $requestClass = $request::class;
        $handlerToBind = $this->handlers[$requestClass] ?? throw new HandlerNotFoundException($requestClass);
        $handler = $this->app->make($handlerToBind);

        if (! method_exists($handler, MediatorConstants::HANDLE_METHOD)) {
            throw new InvalidHandlerException($handler);
        }

        if (! empty($this->pipelines)) {
            $pipeline = $this->app->make(Pipeline::class);

            return $pipeline
                ->send($request)
                ->through($this->pipelines)
                ->then(fn ($processedRequest) => $handler->handle($processedRequest));
        }

        return $handler->handle($request);
    }
}
