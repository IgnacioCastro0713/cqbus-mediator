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
use Ignaciocastro0713\CqbusMediator\Exceptions\InvalidRequestClassException;
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
     * @throws InvalidRequestClassException
     */
    public function __construct(private readonly Application $app)
    {
        $this->loadHandlers();
        $this->loadEventHandlers();
        $this->globalPipelines = MediatorConfig::pipelines();
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

        $handler = $this->resolveHandlerInstance($handlerClass);
        $pipelines = $this->resolvePipelines($handlerClass);

        return $this->executeThroughPipelines($request, $handler, $pipelines);
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

            $handler = $this->resolveHandlerInstance($handlerClass);
            $pipelines = $this->resolvePipelines($handlerClass);

            $results[$handlerClass] = $this->executeThroughPipelines($event, $handler, $pipelines);
        }

        return $results;
    }

    /**
     * Resolve handler instance and verify it has the handle method.
     *
     * @param class-string $handlerClass
     * @return object
     * @throws InvalidHandlerException
     * @throws BindingResolutionException
     */
    private function resolveHandlerInstance(string $handlerClass): object
    {
        $handler = $this->app->make($handlerClass);

        if (! method_exists($handler, MediatorConstants::HANDLE_METHOD)) {
            throw new InvalidHandlerException($handler);
        }

        return $handler;
    }

    /**
     * Run the payload through pipelines and execute the handler.
     *
     * @param object $payload
     * @param object $handler
     * @param array<class-string> $pipelines
     * @return mixed
     * @throws BindingResolutionException
     */
    private function executeThroughPipelines(object $payload, object $handler, array $pipelines): mixed
    {
        if (empty($pipelines)) {
            return $handler->{MediatorConstants::HANDLE_METHOD}($payload);
        }

        return $this->app->make(Pipeline::class)
            ->send($payload)
            ->through($pipelines)
            ->then(fn (object $processedPayload): mixed => $handler->{MediatorConstants::HANDLE_METHOD}($processedPayload));
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
     * Generic loader for handlers (cached or discovered).
     *
     * @param string $cacheKey The key in the cache file (e.g., 'handlers', 'event_handlers')
     * @param class-string $discoveryClass The discovery class to use if cache is missing
     * @return array
     * @throws InvalidRequestClassException
     */
    private function loadDiscovery(string $cacheKey, string $discoveryClass): array
    {
        $cacheHandlersPath = $this->app->bootstrapPath('cache/mediator.php');

        if (File::exists($cacheHandlersPath)) {
            $cached = require $cacheHandlersPath;

            return $cached[$cacheKey] ?? [];
        }

        // @phpstan-ignore-next-line
        return $discoveryClass::in(...MediatorConfig::handlerPaths())->get();
    }

    /**
     * Loads handlers from the unified cache file if available, otherwise scans directories.
     * Use 'php artisan mediator:cache' to generate the cache file for better performance.
     *
     * @throws InvalidRequestClassException
     */
    private function loadHandlers(): void
    {
        $this->handlers = $this->loadDiscovery('handlers', HandlerDiscovery::class);
    }

    /**
     * Loads event handlers from the unified cache file if available, otherwise scans directories.
     * @throws InvalidRequestClassException
     */
    private function loadEventHandlers(): void
    {
        $this->eventHandlers = $this->loadDiscovery('event_handlers', EventHandlerDiscovery::class);
    }
}
