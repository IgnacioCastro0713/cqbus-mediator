# Event Bus (Publish/Subscribe)

Multiple notifications can respond to the same event simultaneously. This is the **1-to-N** pattern.

`publish()` returns a `PublishResults` object — a typed wrapper over the handler results:

```php
$results = $mediator->publish(new UserRegisteredEvent($userId, $email));

// Typed API:
$results->get(SendWelcomeEmailNotification::class); // result from a specific handler
$results->handlerClasses();                          // all handler FQCNs that ran
$results->isEmpty();                                 // true if no handlers were subscribed
$results->count();                                   // number of handlers that ran

// Backward-compatible usage still works:
foreach ($results as $handler => $value) { ... }
count($results);
$results[SendWelcomeEmailNotification::class];
```

## Creating an Event and Notifications

### 1. Scaffold your Event Logic
We provide a dedicated command to scaffold an Event and its initial Notification:

```bash
php artisan make:mediator-notification UserRegisteredNotification
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

```php [SendWelcomeEmailNotification.php]
use Ignaciocastro0713\CqbusMediator\Attributes\Handlers\Notification;
use App\Http\Events\UserRegistered\UserRegisteredEvent;

#[Notification(UserRegisteredEvent::class, priority: 3)]
class SendWelcomeEmailNotification
{
    public function handle(UserRegisteredEvent $event): void
    {
        // Priority 3: Runs before LogUserRegistrationNotification
        Mail::to($event->email)->send(new WelcomeEmail());
    }
}
```

```php [LogRegistrationNotification.php]
use Ignaciocastro0713\CqbusMediator\Attributes\Handlers\Notification;
use App\Http\Events\UserRegistered\UserRegisteredEvent;

#[Notification(UserRegisteredEvent::class)]
class LogUserRegistrationNotification
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
use Ignaciocastro0713\CqbusMediator\Support\PublishResults;

public function __construct(private readonly Mediator $mediator) {}

public function registerUser() 
{
    // ... logic ...

    /** @var PublishResults $results */
    $results = $this->mediator->publish(new UserRegisteredEvent($userId, $email));

    // Typed API — access results by handler class:
    $emailResult = $results->get(SendWelcomeEmailNotification::class);

    // Inspect what ran:
    $results->handlerClasses(); // ['SendWelcomeEmailNotification', 'LogUserRegistrationNotification']
    $results->isEmpty();        // false — two handlers ran
    $results->count();          // 2

    // Legacy array access still works:
    foreach ($results as $handler => $value) { ... }
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
