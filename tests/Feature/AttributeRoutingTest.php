<?php

use Ignaciocastro0713\CqbusMediator\Managers\ActionDecoratorManager;
use Illuminate\Support\Facades\Route;

it('applies prefix and middleware attributes to routes', function () {
    // Point to our fixtures
    config()->set('mediator.handler_paths', [__DIR__ . '/../Fixtures']);

    // Re-run boot to pick up new paths and attributes
    app(ActionDecoratorManager::class)->boot();

    // Verify route registration
    $route = Route::getRoutes()->getByAction(Tests\Fixtures\AttributeAction::class);

    expect($route)->not->toBeNull()
        ->and($route->uri())->toBe('api/v1/attribute-test')
        ->and($route->middleware())->toContain('guest');
});

it('applies multiple middlewares correctly', function () {
    config()->set('mediator.handler_paths', [__DIR__ . '/../Fixtures']);
    app(ActionDecoratorManager::class)->boot();

    $route = Route::getRoutes()->getByAction(Tests\Fixtures\AuthAction::class);

    expect($route)->not->toBeNull()
        ->and($route->uri())->toBe('secure/dashboard')
        ->and($route->middleware())->toContain('web', 'auth');
});

it('applies middleware without prefix', function () {
    config()->set('mediator.handler_paths', [__DIR__ . '/../Fixtures']);
    app(ActionDecoratorManager::class)->boot();

    $route = Route::getRoutes()->getByAction(Tests\Fixtures\NoPrefixAction::class);

    expect($route)->not->toBeNull()
        ->and($route->uri())->toBe('root-api')
        ->and($route->middleware())->toContain('api');
});
