# CQBus Mediator for Laravel
A simple, extensible, and configurable Command/Query Bus (Mediator) implementation for Laravel applications. This package helps you implement the Command/Query Responsibility Segregation (CQRS) and Mediator patterns, promoting cleaner, more maintainable code by separating concerns and decoupling components.

## ✨ Features
- **Attribute-Based Handler Discovery**: Define your command/query handlers using a simple PHP 8 attribute (`#[RequestHandler]`).

- **Configuration-Driven Scanning**: Easily configure the directories and namespace mappings where your handlers are located via a dedicated configuration file.

- **Automatic Dependency Injection**: Handlers are resolved from the Laravel service container, allowing for seamless dependency injection.

- **Clear Separation of Concerns**: Decouples the sender from the handlers, improving testability and code organization.


## 🚀 Installation
You can install this package via Composer.

1. Require the Package:
In your Laravel project's root directory, run:

```pwsh
composer require ignaciocastro0713/cqbus-mediator
```

2. Publish the Configuration File:
The package comes with a configurable file that allows you to define handler discovery paths and namespaces. Publish it using the Artisan command:

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
        // app_path('Http/Controllers'), // Uncomment if invokable controllers are handlers
        // app_path('Features'),       // If you follow a "features" directory structure (e.g., App\Features)
    ],

    /*
    |--------------------------------------------------------------------------
    | Handler Namespace Mappings
    |--------------------------------------------------------------------------
    |
    | This array provides explicit mappings from root namespaces to their
    | corresponding file paths. This is useful for accurately deriving
    | the FQCN (Fully Qualified Class Name) from a file path when the
    | default 'App\' mapping is not sufficient.
    |
    | The key should be the root namespace (e.g., 'App\'), and the value
    | should be the absolute path to its corresponding directory.
    |
    | Example: 'App\Handlers\' => app_path('Handlers')
    |
    */
    'handler_namespaces' => [
        'App\\' => app_path(), // Default Laravel app namespace mapping (e.g., App\Models, App\Http)
        // 'App\\Features\\' => app_path('Features'), // Example if you have App\Features as a root namespace for handlers
    ],

    /*
    |--------------------------------------------------------------------------
    | Exclude Paths (Optional)
    |--------------------------------------------------------------------------
    |
    | Define patterns for paths or file names to exclude from scanning.
    | This uses Symfony's Finder exclude() method syntax.
    |
    */
    'exclude_paths' => [
        'Tests', // Exclude test directories (e.g., app/Features/Users/Tests)
        'stubs', // Exclude stub files
        // Add more patterns like 'Traits' if you want to skip directories containing utility classes
    ],
];
```

Important: Adjust handler_paths to include all directories where your command/query handlers are located. Ensure handler_namespaces correctly maps your root namespaces to their base directories so the package can accurately determine the Fully Qualified Class Name (FQCN) of your handler classes.

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

#[RequestHandler(requestClass: GetUsersQuery::class)]
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
    public function __construct(protected Mediator $mediator)
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



## 🤝 Contributing
Feel free to open issues or submit pull requests on the [GitHub repository](https://github.com/IgnacioCastro0713/cqbus-mediator).

## 📄 License
This package is open-sourced software licensed under the MIT license.
