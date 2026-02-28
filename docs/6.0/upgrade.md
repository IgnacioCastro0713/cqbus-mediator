# Upgrading To 6.0

CQBus Mediator v6.0 introduces several architectural improvements to enhance DX (Developer Experience) and runtime performance. While this is a major release, the upgrade path is straightforward.

## High Impact Changes

### 1. Attribute Namespace Reorganization
To improve code organization, all attributes have been moved into specific sub-namespaces. You must update your `use` statements across your application.

**Before:**
```php
use Ignaciocastro0713\CqbusMediator\Attributes\RequestHandler;
use Ignaciocastro0713\CqbusMediator\Attributes\EventHandler;
use Ignaciocastro0713\CqbusMediator\Attributes\Pipeline;
use Ignaciocastro0713\CqbusMediator\Attributes\SkipGlobalPipelines;
```

**After:**
```php
use Ignaciocastro0713\CqbusMediator\Attributes\Handlers\RequestHandler;
use Ignaciocastro0713\CqbusMediator\Attributes\Handlers\EventHandler;
use Ignaciocastro0713\CqbusMediator\Attributes\Pipelines\Pipeline;
use Ignaciocastro0713\CqbusMediator\Attributes\Pipelines\SkipGlobalPipelines;
```

### 2. Renamed Routing Attributes
The `ApiRoute` and `WebRoute` attributes have been renamed to `Api` and `Web` for brevity.

**Before:**
```php
use Ignaciocastro0713\CqbusMediator\Attributes\ApiRoute;
use Ignaciocastro0713\CqbusMediator\Attributes\WebRoute;

#[ApiRoute]
class MyAction { /* ... */ }
```

**After:**
```php
use Ignaciocastro0713\CqbusMediator\Attributes\Routing\Api;
use Ignaciocastro0713\CqbusMediator\Attributes\Routing\Web;

#[Api]
class MyAction { /* ... */ }
```

### 3. RouteModifier Interface Changes
If you have created custom routing attributes by implementing the `RouteModifier` interface, the `modifyRoute` method signature has changed. It now receives a fluent `RouteOptions` object instead of an array reference.

**Before:**
```php
public function modifyRoute(array &$options): void
{
    $options['middleware'][] = 'my-middleware';
    $options['domain'] = 'api.example.com';
}
```

**After:**
```php
use Ignaciocastro0713\CqbusMediator\Routing\RouteOptions;

public function modifyRoute(RouteOptions $options): void
{
    // addMiddleware safely merges with existing middleware
    $options->addMiddleware(['my-middleware']);
    // Other properties can be called directly thanks to magic methods
    $options->domain('api.example.com');
}
```

## Medium Impact Changes

### Cached Route Attributes
When you run `php artisan mediator:cache`, the package now resolves all Route-related attributes (like `#[Api]`, `#[Web]`, `#[Middleware]`, etc.) directly into route definitions during the caching process. This completely bypasses the Reflection API when loading routes in production, providing a significant performance boost.

You do not need to change any code, but you should remember to always run `php artisan mediator:cache` during your deployment script.
