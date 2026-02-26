# Production & Performance

CQBus Mediator is designed with performance in mind. Because it relies on PHP Attributes for discovery, it must scan your application's directories to find `#[RequestHandler]`, `#[EventHandler]`, and routing attributes.

While this auto-discovery is incredibly convenient for development (zero configuration), scanning the file system and reading attributes via Reflection on every request in a production environment is inefficient.

## Zero-Reflection Caching

To eliminate discovery and Reflection overhead, you **must** cache the mediator registry during your production deployment process.

```bash
php artisan mediator:cache
```

When you run this command, the package performs all the heavy lifting:
1. It scans your codebase for Handlers, Event Handlers, and Actions.
2. It resolves all `#[Pipeline]` and `#[SkipGlobalPipelines]` attributes.
3. It compiles everything into a single, flat, heavily optimized PHP array.

Once cached, the package will read directly from the generated file (`bootstrap/cache/mediator.php`). **No Reflection API is used at runtime when the cache exists**, dropping the overhead to micro-seconds.

### Benchmarks

| Benchmark | Source / Dev Mode | Cached (Production) | Improvement |
|:----------|:-----------:|:-------:|:-------:|
| **Discovery (Boot Phase)** | ~157.00 ms | **~0.06 ms** | ~2,500x Faster |
| **Reflection / Attribute Reading** | ~16.00 μs | **~4.00 μs** | ~4x Faster |
| **Simple Dispatch (`send`)** | - | **~68.00 μs** | Near Zero Overhead |

As you can see, caching reduces the boot discovery time from milliseconds to fractions of a millisecond, and eliminates the Reflection penalty entirely during the dispatch cycle.

## Deployment Script Example

If you use a deployment script (like Laravel Envoyer, GitHub Actions, or a bash script), ensure you add the `mediator:cache` command alongside your standard Laravel optimization commands:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan mediator:cache # <--- Add this!
```