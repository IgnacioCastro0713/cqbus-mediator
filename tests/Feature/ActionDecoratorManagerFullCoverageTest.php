<?php

use Ignaciocastro0713\CqbusMediator\Support\ActionDecoratorManager;
use Illuminate\Routing\Route as LaravelRoute;
use Illuminate\Support\Facades\Route;
use Tests\Fixtures\RedundantMiddlewareAction;

it('removes duplicate middleware in resolveRouteAttributes', function () {
    config()->set('mediator.handler_paths', [__DIR__ . '/../Fixtures']);
    app(ActionDecoratorManager::class)->boot();

    $route = Route::getRoutes()->getByAction(RedundantMiddlewareAction::class);

    expect($route)->not->toBeNull();
    $middlewares = $route->middleware();

    // Check that 'api' appears only once despite being in #[ApiRoute] and #[Middleware(['api', 'guest'])]
    $apiCount = count(array_filter($middlewares, fn ($m) => $m === 'api'));
    expect($apiCount)->toBe(1);
    expect($middlewares)->toContain('api', 'guest');
});

it('isValidActionController returns false for null class', function () {
    $manager = app(ActionDecoratorManager::class);
    $reflection = new ReflectionClass($manager);
    $method = $reflection->getMethod('isValidActionController');
    $method->setAccessible(true);

    $result = $method->invoke($manager, null);
    expect($result)->toBeFalse();
});

it('isValidActionController returns false for existing class without trait', function () {
    $manager = app(ActionDecoratorManager::class);
    $reflection = new ReflectionClass($manager);
    $method = $reflection->getMethod('isValidActionController');
    $method->setAccessible(true);

    // Tests\Fixtures\Handlers\BasicHandler exists but doesn't have AsAction trait
    $result = $method->invoke($manager, Tests\Fixtures\Handlers\BasicHandler::class);
    expect($result)->toBeFalse();
});

it('getControllerClass falls back to controller key if uses is not a string', function () {
    $manager = app(ActionDecoratorManager::class);
    $reflection = new ReflectionClass($manager);
    $method = $reflection->getMethod('getControllerClass');
    $method->setAccessible(true);

    // Create a mock route
    $route = new LaravelRoute('GET', '/test', ['uses' => fn () => 'test']);
    $action = $route->getAction();
    $action['controller'] = 'SomeController@index';
    $route->setAction($action);

    $result = $method->invoke($manager, $route);
    expect($result)->toBe('SomeController');
});

it('getControllerClass returns null if both uses and controller are invalid', function () {
    $manager = app(ActionDecoratorManager::class);
    $reflection = new ReflectionClass($manager);
    $method = $reflection->getMethod('getControllerClass');
    $method->setAccessible(true);

    // Create a mock route with no string controller/uses
    $route = new LaravelRoute('GET', '/test', ['uses' => fn () => 'test']);
    $action = $route->getAction();
    unset($action['controller']);
    $route->setAction($action);

    $result = $method->invoke($manager, $route);
    expect($result)->toBeNull();
});
