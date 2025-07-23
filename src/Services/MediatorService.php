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
     * Attempts to register the handler for the given request class if not already cached.
     *
     * @param string $requestClass
     * @return DiscoveredStructure|string
     */
    private function getOrAddHandler(string $requestClass): DiscoveredStructure|string
    {
        $cacheKey = "mediator_handler_$requestClass";
        return Cache::rememberForever($cacheKey, function () use ($requestClass) {
            $handlerPaths = array_unique(config('mediator.handler_paths', [app_path('Handlers')]));
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

    private function getPipelines(): array
    {
        return config('mediator.pipelines', []);
    }

    /**
     * Send a request through the registered pipelines and then to the handler.
     *
     * @param object $request
     * @return mixed
     * @throws InvalidArgumentException
     * @throws BindingResolutionException
     * @throws ReflectionException
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
