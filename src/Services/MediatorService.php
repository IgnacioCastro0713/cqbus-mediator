<?php

declare(strict_types=1);

namespace Ignaciocastro0713\CqbusMediator\Services;

use Ignaciocastro0713\CqbusMediator\Attributes\Pipeline as PipelineAttribute;
use Ignaciocastro0713\CqbusMediator\Attributes\SkipGlobalPipelines;
use Ignaciocastro0713\CqbusMediator\Constants\MediatorConstants;
use Ignaciocastro0713\CqbusMediator\Contracts\Mediator;
use Ignaciocastro0713\CqbusMediator\Discovery\EventHandlerDiscovery;
use Ignaciocastro0713\CqbusMediator\Discovery\HandlerDiscovery;
use Ignaciocastro0713\CqbusMediator\Exceptions\HandlerNotFoundException;
use Ignaciocastro0713\CqbusMediator\Exceptions\InvalidHandlerException;
use Ignaciocastro0713\CqbusMediator\MediatorConfig;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\File;
use ReflectionClass;
use ReflectionException;

class MediatorService implements Mediator
{
    /** @var array<string, string> Maps request class names to handler class names. */
    private array $handlers = [];

    /** @var array<string, array<array{handler: string, priority: int}>> Maps event class names to event handlers. */
    private array $eventHandlers = [];

    /** @var array<class-string> */
    private array $globalPipelines;

    /**
     * Cache for resolved pipelines per handler class.
     * Avoids repeated Reflection calls for the same handler.
     *
     * @var array<class-string, array<class-string>>
     */
    private array $pipelinesCache = [];

    /**
     * MediatorService constructor.
     *
     * @param Application $app
     * @throws ReflectionException
     */
    public function __construct(private readonly Application $app)
    {
        $this->loadHandlers();
        $this->loadEventHandlers();
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

        $this->handlers = HandlerDiscovery::in(...MediatorConfig::handlerPaths())->get();
    }

    /**
     * Loads event handlers from the unified cache file if available, otherwise scans directories.
     */
    private function loadEventHandlers(): void
    {
        $cacheHandlersPath = $this->app->bootstrapPath('cache/mediator.php');

        if (File::exists($cacheHandlersPath)) {
            $cached = require $cacheHandlersPath;
            $this->eventHandlers = $cached['event_handlers'] ?? [];

            return;
        }

        $this->eventHandlers = EventHandlerDiscovery::in(...MediatorConfig::handlerPaths())->get();
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

        /** @var class-string $handlerClass */
        $handlerClass = $this->handlers[$requestClass] ?? throw new HandlerNotFoundException($requestClass);

        /** @var object $handler */
        $handler = $this->app->make($handlerClass);

        if (! method_exists($handler, MediatorConstants::HANDLE_METHOD)) {
            throw new InvalidHandlerException($handler);
        }

        $pipelines = $this->resolvePipelines($handlerClass);

        return empty($pipelines)
            ? $handler->{MediatorConstants::HANDLE_METHOD}($request)
            : $this->app->make(Pipeline::class)
                ->send($request)
                ->through($pipelines)
                ->then(fn (object $processedRequest): mixed => $handler->{MediatorConstants::HANDLE_METHOD}($processedRequest));
    }

    /**
     * Resolve all pipelines for a handler (global + handler-level).
     * If the handler has #[SkipGlobalPipelines], only handler-level pipelines are used.
     * Results are cached to avoid repeated Reflection calls.
     *
     * @param class-string $handlerClass
     * @return array<class-string>
     */
    private function resolvePipelines(string $handlerClass): array
    {
        if (isset($this->pipelinesCache[$handlerClass])) {
            return $this->pipelinesCache[$handlerClass];
        }

        $handlerPipelines = $this->getHandlerPipelines($handlerClass);

        $pipelines = $this->shouldSkipGlobalPipelines($handlerClass)
            ? $handlerPipelines
            : array_merge($this->globalPipelines, $handlerPipelines);

        return $this->pipelinesCache[$handlerClass] = $pipelines;
    }

    /**
     * Check if the handler has the SkipGlobalPipelines attribute.
     *
     * @param class-string $handlerClass
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
     * @param class-string $handlerClass
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

    /**
     * Publish an event to all registered event handlers.
     * Unlike send(), multiple handlers can respond to the same event.
     * Handlers are executed in priority order (higher priority first).
     *
     * @param object $event The event object to publish
     * @return array<string, mixed> Results from all handlers, keyed by handler class name
     * @throws BindingResolutionException
     * @throws InvalidHandlerException
     */
    public function publish(object $event): array
    {
        $eventClass = $event::class;
        $handlers = $this->eventHandlers[$eventClass] ?? [];

        if (empty($handlers)) {
            return [];
        }

        $results = [];

        foreach ($handlers as $handlerInfo) {
            /** @var class-string $handlerClass */
            $handlerClass = $handlerInfo['handler'];

            /** @var object $handler */
            $handler = $this->app->make($handlerClass);

            if (! method_exists($handler, MediatorConstants::HANDLE_METHOD)) {
                throw new InvalidHandlerException($handler);
            }

            $pipelines = $this->resolvePipelines($handlerClass);

            $results[$handlerClass] = empty($pipelines)
                ? $handler->{MediatorConstants::HANDLE_METHOD}($event)
                : $this->app->make(Pipeline::class)
                    ->send($event)
                    ->through($pipelines)
                    ->then(fn (object $processedEvent): mixed => $handler->{MediatorConstants::HANDLE_METHOD}($processedEvent));
        }

        return $results;
    }
}
