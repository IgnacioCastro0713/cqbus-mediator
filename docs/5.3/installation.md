# Installation

**CQBus Mediator** is a lightweight, zero-configuration Command/Query Bus for Laravel. It simplifies your application architecture by decoupling controllers from business logic using the Mediator pattern (CQRS).

## Requirements
- PHP 8.2+
- Laravel 11.0+

## Installing via Composer

Install the package via Composer:

```bash
composer require ignaciocastro0713/cqbus-mediator
```

The package is auto-discovered by Laravel. 

## Configuration (Optional)

You can optionally publish the configuration file to customize the behavior of the Mediator (like where it searches for handlers or which global pipelines to run):

```bash
php artisan vendor:publish --tag=mediator-config
```

> **Tip:** If you use a custom architecture like DDD (e.g., a `src/` or `Domain/` folder instead of `app/`), you can tell the Mediator where to discover your handlers by updating the `handler_paths` array in the published `config/mediator.php`.