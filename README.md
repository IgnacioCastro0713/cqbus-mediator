![](https://banners.beyondco.de/CQBus%20Mediator%20for%20Laravel.png?theme=light&packageManager=composer+require&packageName=ignaciocastro0713%2Fcqbus-mediator&pattern=architect&style=style_1&description=CQRS+Mediator+implementation+for+Laravel&md=1&showWatermark=0&fontSize=100px&images=https%3A%2F%2Flaravel.com%2Fimg%2Flogomark.min.svg)

[![run-tests](https://github.com/ignaciocastro0713/cqbus-mediator/actions/workflows/run-tests.yml/badge.svg)](https://github.com/ignaciocastro0713/cqbus-mediator/actions/workflows/run-tests.yml)
[![Check & fix styling](https://github.com/ignaciocastro0713/cqbus-mediator/actions/workflows/php-cs-fixer.yml/badge.svg)](https://github.com/ignaciocastro0713/cqbus-mediator/actions/workflows/php-cs-fixer.yml)
<a href="https://packagist.org/packages/ignaciocastro0713/cqbus-mediator" target="_blank"><img src="https://img.shields.io/packagist/v/ignaciocastro0713/cqbus-mediator.svg?style=flat-square"/></a>
<a href="https://packagist.org/packages/ignaciocastro0713/cqbus-mediator" target="_blank"><img src="https://img.shields.io/packagist/dt/ignaciocastro0713/cqbus-mediator.svg?style=flat-square"/></a>
<a href="https://packagist.org/packages/ignaciocastro0713/cqbus-mediator" target="_blank"><img src="https://img.shields.io/packagist/l/ignaciocastro0713/cqbus-mediator.svg?style=flat-square"/></a>

**CQBus Mediator** is a lightweight, zero-configuration Command/Query Bus for Laravel. It simplifies your application architecture by decoupling controllers from business logic using the Mediator pattern (CQRS).

## ✨ Why use this package?

- **⚡ Zero Config**: It automatically discovers your Handlers using PHP Attributes (`#[RequestHandler]`).
- **🛠️ Developer Experience**: Includes Artisan commands to scaffold Requests, Handlers, and Actions instantly.
- **🔌 Dependency Injection**: Handlers are fully resolved from the Laravel Container.
- **🚀 Performance Optimized**: Includes a production cache command to skip scanning and load routes instantly.
- **🔗 Global Pipelines**: Support for middleware-like pipes for logging, transactions, etc.

---

## 🚀 Installation

Install via Composer:

```bash
composer require ignaciocastro0713/cqbus-mediator
```

The package is auto-discovered. You can optionally publish the config file:

```bash
php artisan vendor:publish --tag=mediator-config
```

---

## ⚡ Quick Start

### 1. Scaffold your Logic
Stop writing boilerplate. Generate a Request, Handler, and Action in one command:

```bash
php artisan make:mediator-handler RegisterUserHandler --action
```
*This creates:*
- `app/Http/Handlers/RegisterUser/RegisterUserRequest.php`
- `app/Http/Handlers/RegisterUser/RegisterUserHandler.php`
- `app/Http/Handlers/RegisterUser/RegisterUserAction.php`

### 2. Define the Request
The Request class is a standard Laravel `FormRequest` or a simple DTO.

```php
namespace App\Http\Handlers\RegisterUser;

use Illuminate\Foundation\Http\FormRequest;

class RegisterUserRequest extends FormRequest
{
    public function rules(): array
    {
        return ['email' => 'required|email', 'password' => 'required|min:8'];
    }
    
    public function authorize(): bool
    {
        return true;
    }
}
```

### 3. Write the Logic (Handler)
The handler contains your business logic. It's automatically linked to the Request via the attribute.

```php
namespace App\Http\Handlers\RegisterUser;

use App\Models\User;
use Ignaciocastro0713\CqbusMediator\Attributes\RequestHandler;

#[RequestHandler(RegisterUserRequest::class)]
class RegisterUserHandler
{
    public function handle(RegisterUserRequest $request): User
    {
        // Business logic here
        return User::create($request->validated());
    }
}
```

---

## 🎮 Usage Patterns

You can use the Mediator in two ways:

### Option A: The "Action" Pattern (Recommended)
Use the generated `Action` class as a Single Action Controller. It handles the request, dispatches it to the mediator, and returns a response.

**1. Define the Route inside the Action:**
You don't need to touch `routes/api.php`. Just define the route directly in your Action class:

```php
class RegisterUserAction
{
    use AsAction;

    public function __construct(private readonly Mediator $mediator) {}

    // 🚀 Auto-registered! No need to add to routes/api.php
    public static function route(Router $router): void
    {
        $router->post('/api/register', static::class);
    }

    public function handle(RegisterUserRequest $request): JsonResponse
    {
        $user = $this->mediator->send($request);
        return response()->json($user, 201);
    }
}
```

*> The package automatically discovers this class and registers the route for you.*

**Alternative (Manual Registration):**
If you prefer standard routing, you can omit the `route` method and register it manually in `routes/web.php`:

```php
Route::post('/register', \App\Http\Handlers\RegisterUser\RegisterUserAction::class);
```

### Option B: Classic Controller Injection
Inject the `Mediator` interface into any standard controller.

```php
use Ignaciocastro0713\CqbusMediator\Contracts\Mediator;

class UserController extends Controller
{
    public function store(RegisterUserRequest $request, Mediator $mediator)
    {        
        $user = $mediator->send($request);
        return response()->json($user, 201);
    }
}
```

---

## 🚦 Advanced Routing: Attributes

When using the **Action Pattern**, you can organize your routes and apply middleware directly on the class using PHP Attributes. This keeps your routing logic self-contained and eliminates the need for external groups in `api.php`.

### Available Attributes

- `#[Prefix('api/v1')]`: Prefixes the route URI.
- `#[Middleware(['auth:sanctum', 'throttle:api'])]`: Applies middleware to the route.

### Example

```php
use Ignaciocastro0713\CqbusMediator\Attributes\Middleware;
use Ignaciocastro0713\CqbusMediator\Attributes\Prefix;

#[Prefix('api/users')]
#[Middleware(['auth:sanctum'])]
class UpdateUserAction
{
    use AsAction;

    public static function route(Router $router): void
    {
        // Final Route: POST /api/users/{id}
        // Middleware: auth:sanctum
        $router->post('/{id}', static::class);
    }

    public function handle(UpdateUserRequest $request): JsonResponse
    {
        // ...
    }
}
```

---

## 🔗 Global Pipelines (Middleware)

You can define global pipes that run before every handler (e.g., for logging or transactions).

1. **Create a Pipe:**
   ```php
   class LoggingPipeline
   {
       public function handle($request, \Closure $next)
       {
           \Log::info('Processing: ' . get_class($request));
           return $next($request);
       }
   }
   ```

2. **Register in `config/mediator.php`:**
   ```php
   'pipelines' => [
       App\Pipelines\LoggingPipeline::class,
   ],
   ```

---

## 🚀 Production Optimization

Scanning files for Attributes is fast in development, but for **maximum performance in production**, you should cache the discovery results.

**Add this to your deployment script:**

```bash
# 1. Clear old cache
php artisan mediator:clear

# 2. Cache Handlers and Actions
php artisan mediator:cache
```

This creates a `bootstrap/cache/mediator.php` file. The package will load this map instantly instead of scanning your directories.

> **Note:** The `ActionDecorator` automatically respects `php artisan route:cache`. If your routes are cached, no discovery overhead occurs during booting.

---

## 🤝 Contributing

Feel free to open issues or submit pull requests on the [GitHub repository](https://github.com/IgnacioCastro0713/cqbus-mediator).

## 📄 License

This package is open-sourced software licensed under the MIT license.
