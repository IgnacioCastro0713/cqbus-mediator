# Pipelines (Middleware)

Pipelines allow you to wrap your Handlers in reusable logic, such as Database Transactions, Logging, or Caching. They work exactly like Laravel Middleware but operate at the Mediator level, ensuring they run regardless of how the request was dispatched (HTTP, Console, Jobs).

## 1. Global Pipelines

Global pipelines run before *every* handler dispatched via `send()`. Register them in the `config/mediator.php` file.

```php
// config/mediator.php
return [
    'pipelines' => [
        \App\Pipelines\LoggingPipeline::class,
    ],
];
```

A pipeline class is just an invokable class that receives the request and a `Closure` to pass execution to the next layer:

```php
namespace App\Pipelines;

use Closure;
use Illuminate\Support\Facades\Log;

class LoggingPipeline
{
    public function handle(mixed $request, Closure $next): mixed
    {
        Log::info('Handling request: ' . get_class($request));
        
        $response = $next($request);
        
        Log::info('Request handled successfully');
        
        return $response;
    }
}
```

## 2. Handler-level Pipelines

You can apply pipelines to specific handlers using the `#[Pipeline]` attribute. These run *after* the global pipelines and *before* the handler itself.

```php
use Ignaciocastro0713\CqbusMediator\Attributes\Pipeline;
use Ignaciocastro0713\CqbusMediator\Attributes\RequestHandler;

#[RequestHandler(CreateOrderRequest::class)]
#[Pipeline(TransactionPipeline::class)] // Will run inside a DB transaction
class CreateOrderHandler
{
    public function handle(CreateOrderRequest $request): Order
    {
        return Order::create($request->validated());
    }
}
```

## 3. Skipping Global Pipelines

Sometimes a specific handler shouldn't trigger the global pipelines (e.g., a simple health check query that shouldn't be logged to avoid noise). Use `#[SkipGlobalPipelines]` to bypass them.

```php
use Ignaciocastro0713\CqbusMediator\Attributes\RequestHandler;
use Ignaciocastro0713\CqbusMediator\Attributes\SkipGlobalPipelines;

#[RequestHandler(HealthCheckRequest::class)]
#[SkipGlobalPipelines]
class HealthCheckHandler
{
    public function handle(HealthCheckRequest $request): string
    {
        return 'OK'; // Bypasses global logging or transactions
    }
}
```