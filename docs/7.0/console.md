# Console Commands

The package provides several Artisan commands to speed up your workflow and manage the mediator.

## 🛠️ Generation Commands

Scaffold your classes instantly. All generation commands support a `--root` option to change the base directory (e.g., `--root=Domain/Users`).

| Command | Description | Options |
|---------|-------------|---------|
| `make:mediator-handler` | Creates a Request and Handler class. | `--action` (adds Action), `--root=Dir` |
| `make:mediator-action` | Creates an Action and Request class. | `--root=Dir` |
| `make:mediator-notification`| Creates an Event and its Notification class. | `--root=Dir` |

**Examples:**
```bash
# Uses default root folder (Handlers/)
php artisan make:mediator-handler RegisterUserHandler --action

# Changes root folder to Orders/
php artisan make:mediator-action CreateOrderAction --root=Orders

# Changes root folder to Domain/Events/
php artisan make:mediator-notification UserRegisteredNotification --root=Domain/Events
```

## 🔍 Information Commands

### `mediator:list`
View all discovered or cached handlers, notifications, and actions in a clean console table.

```bash
php artisan mediator:list
```

**Options:**
- `--handlers`: List only Request Handlers.
- `--events`: List only Notifications.
- `--actions`: List only Actions.

**Output Example:**
```text
  Handlers
+------------------------------------------+------------------------------------------+-------------------+
| Request                                  | Handler                                  | Pipelines         |
+------------------------------------------+------------------------------------------+-------------------+
| App\Http\Handlers\RegisterUserRequest    | App\Http\Handlers\RegisterUserHandler    | LoggingPipeline   |
| App\Http\Handlers\GetUserRequest         | App\Http\Handlers\GetUserHandler         | (none)            |
+------------------------------------------+------------------------------------------+-------------------+
```

The **Pipelines** column shows the effective pipeline stack (global + type-specific + handler-level) for each handler. When a cache file is loaded in a non-production environment, a warning is displayed to remind you to clear it after code changes.

## 🚀 Production Optimization

In development, the package scans your directories to auto-discover attributes. In production, this file-system scanning overhead should be eliminated by caching the discovery results.

```bash
# Creates the cache file
php artisan mediator:cache 

# Clears the existing cache file
php artisan mediator:clear 
```
