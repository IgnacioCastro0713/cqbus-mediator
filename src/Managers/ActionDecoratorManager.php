<?php

declare(strict_types=1);

namespace Ignaciocastro0713\CqbusMediator\Managers;

use Ignaciocastro0713\CqbusMediator\Attributes\Middleware;
use Ignaciocastro0713\CqbusMediator\Attributes\Prefix;
use Ignaciocastro0713\CqbusMediator\Decorators\ActionDecorator;
use Ignaciocastro0713\CqbusMediator\Discovery\DiscoverAction;
use Ignaciocastro0713\CqbusMediator\MediatorConfig;
use Ignaciocastro0713\CqbusMediator\Traits\AsAction;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ReflectionClass;

class ActionDecoratorManager
{
    private const ACTION_TRAIT = AsAction::class;

    public function __construct(
        private readonly Router $router,
        private readonly Application $app
    ) {
    }

    /**
     * Boot the manager by registering routes and actions.
     * @throws \ReflectionException
     */
    public function boot(): void
    {
        // Optimization: If routes are already cached by Laravel, we skip the expensive
        // discovery process to improve production performance (boot time).
        /** @phpstan-ignore-next-line */
        if (! $this->app->routesAreCached()) {
            $this->registerRoutes();
        }

        $this->registerActions();
    }

    /**
     * Register all discovered action routes.
     * @throws \ReflectionException
     */
    private function registerRoutes(): void
    {
        $actions = $this->getActions();

        foreach ($actions as $action) {
            $attributes = $this->getRouteAttributes($action);

            if (empty($attributes)) {
                /**
                 * Register action route.
                 **/
                $action::route($this->router);

                continue;
            }

            $this->router->group($attributes, function () use ($action) {
                /**
                 * Register action route within group.
                 **/
                $action::route($this->router);
            });
        }
    }

    /**
     * Extract route attributes (Middleware, Prefix) from the action class.
     * @param class-string $actionClass
     * @throws \ReflectionException
     */
    private function getRouteAttributes(string $actionClass): array
    {
        $attributes = [];
        $reflection = new ReflectionClass($actionClass);

        // Middleware
        $middlewareAttr = $reflection->getAttributes(Middleware::class);
        if (! empty($middlewareAttr)) {
            $attributes['middleware'] = $middlewareAttr[0]->newInstance()->middleware;
        }

        // Prefix
        $prefixAttr = $reflection->getAttributes(Prefix::class);
        if (! empty($prefixAttr)) {
            $attributes['prefix'] = $prefixAttr[0]->newInstance()->prefix;
        }

        return $attributes;
    }

    /**
     * Get the list of action classes from cache or discovery.
     * @return array<class-string>
     */
    private function getActions(): array
    {
        $cachePath = $this->app->bootstrapPath('cache/mediator.php');

        if (File::exists($cachePath)) {
            $cached = require $cachePath;

            return $cached['actions'] ?? [];
        }

        return DiscoverAction::in(...MediatorConfig::handlerPaths())->get();
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

            $route->setAction(array_merge(
                $route->getAction(),
                ['uses' => fn () => (new ActionDecorator($instance, $route, $this->app))()]
            ));
        });
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
