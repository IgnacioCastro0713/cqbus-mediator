<?php

declare(strict_types=1);

namespace Ignaciocastro0713\CqbusMediator\Support;

use Ignaciocastro0713\CqbusMediator\Constants\MediatorConstants;
use Ignaciocastro0713\CqbusMediator\Discovery\ActionDiscovery;
use Ignaciocastro0713\CqbusMediator\Exceptions\InvalidActionException;
use Ignaciocastro0713\CqbusMediator\Exceptions\MissingRouteAttributeException;
use Ignaciocastro0713\CqbusMediator\MediatorConfig;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionException;

readonly class ActionDecoratorManager
{
    /**
     * Create a new ActionDecoratorManager instance.
     *
     * @param Router      $router The Laravel router instance.
     * @param Application $app    The Laravel application instance.
     */
    public function __construct(
        private Router $router,
        private Application $app
    ) {
    }

    /**
     * Boot the manager by registering routes and action overrides.
     * Skips route registration if routes are already cached to improve performance.
     *
     * @return void
     * @throws ReflectionException|MissingRouteAttributeException
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
     * @throws ReflectionException|MissingRouteAttributeException
     */
    private function registerRoutes(): void
    {
        foreach ($this->getActions() as $action) {
            $attributes = $this->resolveRouteAttributes($action);

            $this->router->group($attributes, fn () => $action::{MediatorConstants::ROUTE_METHOD}($this->router));
        }
    }

    /**
     * Get the list of discovered action classes.
     * Loads from cache if available, otherwise performs live discovery.
     *
     * @return array<class-string>
     */
    private function getActions(): array
    {
        $cachePath = $this->app->bootstrapPath('cache/mediator.php');

        if (File::exists($cachePath)) {
            return (require $cachePath)['actions'] ?? [];
        }

        return ActionDiscovery::in(...MediatorConfig::handlerPaths())->get();
    }

    /**
     * Resolve the routing attributes (prefix, middleware, name) for a given action class.
     *
     * @param class-string $actionClass
     *
     * @return array{prefix?: string, middleware?: array<string>, as?: string}
     * @throws ReflectionException|MissingRouteAttributeException
     */
    private function resolveRouteAttributes(string $actionClass): array
    {
        $reflection = new ReflectionClass($actionClass);
        $attributes = [];

        $middlewares = $this->extractMiddlewares($reflection);

        if (! empty($middlewares)) {
            $attributes['middleware'] = $middlewares;
        }

        $prefix = $this->extractPrefix($reflection);

        if ($prefix) {
            $attributes['prefix'] = $prefix;
        }

        $name = $this->extractName($reflection);

        if ($name) {
            $attributes['as'] = $name;
        }

        return $attributes;
    }

    /**
     * Extract middleware names from ApiRoute, WebRoute, and Custom Middleware attributes.
     *
     * @param ReflectionClass<object> $reflection
     *
     * @return array<string>
     * @throws MissingRouteAttributeException
     */
    private function extractMiddlewares(ReflectionClass $reflection): array
    {
        $middlewares = [];
        $hasRoutingAttribute = false;

        if (! empty($reflection->getAttributes(MediatorConstants::ATTRIBUTE_API_ROUTE))) {
            $middlewares[] = 'api';
            $hasRoutingAttribute = true;
        } elseif (! empty($reflection->getAttributes(MediatorConstants::ATTRIBUTE_WEB_ROUTE))) {
            $middlewares[] = 'web';
            $hasRoutingAttribute = true;
        }

        if (! $hasRoutingAttribute) {
            throw new MissingRouteAttributeException($reflection->getName());
        }

        $middlewareAttr = $reflection->getAttributes(MediatorConstants::ATTRIBUTE_MIDDLEWARE);

        if (! empty($middlewareAttr)) {
            $custom = $middlewareAttr[0]->newInstance()->middleware;
            $middlewares = array_merge($middlewares, (array) $custom);
        }

        return array_unique($middlewares);
    }

    /**
     * Extract the route prefix from the Prefix and ApiRoute attributes.
     * Combines ApiRoute's default 'api' prefix with any custom Prefix attribute.
     *
     * @param ReflectionClass<object> $reflection
     *
     * @return string|null
     */
    private function extractPrefix(ReflectionClass $reflection): ?string
    {
        $prefixParts = [];

        if (! empty($reflection->getAttributes(MediatorConstants::ATTRIBUTE_API_ROUTE))) {
            $prefixParts[] = 'api';
        }

        $attributes = $reflection->getAttributes(MediatorConstants::ATTRIBUTE_PREFIX);

        if (! empty($attributes)) {
            $prefixParts[] = trim($attributes[0]->newInstance()->prefix, '/');
        }

        if (empty($prefixParts)) {
            return null;
        }

        return implode('/', $prefixParts);
    }

    /**
     * Extract the route name from the Name attribute.
     *
     * @param ReflectionClass<object> $reflection
     *
     * @return string|null
     */
    private function extractName(ReflectionClass $reflection): ?string
    {
        $attributes = $reflection->getAttributes(MediatorConstants::ATTRIBUTE_NAME);

        if (! empty($attributes)) {
            return $attributes[0]->newInstance()->name;
        }

        return null;
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

        return is_string($uses) ? Str::before($uses, '@') : null;
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
        return $class
            && class_exists($class)
            && in_array(MediatorConstants::ACTION_TRAIT, class_uses_recursive($class));
    }

    /**
     * Override the route's action uses and controller properties to explicitly
     * point to the handle method of the action class.
     *
     * @param Route  $route
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
