# Routing & Actions

Stop digging through massive `routes/api.php` files to find where an endpoint points to. 

We highly recommend using the **Action Pattern** alongside our custom attribute routing. This approach brings the route definition right next to the logic that handles it, making your codebase infinitely easier to navigate and maintain.

## The "Action" Pattern (Recommended)

Use the generated `Action` class as a Single Action Controller. By applying the `AsAction` trait and routing attributes (like `#[Api]`), the package automatically registers the routes and middleware into the Laravel Router.

```php
use Ignaciocastro0713\CqbusMediator\Attributes\Routing\Api;
use Ignaciocastro0713\CqbusMediator\Contracts\Mediator;
use Ignaciocastro0713\CqbusMediator\Traits\AsAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Router;

#[Api] // ⚡ Applies 'api' middleware group AND 'api/' prefix automatically
class RegisterUserAction
{
    use AsAction;

    public function __construct(private readonly Mediator $mediator) {}

    public static function route(Router $router): void
    {
        // Final route mapped: POST /api/register
        $router->post('/register');
    }

    public function handle(RegisterUserRequest $request): JsonResponse
    {
        $user = $this->mediator->send($request);
        return response()->json($user, 201);
    }
}
```

---

## Route Model Binding

The package fully supports Laravel's **Implicit Route Model Binding** directly within your Action's `handle` method.

```php
#[Api]
class UpdateUserAction
{
    use AsAction;

    public static function route(Router $router): void
    {
        // Parameter {user} matches $user in handle()
        $router->put('/users/{user}');
    }

    public function handle(UpdateUserRequest $request, User $user): JsonResponse
    {
        // $user is automatically resolved from the database by Laravel
        $updatedUser = $this->mediator->send($request);
        return response()->json($updatedUser);
    }
}
```

---

## Action Registration Priority

Sometimes you have overlapping routes, such as `/api/users/current` and `/api/users/{user}`. Laravel's router requires the more specific route (like `/api/users/current`) to be registered first. 

You can explicitly set the registration priority using the `#[Priority]` attribute on your Action class.

```php
use Ignaciocastro0713\CqbusMediator\Attributes\Routing\Api;
use Ignaciocastro0713\CqbusMediator\Attributes\Routing\Priority;

#[Api]
#[Priority(10)] // Higher priority registers first by default
class GetCurrentUserAction
{
    use AsAction;

    public static function route(Router $router): void
    {
        // Registers BEFORE /api/users/{user} because it has a priority of 10
        $router->get('/current');
    }

    public function handle(GetCurrentUserRequest $request)
    {
        // ...
    }
}
```

Actions without a `#[Priority]` attribute default to `0`. You can change the global sorting direction in `config/mediator.php` using the `route_priority_direction` key (defaults to `'desc'`).

### Priority Groups (Contextual Sorting)

If you have a large team or multiple domains, relying on global priority integers can lead to conflicts. You can use the optional `group` argument to create isolated sorting contexts. 

Groups are ordered alphabetically first, and then the actions within that group are sorted by their priority integer.

```php
// Actions without a group (globals) are always registered first.
#[Priority(10)] 
class A {}

// Grouped actions are registered after globals, sorted alphabetically by group name ('billing' before 'users').
#[Priority(10, group: 'billing')]
class B {}

#[Priority(20, group: 'users')] // Sorted higher within the 'users' group
class C {}

#[Priority(10, group: 'users')] 
class D {}
```

*Note: If two actions share the exact same group and priority, the Mediator will use their fully qualified class name as a deterministic tie-breaker to ensure your routes always register in the exact same order.*
---

## Global Route Patterns

If you use Laravel's global route parameter patterns (e.g. `Route::pattern('id', '[0-9]+')` in your `AppServiceProvider`), these are automatically respected by the Mediator when registering your Action routes. You don't need to do any extra configuration.

---

## Built-in Routing Attributes

> **⚠️ Important:** Every Action class **must** have either the `#[Api]` or `#[Web]` attribute to define its base routing context. If omitted, the action will not be discovered and its routes will not be registered.

- `#[Api]`: Applies the `api` middleware group and prepends `api/` to the URI.
- `#[Web]`: Applies the `web` middleware group.
- `#[Prefix('v1')]`: Prefixes the route URI. Can be combined with `#[Api]`.
- `#[Name('route.name')]`: Sets the route name or appends to a prefix when a route name is defined in the `route` method.
- `#[Middleware(['auth:sanctum'])]`: Applies custom middleware.
- `#[Priority(10)]`: Sets the route registration priority.

### Example Combining Attributes:

```php
use Ignaciocastro0713\CqbusMediator\Attributes\Routing\Api;
use Ignaciocastro0713\CqbusMediator\Attributes\Routing\Prefix;
use Ignaciocastro0713\CqbusMediator\Attributes\Routing\Name;
use Ignaciocastro0713\CqbusMediator\Attributes\Routing\Middleware;

#[Api]
#[Prefix('v1/orders')]
#[Name('orders.')]
#[Middleware(['auth:sanctum'])]
class CreateOrderAction
{
    use AsAction;

    public static function route(Router $router): void
    {
        // Final Route: POST /api/v1/orders
        // Route Name: orders.create
        // Middleware: api, auth:sanctum
        $router->post('/')->name('create');
    }
}
```

---

## 🧠 Advanced: Custom Routing Attributes

CQBus Mediator is built with the Open/Closed Principle in mind. You are not limited to our built-in attributes!

You can create your own routing attributes (e.g., `#[Domain]`, `#[WithoutMiddleware]`) by simply implementing the `RouteModifier` interface. The package will automatically discover and apply them.

### Example: Creating a `#[Domain]` attribute

**1. Create the Attribute:**
```php
namespace App\Attributes;

use Attribute;
use Ignaciocastro0713\CqbusMediator\Contracts\RouteModifier;
use Ignaciocastro0713\CqbusMediator\Routing\RouteOptions;

#[Attribute(Attribute::TARGET_CLASS)]
class Domain implements RouteModifier
{
    public function __construct(public string $domain) {}

    public function modifyRoute(RouteOptions $options): void
    {
        // Add the domain to the Laravel RouteOptions object
        $options->domain($this->domain);
    }
}
```

**2. Use it in your Action:**
```php
use App\Attributes\Domain;
use Ignaciocastro0713\CqbusMediator\Attributes\Routing\Api;

#[Api]
#[Domain('api.myapp.com')] // 🪄 Your custom attribute!
class TenantDashboardAction
{
    use AsAction;

    public static function route(Router $router): void
    {
        $router->get('/dashboard');
    }
    
    // ...
}
```