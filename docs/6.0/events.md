# Event Bus (Publish/Subscribe)

Multiple handlers can respond to the same event simultaneously. This is the **1-to-N** pattern.

## Creating an Event and Handlers

### 1. Scaffold your Event Logic
We provide a dedicated command to scaffold an Event and its initial Handler:

```bash
php artisan make:mediator-event-handler UserRegisteredHandler
```

### 2. Implementation Example

Below is an example of a broadcasted event with multiple listeners responding with different priorities.

::: code-group

```php [UserRegisteredEvent.php]
namespace App\Http\Events\UserRegistered;

class UserRegisteredEvent
{
    public function __construct(
        public int $userId,
        public string $email
    ) {}
}
```

```php [WelcomeEmailHandler.php]
use Ignaciocastro0713\CqbusMediator\Attributes\Handlers\EventHandler;
use App\Http\Events\UserRegistered\UserRegisteredEvent;

#[EventHandler(UserRegisteredEvent::class, priority: 3)]
class SendWelcomeEmailHandler
{
    public function handle(UserRegisteredEvent $event): void
    {
        // Priority 3: Runs before LogUserRegistrationHandler
        Mail::to($event->email)->send(new WelcomeEmail());
    }
}
```

```php [LogRegistrationHandler.php]
use Ignaciocastro0713\CqbusMediator\Attributes\Handlers\EventHandler;
use App\Http\Events\UserRegistered\UserRegisteredEvent;

#[EventHandler(UserRegisteredEvent::class)]
class LogUserRegistrationHandler
{
    public function handle(UserRegisteredEvent $event): void
    {
        // Default Priority (0)
        Log::info("User registered: {$event->userId}");
    }
}
```

```php [Usage.php]
use Ignaciocastro0713\CqbusMediator\Contracts\Mediator;

public function __construct(private readonly Mediator $mediator) {}

public function registerUser() 
{
    // ... logic ...

    // The event is sent to all registered handlers based on their priority
    $results = $this->mediator->publish(new UserRegisteredEvent($userId, $email));
    
    /* $results looks like:
       [
          'App\Http\Events\SendWelcomeEmailHandler' => null,
          'App\Http\Events\LogUserRegistrationHandler' => null
       ]
    */
}
```
:::

## Using the Facade

If you prefer not to use Dependency Injection via the `__construct` method, you can use the `Mediator` facade to publish events cleanly:

```php
use Ignaciocastro0713\CqbusMediator\Facades\Mediator;

public function registerUser() 
{
    // ... logic ...

    // The event is published via Facade
    $results = Mediator::publish(new UserRegisteredEvent($userId, $email));
}
```