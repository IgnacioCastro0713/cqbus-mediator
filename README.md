![](https://banners.beyondco.de/CQBus%20Mediator%20for%20Laravel.png?theme=light&packageManager=composer+require&packageName=ignaciocastro0713%2Fcqbus-mediator&pattern=architect&style=style_1&description=CQRS+Mediator+implementation+for+Laravel&md=1&showWatermark=0&fontSize=100px&images=https%3A%2F%2Flaravel.com%2Fimg%2Flogomark.min.svg)

[![run-tests](https://github.com/ignaciocastro0713/cqbus-mediator/actions/workflows/run-tests.yml/badge.svg)](https://github.com/ignaciocastro0713/cqbus-mediator/actions/workflows/run-tests.yml)
[![Check & fix styling](https://github.com/ignaciocastro0713/cqbus-mediator/actions/workflows/php-cs-fixer.yml/badge.svg)](https://github.com/ignaciocastro0713/cqbus-mediator/actions/workflows/php-cs-fixer.yml)
<a href="https://packagist.org/packages/ignaciocastro0713/cqbus-mediator" target="_blank"><img src="https://img.shields.io/packagist/v/ignaciocastro0713/cqbus-mediator.svg?style=flat-square"/></a>
<a href="https://packagist.org/packages/ignaciocastro0713/cqbus-mediator" target="_blank"><img src="https://img.shields.io/packagist/dt/ignaciocastro0713/cqbus-mediator.svg?style=flat-square"/></a>
<a href="https://packagist.org/packages/ignaciocastro0713/cqbus-mediator" target="_blank"><img src="https://img.shields.io/packagist/l/ignaciocastro0713/cqbus-mediator.svg?style=flat-square"/></a>

A simple, extensible, and configurable Command/Query Bus (Mediator) implementation for Laravel applications. This
package helps you implement the Command/Query Responsibility Segregation (CQRS) with Mediator pattern, promoting
cleaner, more maintainable code by separating concerns and decoupling components.

## ✨ Features

- **Attribute-Based Handler Discovery**: Define your command/query handlers using a simple PHP 8 attribute (
  `#[RequestHandler]`).

- **Configuration-Driven Scanning**: Easily configure the directories where your handlers are located via a dedicated
  configuration file.

- **Automatic Dependency Injection**: Handlers are resolved from the Laravel service container, allowing for seamless
  dependency injection.

- **Clear Separation of Concerns**: Decouples the sender from the handlers, improving testability and code organization.

- **Global pipelines (middleware) support**: that will apply to every request sent through the Mediator

## 🚀 Installation

You can install this package via Composer.

Require the Package:
In your Laravel project's root directory, run:

```pwsh
composer require ignaciocastro0713/cqbus-mediator
```

The Service Provider will be automatically registered. If you wish, you can publish the configuration file:

```pwsh
php artisan vendor:publish --provider="Ignaciocastro0713\CqbusMediator\MediatorServiceProvider"
```

Publish the Configuration File:
The package comes with a configurable file that allows you to define handler discovery paths. Publish it using the
Artisan command:

```pwsh
php artisan vendor:publish --tag=mediator-config
```

This will create `config/mediator.php` in your Laravel application.

## ⚙️ Configuration (`config/mediator.php`)

After publishing, you'll find a `mediator.php` file in your `config` directory. This file is crucial for discovers your
handlers.

```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Handler Discovery Paths
    |--------------------------------------------------------------------------
    |
    | This handler_paths defines the directories where the MediatorService will scan
    | for command/request handlers. These paths are relative to your Laravel
    | application's base directory (typically the 'app' directory).
    |
    | Example: 'handler_paths' => app_path('Features'), would scan 'app/Features/Commands'. or
    |          'handler_paths' => [app_path('Features'), app_path('UseCases')]
    |
    */
    'handler_paths' => app_path(),

    /*
    |--------------------------------------------------------------------------
    | Global Pipelines
    |--------------------------------------------------------------------------
    |
    | The global pipelines that will be applied to every request
    | sent through the Mediator. Each class should have a handle($request, Closure $next) method.
    |
    | Example Pipeline definition:
    | class LoggingPipeline
    | {
    |     public function handle($request, Closure $next)
    |     {
    |         Log::info('Handling Request pipeline', ['request' => $request]);
    |
    |         $response = $next($request);
    |
    |         Log::info('Handled Request pipeline', ['request' => $request]);
    |
    |         return $response;
    |     }
    | }
    |
    | Example configuration:
    |  'pipelines' => [
    |      App\Pipelines\LoggingMiddleware::class,
    |  ]
    |
    | for more information: https://laravel.com/docs/helpers#pipeline
    */
    'pipelines' => [],
];

```

Important: Adjust handler_paths to include all directories where your command/query handlers are located.

## 🛠️ Generating Handlers and Requests

To simplify the creation of new handler and request classes, the package includes an Artisan command that generates both
files and organizes them into a clean folder structure.

The command is called `make:mediator-handler` and has the following signature:

```bash
php artisan make:mediator-handler {name} {--root=Handlers} {--group=}
```

Arguments and Options

- `name`: The name of the handler you want to create. It must end with the word Handler.

- `--root` (optional): Defines the main folder inside app/Http/. By default, it is Handlers.

- `--action` (optional): Defines an additional action class.

Usage Examples
Here are some examples that illustrate how the command works and the resulting file structure.

#### 1. Basic Usage (default)

This command will create the handler and request in the app/Http/Handlers/ folder.

```bash
php artisan make:mediator-handler CreateUserHandler
```

Result:

`app/Http/Handlers/CreateUser/CreateUserHandler.php` (Namespace: `App\Http\Handlers\CreateUser`)

`app/Http/Handlers/CreateUser/CreateUserRequest.php` (Namespace: `App\Http\Handlers\CreateUser`)

#### 2. Using the --root option

This changes the name of the main folder (Handlers) to a custom name, for example UseCases.

```bash
php artisan make:mediator-handler CreateUserHandler --root=UseCases
```

Result:

`app/Http/UseCases/CreateUser/CreateUserHandler.php` (Namespace: `App\Http\UseCases\CreateUser`)

`app/Http/UseCases/CreateUser/CreateUserRequest.php` (Namespace: `App\Http\UseCases\CreateUser`)

#### 3. Using the --action option

This creates an additional action to group the classes while keeping the default main folder (Handlers).

```bash
php artisan make:mediator-handler CreateUserHandler --action
```

Result:

`app/Http/Handlers/Users/CreateUser/CreateUserHandler.php` (Namespace: `App\Http\Handlers\Users\CreateUser`)

`app/Http/Handlers/Users/CreateUser/CreateUserRequest.php` (Namespace: `App\Http\Handlers\Users\CreateUser`)

`app/Http/Handlers/Users/CreateUser/CreateUserAction.php` (Namespace: `App\Http\Handlers\Users\CreateUser`)

## 🚀 Usage

1. A Request that encapsulates the data needed for an operation.

```php

<?php

namespace App\Http\UseCases\User\GetUsers;

use Illuminate\Foundation\Http\FormRequest;

class GetUsersRequest extends FormRequest
 {
     /**
      * Determine if the user is authorized to make this request.
      */
     public function authorize(): bool
     {
         return true;
     }

     /**
      * Get the validation rules that apply to the request.
      *
      * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
      */
     public function rules(): array
     {
         return [
             //
         ];
     }
 }


```

2. A handler class that will process your command/query. This class must have a public `handle` method and be
   decorated with the `#[RequestHandler]` attribute.

```php

<?php

namespace App\Http\UseCases\User\GetUsers;

use App\Repositories\UserRepositoryInterface;
use Ignaciocastro0713\CqbusMediator\Attributes\RequestHandler;

#[RequestHandler(GetUsersRequest::class)]
class GetUsersHandler
{
    function __construct(private readonly UserRepositoryInterface $userRepository)
    {

    }

    public function handle(GetUsersRequest $request): array
    {
        $users = $this->userRepository->getUsers();

        if ($request->has("name")) {
            $users = array_filter($users, fn($user) => $user === $request->query("name"));
        }

        return array_values($users);
    }
}
```

3. send the Request
   You can inject the `Mediator` interface into your controllers, services, or anywhere you need to send a command or
   query.

```php

<?php

namespace App\Http\Controllers;

use App\Handlers\Users\Queries\GetUsers\GetUsersQuery;
use Ignaciocastro0713\CqbusMediator\Contracts\Mediator; // Import the Mediator interface
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class UserController extends Controller
{
    public function __construct(private readonly Mediator $mediator)
    {
    
    }

    public function index(GetUsersRequest $request): JsonResponse
    {
        $users = $this->mediator->send($request);

        return response()->json($users);
    }

    public function store(CreateUserRequest $request)
    {        
        $result = $this->mediator->send($request);
        
        return response()->json($result, 201);
    }
    
}

```

## 🚀 Usage Handler With Action Controller

- You can create your own Action class using the `AsAction` trait:

```php
namespace App\Http\UseCases\User\GetUsersAction;

use Ignaciocastro0713\CqbusMediator\Traits\AsAction;

class GetUsersAction
{
    use AsAction;

    public function __construct(private readonly Mediator $mediator)
    {
    }

    public function handle(GetUsersRequest $request): JsonResponse
    {
        $users = $this->mediator->send($request);

        return response()->json($users);
    }
}
```

- Register the action class as a controller in your routes file:

```php

Route::get('/users', GetUsersAction::class);
```

- Or Registering routes directly in the action:

```php
class GetUsersAction
{
    use AsAction;

    public static function route(Router $router): void
    {
        $router->get('api/users', static::class);
    }
    
    /*..*/
}

```

**Now, all routes using actions with the `AsAction` trait can be registered as route**

---

**Note:**

- The service provider will only decorate controllers that use the `AsAction` trait.

### How to use global pipelines

1. **Define your pipelines class:**  
   The pipeline should implement a `handle($request, \Closure $next)` method.

   ```php
   namespace App\Pipelines;

   class LoggingPipeline
   {
       public function handle($request, \Closure $next)
       {
           \Log::info('Mediator request received', ['request' => $request]);
           return $next($request);
       }
   }
   ```

2. **Register your pipelines in `config/mediator.php`:**

   ```php
   return [
       'handler_paths' => app_path(),
   
       'pipelines' => [
           App\Pipelines\LoggingPipeline::class,
           // Add more pipelines classes here
       ],
   ];
   ```

## ⚙️ Configuration and Optimization for Production

To get the most out of the package, it is highly recommended to use the new caching commands in production.

### Artisan Commands

The package provides two commands:

`php artisan mediator:cache`: Scans your handler directories and creates an optimized cache file in
bootstrap/cache/mediator_handlers.php.

`php artisan mediator:clear`: Deletes the cache file.

### Recommended Deployment Workflow

To ensure your application in production always runs with maximum performance, integrate the following steps into your
deployment script:

```bash

# Clear any existing handler cache (if any)

php artisan mediator:clear

# Generate the new cache file with the latest handlers

php artisan mediator:cache
```

This way, the expensive file scanning is performed only once during the build process, and the runtime in production is
optimized for every request.

## 🧪 Running Tests

To run the tests for this package, use the following command in your project root:

```bash
vendor/bin/pest
```

## 🎨 Fixing Code Style

To fix the code style (including risky rules), run:

```bash
./vendor/bin/php-cs-fixer fix --allow-risky=yes
```

## 🤝 Contributing

Feel free to open issues or submit pull requests on
the [GitHub repository](https://github.com/IgnacioCastro0713/cqbus-mediator).

## 📄 License

This package is open-sourced software licensed under the MIT license.
