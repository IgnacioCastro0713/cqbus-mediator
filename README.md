![](https://banners.beyondco.de/CQBus%20Mediator%20for%20Laravel.png?theme=light&packageManager=composer+require&packageName=ignaciocastro0713%2Fcqbus-mediator&pattern=architect&style=style_1&description=CQRS+Mediator+implementation+for+Laravel&md=1&showWatermark=0&fontSize=100px&images=https%3A%2F%2Flaravel.com%2Fimg%2Flogomark.min.svg)

[![run-tests](https://github.com/ignaciocastro0713/cqbus-mediator/actions/workflows/run-tests.yml/badge.svg)](https://github.com/ignaciocastro0713/cqbus-mediator/actions/workflows/run-tests.yml)
[![PHPStan](https://github.com/ignaciocastro0713/cqbus-mediator/actions/workflows/phpstan.yml/badge.svg)](https://github.com/ignaciocastro0713/cqbus-mediator/actions/workflows/phpstan.yml)
[![codecov](https://codecov.io/gh/ignaciocastro0713/cqbus-mediator/graph/badge.svg)](https://codecov.io/gh/ignaciocastro0713/cqbus-mediator)
[![Documentation](https://img.shields.io/badge/docs-v7.0.x-red.svg?style=flat-square)](https://ignaciocastro0713.github.io/cqbus-mediator/)
<a href="https://packagist.org/packages/ignaciocastro0713/cqbus-mediator" target="_blank"><img src="https://img.shields.io/packagist/v/ignaciocastro0713/cqbus-mediator.svg?style=flat-square"/></a>
<a href="https://packagist.org/packages/ignaciocastro0713/cqbus-mediator" target="_blank"><img src="https://img.shields.io/packagist/dt/ignaciocastro0713/cqbus-mediator.svg?style=flat-square"/></a>
<a href="https://packagist.org/packages/ignaciocastro0713/cqbus-mediator" target="_blank"><img src="https://img.shields.io/packagist/l/ignaciocastro0713/cqbus-mediator.svg?style=flat-square"/></a>

**CQBus Mediator** is a zero-configuration Command/Query Bus for Laravel that decouples your controllers from business logic using the Mediator pattern (CQRS) and PHP 8 Attributes.

👉 **[Read the full documentation](https://ignaciocastro0713.github.io/cqbus-mediator/7.0/installation.html)**

---

## The Problem

Controllers that mix HTTP, business logic, and side effects are hard to test and maintain.

<table>
<tr>
<th width="50%">❌ Before — everything tangled in one place</th>
<th width="50%">✅ After — each concern in its own place</th>
</tr>
<tr>
<td width="50%" valign="top">

```php
class UserController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        DB::beginTransaction();
        try {
            $user = User::create($request->all());
            Mail::to($user)->send(new WelcomeEmail());
            Log::info("User registered");
            DB::commit();
            return response()->json($user, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
```

</td>
<td width="50%" valign="top">

```php
#[Api]
#[Pipeline(DatabaseTransactionPipeline::class)]
class RegisterUserAction
{
    use AsAction;

    public function __construct(
        private readonly Mediator $mediator
    ) {}

    public static function route(Router $router): void
    {
        $router->post('/register');
    }

    public function handle(RegisterUserRequest $request): JsonResponse
    {
        // Handler runs the logic
        $user = $this->mediator->send($request);

        // Notifications handle side effects
        $this->mediator->publish(new UserRegisteredEvent($user));

        return response()->json($user, 201);
    }
}
```

</td>
</tr>
</table>

---

## Installation

```bash
composer require ignaciocastro0713/cqbus-mediator
```

The package is auto-discovered. Optionally publish the config file:

```bash
php artisan vendor:publish --tag=mediator-config
```

---

## How It Works

The package supports two patterns:

| Pattern | Method | Direction | Use for |
|---|---|---|---|
| **Command / Query** | `send()` | 1-to-1 | Business logic that reads or writes |
| **Event Bus** | `publish()` | 1-to-N | Side effects (emails, logs, etc.) |

### 1. Scaffold your classes

```bash
php artisan make:mediator-handler RegisterUserHandler --action
```

This generates a `RegisterUserRequest`, `RegisterUserHandler`, and `RegisterUserAction` in one go.

### 2. Write your Handler

```php
#[RequestHandler(RegisterUserRequest::class)]
class RegisterUserHandler
{
    public function handle(RegisterUserRequest $request): User
    {
        return User::create($request->validated());
    }
}
```

### 3. Dispatch from your Action

```php
$user = $this->mediator->send($request);
```

That's it — the Mediator discovers and routes to the correct handler automatically.

---

## Key Features

- **Zero config** — handlers are auto-discovered via `#[RequestHandler]` and `#[Notification]` attributes.
- **Event Bus** — publish events to multiple notification handlers, with priority control.
- **Attribute Routing** — define routes directly on Action classes with `#[Api]`, `#[Prefix]`, `#[Middleware]`, and more.
- **Pipelines** — apply middleware-like logic globally or per-handler using `#[Pipeline]`.
- **Testing Fakes** — assert dispatched requests without running business logic.
- **Production Cache** — eliminate discovery overhead with `php artisan mediator:cache`.

---

## Documentation

Full guides on every feature are available in the official docs:

- [Installation](https://ignaciocastro0713.github.io/cqbus-mediator/7.0/installation.html)
- [Core Concepts](https://ignaciocastro0713.github.io/cqbus-mediator/7.0/concepts.html)
- [Commands & Queries](https://ignaciocastro0713.github.io/cqbus-mediator/7.0/commands.html)
- [Event Bus](https://ignaciocastro0713.github.io/cqbus-mediator/7.0/events.html)
- [Routing & Actions](https://ignaciocastro0713.github.io/cqbus-mediator/7.0/actions.html)
- [Pipelines](https://ignaciocastro0713.github.io/cqbus-mediator/7.0/pipelines.html)
- [Testing](https://ignaciocastro0713.github.io/cqbus-mediator/7.0/testing.html)
- [Console Commands](https://ignaciocastro0713.github.io/cqbus-mediator/7.0/console.html)
- [Production & Performance](https://ignaciocastro0713.github.io/cqbus-mediator/7.0/performance.html)

---

## Requirements

- PHP 8.2+
- Laravel 11.0+

---

## Contributing

Feel free to open issues or submit pull requests on the [GitHub repository](https://github.com/IgnacioCastro0713/cqbus-mediator).

## License

This package is open-sourced software licensed under the MIT license.
