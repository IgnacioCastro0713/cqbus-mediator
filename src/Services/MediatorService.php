<?php

namespace Ignaciocastro0713\CqbusMediator\Services;

use Ignaciocastro0713\CqbusMediator\Contracts\Mediator;
use Ignaciocastro0713\CqbusMediator\Attributes\RequestHandler;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use Spatie\StructureDiscoverer\Data\DiscoveredStructure;
use Spatie\StructureDiscoverer\Discover;

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
     * Retrieves the list of handler paths from the configuration.
     *
     * This function returns an array of directory paths where the mediator
     * should look for handler classes. By default, it uses the 'Handlers' folder
     * within the application's root directory if no custom paths are configured.
     *
     * @return array The array of handler paths.
     */
    private function getHandlerPaths(): array
    {
        return config('mediator.handler_paths', [app_path('Handlers')]);
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
        return config('mediator.pipelines', []);
    }

    /**
     * Attempts to register the handler for the given request class if not already cached.
     *
     * Looks up the handler for a given request class, caching the result for future calls.
     * Discovers handler classes using the configured handler paths and the RequestHandler attribute.
     * If no handler is found for the request class, returns null.
     *
     * @param string $requestClass The fully-qualified class name of the request.
     * @return DiscoveredStructure|string The handler class or structure, or null if not found.
     */
    private function getOrAddHandler(string $requestClass): DiscoveredStructure|string
    {
        $cacheKey = "mediator_handler_$requestClass";
        return Cache::rememberForever($cacheKey, function () use ($requestClass) {
            $handlerPaths = array_unique($this->getHandlerPaths());
            $handlers = Discover::in(...$handlerPaths)->classes()->withAttribute(RequestHandler::class)->get();
            $current = null;

            foreach ($handlers as $handler) {
                $reflection = new ReflectionClass($handler);
                $requestHandlerAttribute = $reflection->getAttributes(RequestHandler::class)[0]->newInstance();
                $request = $requestHandlerAttribute->requestClass;

                if ($request === $requestClass) {
                    $current = $handler;
                    break;
                }
            }

            return $current;
        });
    }

    /**
     * Send a request through the registered pipelines and then to the handler.
     *
     * This method resolves the handler for the request, optionally passes the request through
     * any configured pipelines, and finally calls the handler's "handle" method.
     *
     * @param object $request The request object to handle.
     * @return mixed The result of the handler's "handle" method.
     * @throws InvalidArgumentException if no handler is found or handler is missing "handle" method.
     * @throws BindingResolutionException if the handler cannot be resolved from the container.
     * @throws ReflectionException if attribute reflection fails.
     */
    public function send(object $request): mixed
    {
        $requestClass = get_class($request);
        $handlerClass = $this->getOrAddHandler($requestClass);

        if (!$handlerClass) {
            throw new InvalidArgumentException("No handler registered for request: $requestClass");
        }

        $handler = $this->container->make($handlerClass);

        if (!method_exists($handler, 'handle')) {
            throw new InvalidArgumentException("Handler '$handlerClass' must have a 'handle' method.");
        }

        $pipelines = $this->getPipelines();

        if (!empty($pipelines)) {
            return app(Pipeline::class)
                ->send($request)
                ->through($pipelines)
                ->then(fn($request) => $handler->handle($request));
        }

        return $handler->handle($request);
    }
}
