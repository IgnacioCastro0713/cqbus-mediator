<?php

namespace Ignaciocastro0713\CqbusMediator\Services;

use Ignaciocastro0713\CqbusMediator\Contracts\Mediator;
use Ignaciocastro0713\CqbusMediator\Attributes\RequestHandler;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Pipeline\Pipeline;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use Spatie\StructureDiscoverer\Data\DiscoveredStructure;
use Spatie\StructureDiscoverer\Discover;

class MediatorService implements Mediator
{
    /**
     * Array mapping request classes to their handler classes.
     *
     * @var array
     */
    private array $handlers = [];

    /**
     * Array of pipelines classes.
     *
     * @var array
     */
    private array $pipelines = [];

    /**
     * Constructor.
     * Registers handlers and global pipelines.
     *
     * @param Container $container
     * @throws ReflectionException
     */
    public function __construct(private readonly Container $container)
    {
        $this->registerHandlers();
        $this->registerGlobalPipelines();
    }

    /**
     * Discover and register request handlers using attributes.
     * For each request class, only the first discovered handler is registered.
     *
     * @throws ReflectionException
     */
    private function registerHandlers(): void
    {
        $handlerPaths = array_unique(config('mediator.handler_paths', [app_path('Handlers')]));

        foreach ($handlerPaths as $handlerPath) {
            $this->discoverAndRegisterHandlersInPath($handlerPath);
        }
    }

    /**
     * Discovers and registers request handlers located within a given path.
     *
     * @param string $handlerPath The file system path to scan for handler classes.
     * @throws ReflectionException
     */
    private function discoverAndRegisterHandlersInPath(string $handlerPath): void
    {
        foreach (Discover::in($handlerPath)->classes()->get() as $handlerClass) {
            $this->registerHandlerIfApplicable($handlerClass);
        }
    }

    /**
     * Registers a given class as a request handler if it has the RequestHandler attribute
     * and its associated request class is not already registered.
     *
     * @param DiscoveredStructure|string $handlerClass The fully qualified class name of the potential handler.
     * @throws ReflectionException
     */
    private function registerHandlerIfApplicable(DiscoveredStructure|string $handlerClass): void
    {
        $reflection = new ReflectionClass($handlerClass);

        foreach ($reflection->getAttributes(RequestHandler::class) as $attribute) {
            $requestHandlerAttribute = $attribute->newInstance();
            $requestClass = $requestHandlerAttribute->requestClass;

            if (!isset($this->handlers[$requestClass])) {
                $this->handlers[$requestClass] = $handlerClass;
            }
        }
    }

    /**
     * Load global pipeline middleware from config.
     */
    private function registerGlobalPipelines(): void
    {
        $this->pipelines = config('mediator.pipelines', []);
    }

    /**
     * Send a request through the register pipelines and then to the handler.
     *
     * @param object $request
     * @return mixed
     * @throws InvalidArgumentException
     * @throws BindingResolutionException
     */
    public function send(object $request): mixed
    {
        $requestClass = get_class($request);
        $handlerClass = $this->handlers[$requestClass] ?? null;

        if (!$handlerClass) {
            throw new InvalidArgumentException("No handler registered for request: $requestClass");
        }

        $handler = $this->container->make($handlerClass);

        if (!method_exists($handler, 'handle')) {
            throw new InvalidArgumentException("Handler '$handlerClass' must have a 'handle' method.");
        }

        if (!empty($this->pipelines)) {
            return app(Pipeline::class)
                ->send($request)
                ->through($this->pipelines)
                ->then(fn($request) => $handler->handle($request));
        }

        return $handler->handle($request);
    }
}
