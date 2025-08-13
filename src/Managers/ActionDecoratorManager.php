<?php

namespace Ignaciocastro0713\CqbusMediator\Managers;

use Ignaciocastro0713\CqbusMediator\Config;
use Ignaciocastro0713\CqbusMediator\Decorators\ActionDecorator;
use Ignaciocastro0713\CqbusMediator\Traits\AsAction;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Str;
use ReflectionMethod;
use Spatie\StructureDiscoverer\Data\DiscoveredStructure;
use Spatie\StructureDiscoverer\Discover;

class ActionDecoratorManager
{
    private const ACTION_TRAIT = AsAction::class;
    private const ROUTE_METHOD = 'route';

    public function __construct(private readonly Router $router)
    {
    }

    /**
     * Boot the manager by registering routes and actions.
     */
    public function boot(): void
    {
        $this->registerRoutes();
        $this->registerActions();
    }

    /**
     * Register all discovered action routes.
     */
    private function registerRoutes(): void
    {
        $actions = Discover::in(...Config::handlerPaths())
            ->classes()
            ->custom(fn (DiscoveredStructure $structure) => $this->isValidActionClass($structure->getFcqn()))
            ->get();

        foreach ($actions as $action) {
            /**
             * Register action route.
             * @noinspection PhpUndefinedMethodInspection
             * @phpstan-ignore-next-line because route() is a static method on action classes
             **/
            $action::route($this->router);
        }
    }

    /**
     * Register action decorators for matched routes.
     */
    private function registerActions(): void
    {
        $this->router->matched(function (RouteMatched $event) {
            $route = $event->route;
            $controllerClass = $this->getControllerClass($route);

            if (! $controllerClass ||
                ! class_exists($controllerClass) ||
                ! in_array(self::ACTION_TRAIT, class_uses_recursive($controllerClass))
            ) {
                return;
            }

            $instance = app($controllerClass);

            $route->setAction([
                'uses' => fn () => (new ActionDecorator($instance, $route))(),
            ]);
        });
    }

    /**
     * Returns true if the given class uses the action trait and has the route static method.
     */
    private function isValidActionClass(string $className): bool
    {
        return in_array(self::ACTION_TRAIT, class_uses_recursive($className), true)
            && method_exists($className, self::ROUTE_METHOD)
            && (new ReflectionMethod($className, self::ROUTE_METHOD))->isStatic();
    }

    /**
     * Gets the controller class from the route action.
     */
    private function getControllerClass(Route $route): ?string
    {
        $uses = $route->getAction('uses');

        return is_string($uses) ? Str::before($uses, '@') : null;
    }
}
