<?php

namespace Ignaciocastro0713\CqbusMediator;

use Ignaciocastro0713\CqbusMediator\Attributes\RequestHandler;
use Ignaciocastro0713\CqbusMediator\Contracts\Mediator;
use Ignaciocastro0713\CqbusMediator\Services\MediatorService;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use Spatie\StructureDiscoverer\Discover;

class MediatorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/mediator.php', 'mediator');

        $this->app->singleton(Mediator::class, MediatorService::class);
    }

    /**
     * Bootstrap any application services.
     * @throws ReflectionException
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/mediator.php' => config_path('mediator.php'),
        ], 'mediator-config');

        $this->registerHandlers();
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
     * Scans handler directories using Spatie\StructureDiscoverer and registers.
     * @throws ReflectionException
     */
    private function registerHandlers(): void
    {
        $handlerPaths = array_unique($this->getHandlerPaths());
        $discoveredHandlers = Discover::in(...$handlerPaths)
            ->classes()
            ->withAttribute(RequestHandler::class)
            ->get();

        foreach ($discoveredHandlers as $handlerClass) {
            try {
                $reflection = new ReflectionClass($handlerClass);

                if (!$reflection->isInstantiable()) {
                    continue;
                }

                $attributes = $reflection->getAttributes(RequestHandler::class);

                if (empty($attributes)) {
                    continue;
                }

                $requestHandlerAttribute = $attributes[0]->newInstance();
                $requestClass = $requestHandlerAttribute->requestClass;

                if (empty($requestClass)) {
                    continue;
                }

                $this->app->bind("mediator.handler.$requestClass", fn ($app) => $app->make($handlerClass));

            } catch (ReflectionException|InvalidArgumentException $e) {
                report($e);
            }
        }

    }
}
