![](https://banners.beyondco.de/CQBus%20Mediator%20for%20Laravel.png?theme=light&packageManager=composer+require&packageName=ignaciocastro0713%2Fcqbus-mediator&pattern=architect&style=style_1&description=CQRS+Mediator+implementation+for+Laravel&md=1&showWatermark=0&fontSize=100px&images=https%3A%2F%2Flaravel.com%2Fimg%2Flogomark.min.svg)

[![run-tests](https://github.com/ignaciocastro0713/cqbus-mediator/actions/workflows/run-tests.yml/badge.svg)](https://github.com/ignaciocastro0713/cqbus-mediator/actions/workflows/run-tests.yml)
[![Check & fix styling](https://github.com/ignaciocastro0713/cqbus-mediator/actions/workflows/php-cs-fixer.yml/badge.svg)](https://github.com/ignaciocastro0713/cqbus-mediator/actions/workflows/php-cs-fixer.yml)
[![codecov](https://codecov.io/gh/ignaciocastro0713/cqbus-mediator/graph/badge.svg)](https://codecov.io/gh/ignaciocastro0713/cqbus-mediator)
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
use Ignaciocastro0713\CqbusMediator\Contracts\Mediator;
use Ignaciocastro0713\CqbusMediator\Traits\AsAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Router;

class RegisterUserAction
{
    use AsAction;

    public function __construct(private readonly Mediator $mediator)
    {
    }

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

> **Important:** The `route()` method is required when using the `AsAction` trait. This static method is called during boot to register your route.

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

## 📢 Event Bus (Publish/Subscribe)

In addition to the Command/Query pattern (one request → one handler), CQBus Mediator supports an **Event Bus** where multiple handlers can respond to a single event.

### When to use Events vs Commands

| Pattern | Use Case |
|---------|----------|
| `send()` | One request, one handler (Commands/Queries) |
| `publish()` | One event, multiple handlers (Events/Notifications) |

### 1. Define an Event

```php
namespace App\Events;

class UserRegistered
{
    public function __construct(
        public readonly string $userId,
        public readonly string $email
    ) {}
}
```

### 2. Create Event Handlers

Multiple handlers can respond to the same event. Use `priority` to control execution order (higher = first). Priority is optional (defaults to 0):

```php
use Ignaciocastro0713\CqbusMediator\Attributes\EventHandler;
use App\Events\UserRegistered;

#[EventHandler(UserRegistered::class, priority: 3)]
class SendWelcomeEmailHandler
{
    public function handle(UserRegistered $event): void
    {
        // Send welcome email
        Mail::to($event->email)->send(new WelcomeEmail());
    }
}

#[EventHandler(UserRegistered::class, priority: 2)]
class CreateDefaultSettingsHandler
{
    public function handle(UserRegistered $event): void
    {
        // Create default user settings
        UserSettings::create(['user_id' => $event->userId]);
    }
}

#[EventHandler(UserRegistered::class)]  // priority: 0 (default)
class LogUserRegistrationHandler
{
    public function handle(UserRegistered $event): void
    {
        Log::info("User registered: {$event->userId}");
    }
}
```

### 3. Publish the Event

```php
use Ignaciocastro0713\CqbusMediator\Contracts\Mediator;

class RegisterUserController
{
    public function __construct(private readonly Mediator $mediator) {}

    public function __invoke(RegisterUserRequest $request)
    {
        // Create the user (using send for the command)
        $user = $this->mediator->send($request);
        
        // Publish event to all handlers
        $this->mediator->publish(new UserRegistered($user->id, $user->email));
        
        return response()->json($user, 201);
    }
}
```

### 4. Get Results from Handlers

The `publish()` method returns an array of results keyed by handler class:

```php
$results = $this->mediator->publish(new UserRegistered($userId, $email));

// $results = [
//     SendWelcomeEmailHandler::class => null,
//     CreateDefaultSettingsHandler::class => $settings,
//     LogUserRegistrationHandler::class => null,
// ]
```

> **Note:** Event handlers also support global and handler-level pipelines, just like request handlers.

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

## 🎯 Handler-level Pipelines

In addition to global pipelines, you can apply pipelines to specific handlers using the `#[Pipeline]` attribute. This is useful for handlers that need specific middleware like transactions, caching, or auditing.

### Example: Transaction Pipeline

1. **Create a Transaction Pipe:**
   ```php
   namespace App\Pipelines;
   
   use Closure;
   use Illuminate\Support\Facades\DB;
   
   class TransactionPipeline
   {
       public function handle($request, Closure $next)
       {
           return DB::transaction(fn () => $next($request));
       }
   }
   ```

2. **Apply to a specific Handler:**
   ```php
   use Ignaciocastro0713\CqbusMediator\Attributes\Pipeline;
   use Ignaciocastro0713\CqbusMediator\Attributes\RequestHandler;
   
   #[RequestHandler(CreateOrderRequest::class)]
   #[Pipeline(TransactionPipeline::class)]
   class CreateOrderHandler
   {
       public function handle(CreateOrderRequest $request): Order
       {
           // This code runs inside a database transaction
           $order = Order::create($request->validated());
           $order->items()->createMany($request->items);
           
           return $order;
       }
   }
   ```

3. **Multiple Pipelines:**
   ```php
   #[RequestHandler(CreateOrderRequest::class)]
   #[Pipeline([TransactionPipeline::class, AuditPipeline::class])]
   class CreateOrderHandler
   {
       // Pipelines execute in order: Transaction → Audit → Handler
   }
   ```

> **Note:** Handler-level pipelines run **after** global pipelines. The execution order is: Global Pipelines → Handler Pipelines → Handler.

### Skipping Global Pipelines

Sometimes you need certain handlers to bypass global pipelines entirely (e.g., health checks, internal system handlers, or high-frequency endpoints). Use the `#[SkipGlobalPipelines]` attribute:

```php
use Ignaciocastro0713\CqbusMediator\Attributes\RequestHandler;
use Ignaciocastro0713\CqbusMediator\Attributes\SkipGlobalPipelines;

#[RequestHandler(HealthCheckRequest::class)]
#[SkipGlobalPipelines]
class HealthCheckHandler
{
    public function handle(HealthCheckRequest $request): array
    {
        // This handler skips all global pipelines (logging, transactions, etc.)
        return ['status' => 'ok'];
    }
}
```

You can still use handler-level pipelines with `#[SkipGlobalPipelines]`:

```php
#[RequestHandler(InternalProcessRequest::class)]
#[SkipGlobalPipelines]
#[Pipeline(SpecificPipeline::class)]  // This will still run
class InternalProcessHandler
{
    // Skips global pipelines, but SpecificPipeline runs
}
```

---

## 📋 Listing Handlers and Actions

Use the `mediator:list` command to view all registered handlers and actions. This is helpful for debugging and understanding your application structure.

```bash
php artisan mediator:list
```

**Output:**
```
📦 Loading from cache: bootstrap/cache/mediator.php

  Handlers

+------------------------------------------+------------------------------------------+
| Request                                  | Handler                                  |
+------------------------------------------+------------------------------------------+
| App\Http\Handlers\RegisterUserRequest    | App\Http\Handlers\RegisterUserHandler    |
| App\Http\Handlers\CreateOrderRequest     | App\Http\Handlers\CreateOrderHandler     |
+------------------------------------------+------------------------------------------+

  Actions

+------------------------------------------+
| Action Class                             |
+------------------------------------------+
| App\Http\Handlers\RegisterUserAction     |
| App\Http\Handlers\CreateOrderAction      |
+------------------------------------------+

  Handlers: 2 | Actions: 2
```

### Filter Options

```bash
# Show only handlers
php artisan mediator:list --handlers

# Show only actions
php artisan mediator:list --actions
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

## 🛠️ Development

### Requirements

- PHP 8.1+
- Composer

### Setup

```bash
# Clone the repository
git clone https://github.com/IgnacioCastro0713/cqbus-mediator.git
cd cqbus-mediator

# Install dependencies
composer install
```

### Available Commands

| Command | Description |
|---------|-------------|
| `composer test` | Run tests with Pest |
| `composer test:unit` | Run only unit tests |
| `composer test:feature` | Run only feature tests |
| `composer test:coverage` | Run tests with coverage report (terminal) |
| `composer test:coverage-html` | Run tests with HTML coverage report |
| `composer analyse` | Run static analysis with PHPStan (level 8) |
| `composer format` | Fix code style with PHP CS Fixer |
| `composer format:check` | Check code style without fixing |
| `composer check` | Run all checks (format + analyse + test) |
| `composer ci` | Run CI checks (format:check + analyse + test:coverage) |
| `composer benchmark` | Run performance benchmarks |

### Running Tests

```bash
# Run all tests
composer test

# Run tests with coverage (terminal output)
composer test:coverage

# Run tests with HTML coverage report
composer test:coverage-html
# Then open coverage-report/index.html in your browser

# Run specific test file
./vendor/bin/pest tests/Unit/MediatorTest.php

# Run tests matching a filter
./vendor/bin/pest --filter="handler"
```

### Test Coverage

To generate coverage reports, you need **Xdebug** or **PCOV** installed.

```bash
# Terminal coverage report
composer test:coverage

# HTML coverage report (opens in browser)
composer test:coverage-html
```

The HTML report will be generated in `coverage-report/` directory.

### Static Analysis (PHPStan)

```bash
# Run PHPStan analysis (level 8)
composer analyse

# Or directly with options
./vendor/bin/phpstan analyse src --level=8
```

### Code Style (PHP CS Fixer)

```bash
# Fix code style
composer format

# Check without fixing (dry-run)
composer format:check
```

### Full Check (CI)

Run all checks before committing:

```bash
composer check
```

This runs:
1. **PHP CS Fixer** - Fixes code formatting
2. **PHPStan** - Static analysis (level 8)
3. **Pest** - Test suite

### Project Structure

```
src/
├── Attributes/          # PHP Attributes (#[RequestHandler], #[EventHandler], #[Pipeline], etc.)
├── Console/             # Artisan commands (CacheCommand, ClearCommand, ListCommand, MakeHandlerCommand)
│   └── Stubs/           # Stub files for code generation
├── Constants/           # Shared constants
├── Contracts/           # Interfaces (Mediator)
├── Decorators/          # Action decorator for route handling
├── Discovery/           # Handler, EventHandler and Action discovery
├── Exceptions/          # Custom exceptions
├── Services/            # MediatorService implementation
├── Support/             # ActionDecoratorManager
└── Traits/              # AsAction trait

tests/
├── Feature/             # Feature/Integration tests
├── Fixtures/            # Test fixtures (handlers, requests, pipelines, events)
└── Unit/                # Unit tests
```

---

## 🤝 Contributing

Feel free to open issues or submit pull requests on the [GitHub repository](https://github.com/IgnacioCastro0713/cqbus-mediator).

## 📄 License

This package is open-sourced software licensed under the MIT license.
