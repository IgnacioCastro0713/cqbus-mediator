# Testing Fakes

Testing applications built with the Mediator pattern should be simple. You shouldn't need to execute complex business logic, database queries, or external API calls just to verify that a controller or action dispatched the correct command.

To solve this, CQBus Mediator provides a powerful `Mediator` Facade with a built-in fake.

## Testing with `PublishResults`

When testing real dispatches (without faking), `publish()` returns a `PublishResults` object. Use its typed API in assertions:

```php
use Ignaciocastro0713\CqbusMediator\Support\PublishResults;

it('publishes an event and returns typed results', function () {
    $results = $this->mediator->publish(new OrderShippedEvent($orderId));

    expect($results)->toBeInstanceOf(PublishResults::class)
        ->and($results->isEmpty())->toBeFalse()
        ->and($results->get(NotifyCustomerHandler::class))->toBe('email_sent');
});
```

> **Note:** `Mediator::fake()` returns `new PublishResults()` (empty) — the fake records the event but does not execute handlers.

## Faking the Mediator

You can instruct the Mediator to swap itself with a test double by calling the `Mediator::fake()` method. Once faked, the Mediator will **intercept and record** all requests and events instead of executing their handlers.

You can then use expressive assertions to verify your application's behavior.

```php
use Ignaciocastro0713\CqbusMediator\Facades\Mediator;
use App\Http\Handlers\RegisterUser\RegisterUserRequest;
use App\Http\Events\UserRegistered\UserRegisteredEvent;

it('dispatches the correct request and events', function () {
    // 1. Swap the real mediator with the fake
    Mediator::fake();

    // 2. Perform your application action (e.g., an HTTP request)
    $response = $this->postJson('/api/register', [
        'email' => 'test@example.com',
        'password' => 'secret123'
    ]);

    $response->assertStatus(201);

    // 3. Assert a specific Request was sent
    Mediator::assertSent(RegisterUserRequest::class);
    
    // 4. Assert a specific Event was published
    Mediator::assertPublished(UserRegisteredEvent::class);
});
```

## Advanced Assertions (Closures)

Sometimes you need to verify not just *that* a request was sent, but that it contained the correct data. All assertion methods accept a closure (truth test) that receives the intercepted object.

```php
it('sends the request with the correct formatted email', function () {
    Mediator::fake();

    $this->postJson('/api/register', [
        'email' => '  TEST@EXAMPLE.com  ', // Messy input
    ]);

    // Verify the request was sent and the data was properly formatted
    Mediator::assertSent(function (RegisterUserRequest $request) {
        return $request->email === 'test@example.com'; 
    });
});
```

## Available Assertions

Here is the full list of assertions available on the faked Mediator:

| Method | Description |
|--------|-------------|
| `Mediator::assertSent($request)` | Asserts a specific request class or closure truth test was sent. |
| `Mediator::assertNotSent($request)` | Asserts a specific request was **not** sent. |
| `Mediator::assertPublished($event)` | Asserts a specific event class or closure truth test was published. |
| `Mediator::assertNotPublished($event)` | Asserts a specific event was **not** published. |
| `Mediator::assertNothingSent()` | Asserts that absolutely zero requests were sent. |
| `Mediator::assertNothingPublished()` | Asserts that absolutely zero events were published. |

## Inspecting the Records

If you need to perform complex assertions or inspect multiple dispatched items, you can retrieve the underlying Collections using the `sent()` and `published()` methods:

```php
it('dispatches multiple notifications', function () {
    Mediator::fake();

    // ... run code that publishes multiple events ...

    // Get a collection of all UserRegisteredEvent instances that were published
    $events = Mediator::published(UserRegisteredEvent::class);

    expect($events)->toHaveCount(3);
    expect($events->first()->userId)->toBe(1);
});
```
