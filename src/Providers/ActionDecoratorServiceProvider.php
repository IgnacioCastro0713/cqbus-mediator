<?php

namespace Ignaciocastro0713\CqbusMediator\Providers;

use Ignaciocastro0713\CqbusMediator\Decorators\ActionDecorator;
use Ignaciocastro0713\CqbusMediator\Traits\AsAction;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class ActionDecoratorServiceProvider extends ServiceProvider
{
    public function boot(Router $router): void
    {
        $router->matched(function (RouteMatched $event) {
            $route = $event->route;
            $controllerClass = $this->getControllerClass($route);

            if (! $controllerClass || ! class_exists($controllerClass)) {
                return;
            }

            if (! in_array(AsAction::class, class_uses($controllerClass))) {
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
    protected function getControllerClass(Route $route): ?string
    {
        $uses = $route->getAction('uses');

        if (! is_string($uses)) {
            return null;
        }

        return explode('@', $uses)[0];
    }
}
