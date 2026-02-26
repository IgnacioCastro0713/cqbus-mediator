# Routing & Actions

We highly recommend using the **Action Pattern** alongside our custom attribute routing. This approach eliminates bloated `routes/api.php` or `routes/web.php` files by keeping the route definition right next to the logic.

## The "Action" Pattern (Recommended)

Use the generated `Action` class as a Single Action Controller. By using the `AsAction` trait and routing attributes (like `#[ApiRoute]`), the package automatically registers the routes and middleware into the Laravel Router.

```php
use Ignaciocastro0713\CqbusMediator\Attributes\ApiRoute;
use Ignaciocastro0713\CqbusMediator\Contracts\Mediator;
use Ignaciocastro0713\CqbusMediator\Traits\AsAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Router;

#[ApiRoute] // ⚡ Applies 'api' middleware group AND 'api/' prefix automatically
class RegisterUserAction
{
    use AsAction;

    public function __construct(private readonly Mediator $mediator) {}

    public static function route(Router $router): void
    {
        // Final route: POST /api/register
        $router->post('/register');
    }

    public function handle(RegisterUserRequest $request): JsonResponse
    {
        $user = $this->mediator->send($request);
        return response()->json($user, 201);
    }
}
```

## Route Model Binding

The package fully supports Laravel's **Implicit Route Model Binding** directly within your Action's `handle` method.

```php
#[ApiRoute]
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

## Available Routing Attributes

> **⚠️ Important:** Every Action class **must** have either the `#[ApiRoute]` or `#[WebRoute]` attribute to define its base routing context. If omitted, the application will throw a `MissingRouteAttributeException`.

- `#[ApiRoute]`: Applies the `api` middleware group and prepends `api/` to the URI.
- `#[WebRoute]`: Applies the `web` middleware group.
- `#[Prefix('v1')]`: Prefixes the route URI. Can be combined with `#[ApiRoute]`.
- `#[Name('route.name')]`: Sets the route name or appends to a prefix when a route name is defined in the `route` method.
- `#[Middleware(['auth:sanctum'])]`: Applies custom middleware.

### Example Combining Attributes:

```php
use Ignaciocastro0713\CqbusMediator\Attributes\ApiRoute;
use Ignaciocastro0713\CqbusMediator\Attributes\Middleware;
use Ignaciocastro0713\CqbusMediator\Attributes\Name;
use Ignaciocastro0713\CqbusMediator\Attributes\Prefix;
use Ignaciocastro0713\CqbusMediator\Traits\AsAction;
use Illuminate\Routing\Router;

#[ApiRoute]
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

    // ... handle method ...
}
```
