<?php

namespace Ignaciocastro0713\CqbusMediator\Providers;

use Ignaciocastro0713\CqbusMediator\Config;
use Ignaciocastro0713\CqbusMediator\Decorators\ActionDecorator;
use Ignaciocastro0713\CqbusMediator\Traits\AsAction;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Spatie\StructureDiscoverer\Discover;

class ActionDecoratorServiceProvider extends ServiceProvider
{
    public function boot(Router $router): void
    {
        $this->registerRoutes($router);

        $this->registerActions($router);
    }

    /**
     * @param Router $router
     * @return void
     */
    private function registerRoutes(Router $router): void
    {
        $actions = Discover::in(...Config::handlerPaths())
            ->classes()
            ->get();

        $actionsWithTrait = array_filter(
            $actions,
            fn (string $className) => in_array(AsAction::class, class_uses_recursive($className)) && method_exists($className, 'route')
        );

        foreach ($actionsWithTrait as $action) {
            /** @phpstan-ignore-next-line */
            $action::route($router);
        }
    }

    /**
     * @param Router $router
     * @return void
     */
    public function registerActions(Router $router): void
    {
        $router->matched(function (RouteMatched $event) {
            $route = $event->route;
            $controllerClass = $this->getControllerClass($route);

            if (! $controllerClass || ! class_exists($controllerClass)) {
                return;
            }

            if (! in_array(AsAction::class, class_uses_recursive($controllerClass))) {
                return;
            }

            $instance = app($controllerClass);

            $route->setAction([
                'uses' => fn () => (new ActionDecorator($instance, $route))(),
            ]);
        });
    }

    /**
     * @param Route $route
     * @return string|null
     */
    private function getControllerClass(Route $route): ?string
    {
        $uses = $route->getAction('uses');

        if (! is_string($uses)) {
            return null;
        }

        return explode('@', $uses)[0];
    }
}
