# Installation

Welcome to **CQBus Mediator**! 🚀 

If you are tired of massive, unmaintainable controllers and tangled business logic in your Laravel applications, you are in the right place. This package brings the power of the **CQRS (Command/Query Responsibility Segregation)** pattern to your app with zero friction, utilizing modern PHP 8 Attributes.

## Requirements

Before you start, ensure your environment meets the following:
- **PHP:** 8.2 or higher
- **Laravel:** 11.0, 12.0, or 13.0

## Installing via Composer

Pull the package into your project using Composer. Laravel's package discovery will handle the rest automatically.

```bash
composer require ignaciocastro0713/cqbus-mediator
```

## Configuration (Optional)

Out of the box, CQBus Mediator is ready to go. However, if you want to tweak its behavior—like defining custom directories for your handlers or registering global pipelines—you should publish the configuration file:

```bash
php artisan vendor:publish --tag=mediator-config
```

::: tip 🏗️ Using Domain-Driven Design (DDD)?
By default, the package scans your `app/` directory for Handlers and Actions. If you use a custom architecture (e.g., a `src/Domain/` folder), simply update the `handler_paths` array in the published `config/mediator.php` file to point to your custom directories.
:::

## What's New in v7.0

- **Semantic CQRS attributes** — `#[CommandHandler]` and `#[QueryHandler]` as expressive aliases for `#[RequestHandler]`.
- **`PublishResults` typed API** — `publish()` now returns a typed object instead of a plain array, with methods like `get()`, `handlerClasses()`, and `isEmpty()`.
- **Better error messages** — `InvalidRequestClassException` now names the offending attribute; new `InvalidPipelineException` validates pipelines at boot time.
- **Pipeline cache fix** — Zero-reflection dispatch in production is now guaranteed. Regenerate your cache after upgrading.
- **`mediator:list` improvements** — Shows the effective pipeline stack per handler in a new Pipelines column.

See the [Upgrade Guide](/7.0/upgrade) for migration steps.
