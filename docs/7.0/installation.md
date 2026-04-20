# Installation

Welcome to **CQBus Mediator**! 🚀 

If you are tired of massive, unmaintainable controllers and tangled business logic in your Laravel applications, you are in the right place. This package brings the power of the **CQRS (Command/Query Responsibility Segregation)** pattern to your app with zero friction, utilizing modern PHP 8 Attributes.

## Requirements

Before you start, ensure your environment meets the following:
- **PHP:** 8.2 or higher
- **Laravel:** 11.0 or higher

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
