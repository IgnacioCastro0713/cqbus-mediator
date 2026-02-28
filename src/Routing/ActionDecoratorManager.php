<?php

declare(strict_types=1);

namespace Ignaciocastro0713\CqbusMediator\Routing;

use Ignaciocastro0713\CqbusMediator\Constants\MediatorConstants;
use Ignaciocastro0713\CqbusMediator\Contracts\RouteModifier;
use Ignaciocastro0713\CqbusMediator\Discovery\MediatorDiscovery;
use Ignaciocastro0713\CqbusMediator\Exceptions\InvalidActionException;
use Ignaciocastro0713\CqbusMediator\Exceptions\InvalidRequestClassException;
use Ignaciocastro0713\CqbusMediator\MediatorConfig;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionException;

class ActionDecoratorManager
{
    /** @var array<string, bool> Cache of validated action controller classes. */
    private array $validatedControllers = [];

    /**
     * Create a new ActionDecoratorManager instance.
     *
     * @param Router $router The Laravel router instance.
     * @param Application $app The Laravel application instance.
     */
    public function __construct(
        private readonly Router      $router,
        private readonly Application $app
    ) {
    }

    /**
     * Boot the manager by registering routes and action overrides.
     * Skips route registration if routes are already cached to improve performance.
     *
     * @return void
     * @throws ReflectionException|InvalidRequestClassException
     */
    public function boot(): void
    {
        /** @phpstan-ignore-next-line */
        if (! $this->app->routesAreCached()) {
            $this->registerRoutes();
        }

        $this->registerActionOverrides();
    }

    /**
     * Register all discovered action routes into the Laravel Router.
     * Applies route groups based on the resolved attributes (middleware, prefix).
     *
     * @return void
     * @throws ReflectionException|InvalidRequestClassException
     */
    private function registerRoutes(): void
    {
        foreach ($this->getActions() as $key => $value) {
            if (is_int($key)) {
                /** @var class-string $actionClass */
                $actionClass = $value;
                $attributes = $this->resolveRouteAttributes($actionClass);
            } else {
                /** @var class-string $actionClass */
                $actionClass = $key;
                /** @var array<string, mixed> $attributes */
                $attributes = $value;
            }

            $this->router->group($attributes, fn () => $actionClass::{MediatorConstants::ROUTE_METHOD}($this->router));
        }
    }

    /**
     * Get the list of discovered action classes.
     * Loads from cache if available, otherwise performs live discovery.
     *
     * @return array<int|string, mixed>
     * @throws InvalidRequestClassException
     */
    private function getActions(): array
    {
        $cachePath = $this->app->bootstrapPath('cache/mediator.php');

        if (is_file($cachePath)) {
            return (require $cachePath)['actions'] ?? [];
        }

        return MediatorDiscovery::discover(MediatorConfig::handlerPaths())['actions'];
    }

    /**
     * Resolve the routing attributes (prefix, middleware, name) for a given action class.
     * Iterates over attributes implementing RouteModifier and delegates modifications to them.
     *
     * @param class-string $actionClass
     *
     * @return array<string, mixed>
     * @throws ReflectionException
     */
    public function resolveRouteAttributes(string $actionClass): array
    {
        $reflection = new ReflectionClass($actionClass);

        $routeOptions = new RouteOptions(['controller' => $actionClass]);

        foreach ($reflection->getAttributes() as $reflectionAttribute) {
            $attributeInstance = $reflectionAttribute->newInstance();

            if ($attributeInstance instanceof RouteModifier) {
                $attributeInstance->modifyRoute($routeOptions);
            }
        }

        return $routeOptions->toArray();
    }

    /**
     * Listen for matched routes and override the action to point directly
     * to the controller's handle method, ensuring Route Model Binding compatibility.
     *
     * @return void
     */
    private function registerActionOverrides(): void
    {
        $this->router->matched(function (RouteMatched $event) {
            $route = $event->route;
            $controllerClass = $this->getControllerClass($route);

            if (! $this->isValidActionController($controllerClass)) {
                return;
            }

            /** @var string $controllerClass */
            $this->overrideRouteAction($route, $controllerClass);
        });
    }

    /**
     * Get the controller class name from the route's action array.
     *
     * @param Route $route
     *
     * @return string|null
     */
    private function getControllerClass(Route $route): ?string
    {
        $uses = $route->getAction('uses');

        if (is_string($uses)) {
            return Str::before($uses, '@');
        }

        $controller = $route->getAction('controller');

        if (is_string($controller)) {
            return Str::before($controller, '@');
        }

        return null;
    }

    /**
     * Check if the given class is a valid action controller utilizing the AsAction trait.
     *
     * @param string|null $class
     *
     * @return bool
     */
    private function isValidActionController(?string $class): bool
    {
        if (! $class) {
            return false;
        }

        return $this->validatedControllers[$class] ??= class_exists($class)
            && in_array(MediatorConstants::ACTION_TRAIT, class_uses_recursive($class));
    }

    /**
     * Override the route's action uses and controller properties to explicitly
     * point to the handle method of the action class.
     *
     * @param Route $route
     * @param string $controllerClass
     *
     * @return void
     *
     * @throws InvalidActionException If the action class is missing the handle method.
     */
    private function overrideRouteAction(Route $route, string $controllerClass): void
    {
        if (! method_exists($controllerClass, MediatorConstants::HANDLE_METHOD)) {
            throw new InvalidActionException(new $controllerClass(), MediatorConstants::HANDLE_METHOD);
        }

        $action = $route->getAction();
        $action['uses'] = $controllerClass . '@' . MediatorConstants::HANDLE_METHOD;
        $action['controller'] = $action['uses'];

        $route->setAction($action);
    }
}
