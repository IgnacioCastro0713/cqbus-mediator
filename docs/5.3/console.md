# Console Commands

The package provides several Artisan commands to speed up your workflow and manage the mediator.

## 🛠️ Generation Commands

Scaffold your classes instantly. All generation commands support a `--root` option to change the base directory (e.g., `--root=Domain/Users`).

| Command | Description | Variations/Options |
|---------|-------------|--------------------|
| `make:mediator-handler` | Creates a Request and Handler class. | `--action` (also generates an Action class) |
| `make:mediator-action` | Creates an Action and Request class. | |
| `make:mediator-event-handler`| Creates an Event and its Handler class. | |

**Examples:**
```bash
php artisan make:mediator-handler RegisterUserHandler --action
php artisan make:mediator-action CreateOrderAction --root=Orders
php artisan make:mediator-event-handler UserRegisteredHandler
```

## 🔍 Information Commands

### `mediator:list`
View all discovered or cached handlers, event handlers, and actions in a clean console table.

```bash
php artisan mediator:list
```

**Options:**
- `--handlers`: List only Request Handlers.
- `--events`: List only Event Handlers.
- `--actions`: List only Actions.

**Output Example:**
```text
  Handlers
+------------------------------------------+------------------------------------------+
| Request                                  | Handler                                  |
+------------------------------------------+------------------------------------------+
| App\Http\Handlers\RegisterUserRequest    | App\Http\Handlers\RegisterUserHandler    |
+------------------------------------------+------------------------------------------+
```

## 🚀 Production Optimization

In development, the package scans your directories to auto-discover attributes. In production, this file-system scanning overhead should be eliminated by caching the discovery results.

```bash
# Creates the cache file
php artisan mediator:cache 

# Clears the existing cache file
php artisan mediator:clear 
```
