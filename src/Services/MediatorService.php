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
        $this->registerPipelines();
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
        $handlers = Discover::in(...$handlerPaths)->classes()->withAttribute(RequestHandler::class)->get();

        foreach ($handlers as $handler) {
            $this->registerHandler($handler);
        }
    }


    /**
     * Registers a given class as a request handler if it has the RequestHandler attribute
     * and its associated request class is not already registered.
     *
     * @param DiscoveredStructure|string $handler The fully qualified class name of the potential handler.
     * @throws ReflectionException
     */
    private function registerHandler(DiscoveredStructure|string $handler): void
    {
        $reflection = new ReflectionClass($handler);
        $requestHandlerAttribute = $reflection->getAttributes(RequestHandler::class)[0]->newInstance();
        $request = $requestHandlerAttribute->requestClass;

        if (!isset($this->handlers[$request])) {
            $this->handlers[$request] = $handler;
        }
    }

    /**
     * Load global pipeline middleware from config.
     */
    private function registerPipelines(): void
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
