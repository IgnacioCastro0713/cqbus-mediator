---
# Esta es tu Landing Page. Se renderiza con un diseño espectacular por defecto en VitePress.
layout: home

hero:
  name: "CQBus Mediator"
  text: "for Laravel"
  tagline: "A modern CQRS Mediator using PHP 8 Attributes, auto-discovery, and routing pipelines."
  actions:
    - theme: brand
      text: Get Started
      link: /7.0/installation
    - theme: alt
      text: View on GitHub
      link: https://github.com/IgnacioCastro0713/cqbus-mediator

features:
  - title: ⚡ Zero Config
    details: Automatically discovers Handlers and Events using modern PHP Attributes (`#[RequestHandler]`, `#[EventHandler]`). No arrays to map.
  - title: 📢 Dual Pattern Support
    details: Seamlessly handle both Command/Query (one-to-one) and Event Bus (one-to-many) patterns in the same package.
  - title: 🎮 Attribute Routing
    details: Manage routes, prefixes, and middleware directly in your Action classes—say goodbye to bloated route files.
---

<br>

<div style="max-width: 800px; margin: 0 auto; text-align: center;">
  <h2>Stop writing Fat Controllers. Start writing Actions.</h2>
  <p style="color: var(--vp-c-text-2); margin-bottom: 2rem;">Decouple your business logic, routing, and side effects instantly.</p>
</div>

```php
// app/Http/Actions/RegisterUserAction.php
use Ignaciocastro0713\CqbusMediator\Attributes\Routing\Api;
use Ignaciocastro0713\CqbusMediator\Attributes\Pipelines\Pipeline;
use Ignaciocastro0713\CqbusMediator\Traits\AsAction;

#[Api]
#[Pipeline(DatabaseTransactionPipeline::class)]
class RegisterUserAction
{
    use AsAction;

    public function __construct(private readonly Mediator $mediator) {}

    public static function route(Router $router): void
    {
        $router->post('/register');
    }

    public function handle(RegisterUserRequest $request): JsonResponse
    {
        $user = $this->mediator->send($request); 
        $this->mediator->publish(new UserRegisteredEvent($user)); 
        
        return response()->json($user, 201);
    }
}
```
