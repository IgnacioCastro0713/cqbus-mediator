# CQBus Mediator for Laravel

[![run-tests](https://github.com/ignaciocastro0713/cqbus-mediator/actions/workflows/run-tests.yml/badge.svg)](https://github.com/ignaciocastro0713/cqbus-mediator/actions/workflows/run-tests.yml)
<a href="https://packagist.org/packages/ignaciocastro0713/cqbus-mediator" target="_blank"><img src="https://img.shields.io/packagist/v/ignaciocastro0713/cqbus-mediator.svg?style=flat-square"/></a>
<a href="https://packagist.org/packages/ignaciocastro0713/cqbus-mediator" target="_blank"><img src="https://img.shields.io/packagist/dt/ignaciocastro0713/cqbus-mediator.svg?style=flat-square"/></a>
<a href="https://packagist.org/packages/ignaciocastro0713/cqbus-mediator" target="_blank"><img src="https://img.shields.io/packagist/l/ignaciocastro0713/cqbus-mediator.svg?style=flat-square"/></a>

A simple, extensible, and configurable Command/Query Bus (Mediator) implementation for Laravel applications. This package helps you implement the Command/Query Responsibility Segregation (CQRS) with Mediator pattern, promoting cleaner, more maintainable code by separating concerns and decoupling components.

## ✨ Features
- **Attribute-Based Handler Discovery**: Define your command/query handlers using a simple PHP 8 attribute (`#[RequestHandler]`).

- **Configuration-Driven Scanning**: Easily configure the directories where your handlers are located via a dedicated configuration file.

- **Automatic Dependency Injection**: Handlers are resolved from the Laravel service container, allowing for seamless dependency injection.

- **Clear Separation of Concerns**: Decouples the sender from the handlers, improving testability and code organization.

- **Global pipelines (middleware) support**: that will apply to every request sent through the Mediator

## 🚀 Installation
You can install this package via Composer.

1. Require the Package:
In your Laravel project's root directory, run:

```pwsh
composer require ignaciocastro0713/cqbus-mediator
```

2. Publish the Configuration File:
The package comes with a configurable file that allows you to define handler discovery paths. Publish it using the Artisan command:

```pwsh
php artisan vendor:publish --tag=mediator-config
```

This will create `config/mediator.php` in your Laravel application.

## ⚙️ Configuration (`config/mediator.php`)
After publishing, you'll find a `mediator.php` file in your `config` directory. This file is crucial for discovers your handlers.

```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Handler Discovery Paths
    |--------------------------------------------------------------------------
    |
    | This array defines the directories where the MediatorService will scan
    | for command/request handlers. These paths are relative to your Laravel
    | application's base directory (typically the 'app' directory).
    |
    | Example: app_path('Handlers/Commands') would scan 'app/Handlers/Commands'.
    |
    */
    'handler_paths' => [
        app_path('Handlers'), // A common directory for all handlers
    ],

    /*
    |--------------------------------------------------------------------------
    | Global Pipelines
    |--------------------------------------------------------------------------
    |
    | The global pipelines (middleware) that will be applied to every request
    | sent through the Mediator. Each class should have a handle($request, Closure $next) method.
    |
    | Example:
    |   App\Pipelines\LoggingMiddleware::class,
    |   App\Pipelines\AuthMiddleware::class,
    */
    'pipelines' => [
    ],
];

```

Important: Adjust handler_paths to include all directories where your command/query handlers are located.

## 🚀 Usage
1. Define your Command/Query (Request)
A request is a simple DTO (Data Transfer Object) that encapsulates the data needed for an operation.
```php

<?php

namespace App\Handlers\Users\Queries\GetUsers;

// Example: app/Handlers/Users/Queries/GetUsers/GetUsersQuery.php

class GetUsersQuery
{
    public function __construct(public ?string $search = null)
    {
        //
    }
}
```

2. Define your Handler
Create a handler class that will process your command/query. This class must have a public `handle` method and be decorated with the `#[RequestHandler]` attribute.
```php

<?php

namespace App\Handlers\Users\Queries\GetUsers;

use Ignaciocastro0713\CqbusMediator\Attributes\RequestHandler;

// Example: app/Handlers/Users/Queries/GetUsers/GetUsersQueryHandler.php

#[RequestHandler(GetUsersQuery::class)]
class GetUsersQueryHandler
{
    // You can inject dependencies here, e.g., a UserRepository
    public function __construct()
    {
        //
    }

    public function handle(GetUsersQuery $query)
    {
        // Your logic to retrieve users based on $query->search
        // This is where you would interact with your database, services, etc.
        if ($query->search) {
            return ['Filtered User for: ' . $query->search];
        }

        return ['User 1', 'User 2', 'User 3'];
    }
}
```

3. send the Command/Query
You can inject the `Mediator` interface into your controllers, services, or anywhere you need to send a command or query.

```php

<?php

namespace App\Http\Controllers;

use App\Handlers\Users\Queries\GetUsers\GetUsersQuery;
use Ignaciocastro0713\CqbusMediator\Contracts\Mediator; // Import the Mediator interface
use Illuminate\Http\Request;
use Illuminate\Routing\Controller; // Use Illuminate\Routing\Controller

class UserController extends Controller
{
    public function __construct(private readonly Mediator $mediator)
    {
        // The Mediator instance is automatically injected by Laravel
    }

    public function index(Request $request)
    {
        $query = new GetUsersQuery($request->input('search'));

        // send the query to its handler
        $users = $this->mediator->send($query);

        return response()->json($users);
    }

    // Example for a Command (assuming you have a CreateUserCommand and CreateUserCommandHandler)
    /*
    public function store(Request $request)
    {
        $command = new CreateUserCommand($request->input('name'), $request->input('email'));
        $result = $this->mediator->send($command);
        return response()->json($result, 201);
    }
    */
}

```

### How to use global pipelines

1. **Define your middleware class:**  
   The middleware should implement a `handle($request, \Closure $next)` method.

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

2. **Register your middleware in `config/mediator.php`:**

   ```php
   return [
       'handler_paths' => [
        app_path('Handlers')
       ],
       'pipelines' => [
           App\Pipelines\LoggingPipeline::class,
           // Add more middleware classes here
       ],
   ];
   ```

## 🤝 Contributing
Feel free to open issues or submit pull requests on the [GitHub repository](https://github.com/IgnacioCastro0713/cqbus-mediator).

## 📄 License
This package is open-sourced software licensed under the MIT license.
