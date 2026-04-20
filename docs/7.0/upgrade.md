# Upgrading To 7.0

CQBus Mediator v7.0 is a major release that improves API expressiveness, adds semantic CQRS attributes, improves error messages, and fixes a production cache bug.

## Breaking Changes

### 1. `publish()` returns `PublishResults` instead of `array`

The `Mediator::publish()` method (and `MediatorFake::publish()`) now returns a `PublishResults` object instead of a plain PHP array.

**Most existing code will continue to work unchanged** because `PublishResults` implements `ArrayAccess`, `Countable`, and `IteratorAggregate`:

```php
// These patterns continue to work:
foreach ($results as $handler => $value) { ... }  // ✅ IteratorAggregate
count($results);                                   // ✅ Countable
$results[MyHandler::class];                        // ✅ ArrayAccess
```

**Update these patterns** if they exist in your code:

```php
// ❌ Breaks:
is_array($results);          // → use $results instanceof PublishResults
array_keys($results);        // → use $results->handlerClasses()
array $r = $mediator->publish(...);  // → remove array type-hint

// ✅ New API:
$results->get(MyHandler::class);   // typed result retrieval
$results->all();                   // raw array
$results->isEmpty();               // check if no handlers ran
$results->handlerClasses();        // list of handler FQCNs
```

### 2. Pipeline cache file format changed

The `bootstrap/cache/mediator.php` format for pipeline keys has changed. After upgrading, regenerate the cache:

```bash
php artisan mediator:clear
php artisan mediator:cache
```

## New Features

### 1. Semantic CQRS attributes: `#[CommandHandler]` and `#[QueryHandler]`

Two new attributes alias `#[RequestHandler]` with semantic meaning:

```php
use Ignaciocastro0713\CqbusMediator\Attributes\Handlers\CommandHandler;
use Ignaciocastro0713\CqbusMediator\Attributes\Handlers\QueryHandler;

// Command: mutates state, returns void or an ID
#[CommandHandler(CreateOrderCommand::class)]
class CreateOrderHandler
{
    public function handle(CreateOrderCommand $cmd): string { ... }
}

// Query: only reads state, returns data
#[QueryHandler(GetOrderQuery::class)]
class GetOrderHandler
{
    public function handle(GetOrderQuery $q): Order { ... }
}
```

### 2. `PublishResults` typed API

```php
$results = $mediator->publish(new OrderShippedEvent($orderId));

// New typed methods:
$results->get(NotifyCustomerHandler::class);  // specific handler result
$results->handlerClasses();                   // all handler FQCNs
$results->isEmpty();                          // true if no handlers ran
```

### 3. `mediator:list` now shows the Pipelines column

```
+----------------------------+-------------------------+-------------------+
| Request                    | Handler                 | Pipelines         |
+----------------------------+-------------------------+-------------------+
| CreateOrderCommand         | CreateOrderHandler      | LoggingPipeline   |
| GetOrderQuery              | GetOrderHandler         | (none)            |
+----------------------------+-------------------------+-------------------+
```

### 4. Better error messages

- `InvalidRequestClassException` now tells you which attribute (`#[CommandHandler]`, `#[QueryHandler]`, `#[RequestHandler]`, or `#[Notification]`) references a non-existent class.
- `InvalidPipelineException` (new) is thrown at boot time when a pipeline class in `config/mediator.php` does not exist, with actionable suggestions.

### 5. Performance: pipeline cache bug fix

In versions before 7.0, the pipeline pre-computation in `mediator:cache` used incorrect cache keys, causing `ReflectionClass` calls on every dispatch even in production. This is now fixed. After upgrading and regenerating the cache, handlers will benefit from zero-reflection dispatch in production.

## Upgrade Steps

1. Run `composer require ignaciocastro0713/cqbus-mediator:^7.0`
2. Search your codebase for `is_array($result)` or `array_keys($result)` where `$result` comes from `publish()` and update them (see Breaking Changes above).
3. Remove any strict `array` type-hints on variables receiving `publish()` results.
4. Regenerate the mediator cache:
   ```bash
   php artisan mediator:clear
   php artisan mediator:cache
   ```
5. Optionally replace `#[RequestHandler]` with `#[CommandHandler]` or `#[QueryHandler]` for semantic clarity (no functional change required).
