<?php

namespace Ignaciocastro0713\CqbusMediator\Managers;

use Ignaciocastro0713\CqbusMediator\Config;
use Ignaciocastro0713\CqbusMediator\Decorators\ActionDecorator;
use Ignaciocastro0713\CqbusMediator\Traits\AsAction;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Spatie\StructureDiscoverer\Discover;

class ActionDecoratorManager
{
    public function __construct(private readonly Router $router)
    {
    }

    public function boot(): void
    {
        $this->registerRoutes();
        $this->registerActions();
    }

    private function registerRoutes(): void
    {
        $actions = Discover::in(...Config::handlerPaths())
            ->classes()
            ->get();

        $actionsWithTrait = array_filter(
            $actions,
            fn (string $className) =>
                in_array(AsAction::class, class_uses_recursive($className))
                && method_exists($className, 'route')
        );

        foreach ($actionsWithTrait as $action) {
            /** @phpstan-ignore-next-line */
            $action::route($this->router);
        }
    }

    private function registerActions(): void
    {
        $this->router->matched(function (RouteMatched $event) {
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

    private function getControllerClass(Route $route): ?string
    {
        $uses = $route->getAction('uses');

        if (! is_string($uses)) {
            return null;
        }

        return explode('@', $uses)[0];
    }
}
