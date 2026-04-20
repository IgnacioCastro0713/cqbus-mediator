---
layout: home

hero:
  name: "CQBus Mediator"
  text: "Clean architecture for Laravel"
  tagline: "Decouple your commands, queries, and events with zero configuration. Auto-discovery, attribute routing, and pipelines — out of the box."
  actions:
    - theme: brand
      text: Get Started →
      link: /7.0/installation
    - theme: alt
      text: View on GitHub
      link: https://github.com/IgnacioCastro0713/cqbus-mediator

features:
  - icon: 🚀
    title: Zero Configuration
    details: Drop in the package and go. Handlers, notifications, and actions are discovered automatically — no registration arrays, no service providers to edit.

  - icon: ⚡
    title: CQRS & Event Bus
    details: First-class support for Commands, Queries, and Events in one package. Use <code>#[CommandHandler]</code>, <code>#[QueryHandler]</code>, and <code>#[Notification]</code> to communicate intent clearly.

  - icon: 🛣️
    title: Attribute Routing
    details: Define your route right next to your handler. <code>#[Api]</code>, <code>#[Prefix]</code>, <code>#[Middleware]</code> — no more hunting through bloated route files.

  - icon: 🔗
    title: Layered Pipelines
    details: Wrap every dispatch in reusable middleware — globally, per type, or per handler. Works seamlessly whether triggered by HTTP, CLI, or a queued job.

  - icon: 🧪
    title: Built for Testing
    details: <code>Mediator::fake()</code> intercepts and records all dispatches so you can assert commands and events without running a single handler.

  - icon: ⚙️
    title: Production Ready
    details: Cache the entire mediator registry with <code>php artisan mediator:cache</code> for zero-reflection dispatch in production — from 157ms boot to 0.06ms.
---

<div class="home-cta-text">
  <h2>Stop writing Fat Controllers.<br>Start writing Actions.</h2>
  <p>Decouple your business logic, routing, and side effects instantly.</p>
</div>

<div class="home-code-section">
  <div class="home-code-header">
    <span class="home-code-label">The full CQRS flow — in one Action class</span>
  </div>

```php
// app/Http/Actions/RegisterUserAction.php

#[Api] // ⚡ Applies 'api' middleware group AND 'api/' prefix automatically
class RegisterUserAction
{
    use AsAction;

    public function __construct(private readonly Mediator $mediator) {}

    public static function route(Router $router): void
    {
        $router->post('/register');                    // POST /api/register
    }

    public function handle(RegisterUserRequest $request): JsonResponse
    {
        $user = $this->mediator->send($request);                       // → RegisterUserHandler
        $this->mediator->publish(new UserRegisteredEvent($user->id));  // → N notifications

        return response()->json($user, 201);
    }
}
```

</div>
