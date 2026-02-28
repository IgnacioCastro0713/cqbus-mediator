<?php

use Ignaciocastro0713\CqbusMediator\Routing\ActionDecoratorManager;
use Illuminate\Support\Facades\Route;

it('applies prefix and middleware attributes to routes', function () {
    // Point to our fixtures
    config()->set('mediator.handler_paths', [__DIR__ . '/../Fixtures']);

    // Re-run boot to pick up new paths and attributes
    app(ActionDecoratorManager::class)->boot();

    // Verify route registration
    $route = Route::getRoutes()->getByAction(Tests\Fixtures\AttributeAction::class);

    expect($route)->not->toBeNull()
        ->and($route->uri())->toBe('api/api/v1/attribute-test')
        ->and($route->middleware())->toContain('api', 'guest');
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
        ->and($route->uri())->toBe('api/root-api')
        ->and($route->middleware())->toContain('api');
});

it('applies api group middleware and api prefix when ApiRoute attribute is used', function () {
    config()->set('mediator.handler_paths', [__DIR__ . '/../Fixtures']);
    app(ActionDecoratorManager::class)->boot();

    $route = Route::getRoutes()->getByAction(Tests\Fixtures\ApiRouteAction::class);

    expect($route)->not->toBeNull()
        // It should prepend 'api/' to the route defined in the fixture ('/api-route-test')
        ->and($route->uri())->toBe('api/api-route-test')
        ->and($route->middleware())->toContain('api');
});

it('applies web group middleware and no prefix when WebRoute attribute is used', function () {
    config()->set('mediator.handler_paths', [__DIR__ . '/../Fixtures']);
    app(ActionDecoratorManager::class)->boot();

    $route = Route::getRoutes()->getByAction(Tests\Fixtures\WebRouteAction::class);

    expect($route)->not->toBeNull()
        ->and($route->uri())->toBe('web-route-test')
        ->and($route->middleware())->toContain('web');
});

it('combines ApiRoute prefix with custom Prefix attribute correctly', function () {
    config()->set('mediator.handler_paths', [__DIR__ . '/../Fixtures']);
    app(ActionDecoratorManager::class)->boot();

    $route = Route::getRoutes()->getByAction(Tests\Fixtures\ApiWithCustomPrefixAction::class);

    expect($route)->not->toBeNull()
        // 'api' from #[Api] + 'v2/users' from #[Prefix] + 'test' from route()
        ->and($route->uri())->toBe('api/v2/users/test')
        ->and($route->middleware())->toContain('api');
});

it('combines ApiRoute middleware with custom Middleware attribute correctly', function () {
    config()->set('mediator.handler_paths', [__DIR__ . '/../Fixtures']);
    app(ActionDecoratorManager::class)->boot();

    $route = Route::getRoutes()->getByAction(Tests\Fixtures\ApiWithCustomMiddlewareAction::class);

    expect($route)->not->toBeNull()
        ->and($route->uri())->toBe('api/test-api-auth')
        ->and($route->middleware())->toContain('api', 'auth:sanctum');
});

it('applies name prefix when Name attribute is used', function () {
    config()->set('mediator.handler_paths', [__DIR__ . '/../Fixtures']);
    app(ActionDecoratorManager::class)->boot();

    $route = Route::getRoutes()->getByAction(Tests\Fixtures\NamedAction::class);

    expect($route)->not->toBeNull()
        ->and($route->getName())->toBe('api.named.index');
});

it('infers action when not explicitly provided in the route method and matches request correctly', function () {
    config()->set('mediator.handler_paths', [__DIR__ . '/../Fixtures']);
    app(ActionDecoratorManager::class)->boot();

    $request = Illuminate\Http\Request::create('/implicit-route-test', 'GET');
    $route = Route::getRoutes()->match($request);

    expect($route)->not->toBeNull()
        ->and($route->uri())->toBe('implicit-route-test')
        ->and($route->getActionName())->toBe(Tests\Fixtures\ImplicitRouteAction::class);
});
