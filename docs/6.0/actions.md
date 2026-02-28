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

## Built-in Routing Attributes

> **⚠️ Important:** Every Action class **must** have either the `#[Api]` or `#[Web]` attribute to define its base routing context. If omitted, the action will not be discovered and its routes will not be registered.

- `#[Api]`: Applies the `api` middleware group and prepends `api/` to the URI.
- `#[Web]`: Applies the `web` middleware group.
- `#[Prefix('v1')]`: Prefixes the route URI. Can be combined with `#[Api]`.
- `#[Name('route.name')]`: Sets the route name or appends to a prefix when a route name is defined in the `route` method.
- `#[Middleware(['auth:sanctum'])]`: Applies custom middleware.

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
