<?php

declare(strict_types=1);

namespace Ignaciocastro0713\CqbusMediator\Traits;

use BadMethodCallException;
use Illuminate\Routing\Router;

/**
 * Trait for creating single-action controllers.
 *
 * Classes using this trait must implement:
 * - A public `handle()` method containing the action logic
 * - A public static `route(Router $router)` method to register the route
 *
 * @method mixed handle() The main action logic
 * @method static void route(Router $router) Register the route for this action
 */
trait AsAction
{
    /**
     * This method exists to satisfy Laravel's controller resolution.
     * The actual invocation is handled by ActionDecorator which calls handle().
     *
     * @throws BadMethodCallException if called directly
     */
    public function __invoke(mixed ...$arguments): never
    {
        throw new BadMethodCallException(
            'Direct invocation is not supported. The handle() method is called by the ActionDecorator.'
        );
    }
}
