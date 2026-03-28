# Upgrading To 6.1

CQBus Mediator v6.1 is a feature-rich, non-breaking minor release that brings more control over route registration and pipeline execution.

## New Features

### 1. Segregated Pipelines
Previously, `global_pipelines` ran for *both* Requests (Commands/Queries) and Notifications (Events). In `v6.1`, we've introduced scoped pipelines in your `config/mediator.php`:

- **`request_pipelines`**: Run only when you dispatch via `$mediator->send(...)`.
- **`notification_pipelines`**: Run only when you dispatch via `$mediator->publish(...)`.

This allows you to easily apply logic (like a database transaction wrapper) only to Requests, without affecting Notifications.

*Note: If you use the `#[SkipGlobalPipelines]` attribute on a handler, it will skip `global_pipelines`, `request_pipelines`, and `notification_pipelines`.*

### 2. Action Registration Priority (`#[Priority]`)
If you have overlapping routes in your Action classes (e.g., `/api/users/current` and `/api/users/{user}`), Laravel's router requires the more specific route to be registered first. 

You can now explicitly set the registration priority using the `#[Priority]` attribute on your Action class.

```php
use Ignaciocastro0713\CqbusMediator\Attributes\Routing\Api;
use Ignaciocastro0713\CqbusMediator\Attributes\Routing\Priority;

#[Api]
#[Priority(10)] // Higher priority registers first by default
class GetCurrentUserAction { /* ... */ }
```

You can change the global sorting direction in `config/mediator.php` using the `route_priority_direction` key (defaults to `'desc'`).

For large projects, you can use the optional `group` argument to create isolated sorting contexts (e.g. `#[Priority(10, group: 'users')]`). Grouped priorities are sorted alphabetically by group name first, and then by their numeric priority.
### 3. Global Route Patterns Support
Laravel's `Route::pattern()` definitions are now fully supported and automatically applied to your Action classes when they are registered via the mediator.

## Upgrade Steps

1. Run `composer require ignaciocastro0713/cqbus-mediator:^6.1`
2. **(Optional)** Publish the updated configuration file to see the new pipeline arrays and priority direction:
   ```bash
   php artisan vendor:publish --tag=mediator-config --force
   ```
3. If you cache the mediator in production, don't forget to run `php artisan mediator:clear` and `php artisan mediator:cache`.
