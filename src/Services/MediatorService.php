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
use Spatie\StructureDiscoverer\Discover;

class MediatorService implements Mediator
{
    /**
     * Array mapping request classes to their handler classes.
     *
     * @var array
     */
    protected array $handlers = [];

    /**
     * Array of pipelines classes.
     *
     * @var array
     */
    protected array $pipelines = [];

    /**
     * Constructor.
     * Registers handlers and global pipelines.
     *
     * @param Container $container
     * @throws ReflectionException
     */
    public function __construct(protected Container $container)
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
    protected function registerHandlers(): void
    {
        $handlerPaths = config('mediator.handler_paths', []);

        foreach ($handlerPaths as $handlerPath) {

            foreach (Discover::in($handlerPath)->classes()->get() as $handlerClass) {

                $reflection = new ReflectionClass($handlerClass);

                foreach ($reflection->getAttributes(RequestHandler::class) as $attribute) {

                    $requestHandlerAttribute = $attribute->newInstance();
                    $requestClass = $requestHandlerAttribute->requestClass;

                    if (isset($this->handlers[$requestClass])) {
                        continue;
                    }

                    $this->handlers[$requestClass] = $handlerClass;
                }
            }
        }
    }

    /**
     * Load global pipeline middleware from config.
     */
    protected function registerGlobalPipelines(): void
    {
        $this->pipelines = config('mediator.pipelines', []);
    }

    /**
     * Send a request through the global pipelines and then to the handler.
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
            throw new InvalidArgumentException("No handler registered for command: $requestClass");
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
