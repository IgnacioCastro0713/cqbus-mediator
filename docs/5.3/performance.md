# Production & Performance

CQBus Mediator is designed with performance in mind. Because it relies on PHP Attributes for discovery, it must scan your application's directories to find `#[RequestHandler]`, `#[EventHandler]`, and routing attributes.

While this auto-discovery is incredibly convenient for development (zero configuration), scanning the file system on every request in a production environment is inefficient.

## Caching

To eliminate discovery overhead, you **must** cache the mediator routes and handlers during your production deployment process.

```bash
php artisan mediator:cache
```

Once cached, the package will read directly from the generated PHP array, dropping the discovery time to virtually zero.

### Benchmarks

| Benchmark | Mode (Time) | Memory |
|:----------|:-----------:|:-------|
| **Handler Discovery (Source)** | ~43.20 ms | 4.67 MB |
| **Handler Discovery (Cached)** | **~0.07 ms** | 4.65 MB |
| **Mediator Dispatch (Simple)** | ~0.08 ms | 13.34 MB |

As you can see, caching reduces the discovery time from `~43ms` to `~0.07ms`.

## Deployment Script Example

If you use a deployment script (like Laravel Envoyer, GitHub Actions, or a bash script), ensure you add the `mediator:cache` command alongside your standard Laravel optimization commands:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan mediator:cache # <--- Add this!
```