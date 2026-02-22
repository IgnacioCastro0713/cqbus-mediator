<?php

declare(strict_types=1);

namespace Ignaciocastro0713\CqbusMediator\Services;

use Ignaciocastro0713\CqbusMediator\Attributes\Pipeline as PipelineAttribute;
use Ignaciocastro0713\CqbusMediator\Attributes\SkipGlobalPipelines;
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
use ReflectionClass;
use ReflectionException;
use Spatie\StructureDiscoverer\Data\DiscoveredStructure;

class MediatorService implements Mediator
{
    /** @var array<string, DiscoveredStructure|string> Maps request class names to handler class names. */
    private array $handlers = [];
    /** @var array<class-string> */
    private array $globalPipelines;

    /**
     * MediatorService constructor.
     *
     * @param Application $app
     * @throws ReflectionException
     */
    public function __construct(private readonly Application $app)
    {
        $this->loadHandlers();
        $this->globalPipelines = MediatorConfig::pipelines();
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
     * optionally passes the request through any configured pipelines (global + handler-level),
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
        $handlerClass = $this->handlers[$requestClass] ?? throw new HandlerNotFoundException($requestClass);
        $handler = $this->app->make($handlerClass);

        if (! method_exists($handler, MediatorConstants::HANDLE_METHOD)) {
            throw new InvalidHandlerException($handler);
        }

        $pipelines = $this->resolvePipelines($handlerClass);

        if (! empty($pipelines)) {
            return $this->app->make(Pipeline::class)
                ->send($request)
                ->through($pipelines)
                ->then(fn ($processedRequest) => $handler->handle($processedRequest));
        }

        return $handler->handle($request);
    }

    /**
     * Resolve all pipelines for a handler (global + handler-level).
     * If the handler has #[SkipGlobalPipelines], only handler-level pipelines are used.
     *
     * @param string $handlerClass
     * @return array<class-string>
     */
    private function resolvePipelines(string $handlerClass): array
    {
        $handlerPipelines = $this->getHandlerPipelines($handlerClass);

        if ($this->shouldSkipGlobalPipelines($handlerClass)) {
            return $handlerPipelines;
        }

        return array_merge($this->globalPipelines, $handlerPipelines);
    }

    /**
     * Check if the handler has the SkipGlobalPipelines attribute.
     *
     * @param string $handlerClass
     * @return bool
     */
    private function shouldSkipGlobalPipelines(string $handlerClass): bool
    {
        try {
            $reflection = new ReflectionClass($handlerClass);

            return ! empty($reflection->getAttributes(SkipGlobalPipelines::class));
        } catch (ReflectionException) {
            return false;
        }
    }

    /**
     * Extract pipelines from the handler's Pipeline attribute.
     *
     * @param string $handlerClass
     * @return array<class-string>
     */
    private function getHandlerPipelines(string $handlerClass): array
    {
        try {
            $reflection = new ReflectionClass($handlerClass);
            $attributes = $reflection->getAttributes(PipelineAttribute::class);

            if (empty($attributes)) {
                return [];
            }

            return $attributes[0]->newInstance()->pipes;
        } catch (ReflectionException) {
            return [];
        }
    }
}
