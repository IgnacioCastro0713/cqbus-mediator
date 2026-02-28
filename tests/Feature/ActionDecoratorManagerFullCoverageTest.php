<?php

use Ignaciocastro0713\CqbusMediator\Routing\ActionDecoratorManager;
use Illuminate\Support\Facades\Route;
use Tests\Fixtures\RedundantMiddlewareAction;

it('removes duplicate middleware in resolveRouteAttributes', function () {
    config()->set('mediator.handler_paths', [__DIR__ . '/../Fixtures']);
    app(ActionDecoratorManager::class)->boot();

    $route = Route::getRoutes()->getByAction(RedundantMiddlewareAction::class);

    expect($route)->not->toBeNull();
    $middlewares = $route->middleware();

    // Check that 'api' appears only once despite being in #[Api] and #[Middleware(['api', 'guest'])]
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

it('extracts controller class correctly using fallback and stripping method references', function () {
    $manager = app(ActionDecoratorManager::class);
    $reflection = new ReflectionClass($manager);
    $method = $reflection->getMethod('getControllerClass');
    $method->setAccessible(true);

    // Create a mock route with a standard string controller structure 'Class@method'
    $route = new \Illuminate\Routing\Route('GET', '/test', ['uses' => 'App\Http\Controllers\TestController@index']);

    $result = $method->invoke($manager, $route);

    expect($result)->toBe('App\Http\Controllers\TestController');
});

it('extracts controller class when uses is a closure but controller is a string', function () {
    $manager = app(ActionDecoratorManager::class);
    $reflection = new ReflectionClass($manager);
    $method = $reflection->getMethod('getControllerClass');
    $method->setAccessible(true);

    $route = new \Illuminate\Routing\Route('GET', '/test', function () {
        return 'test';
    });

    // Forzar el caso en el que 'uses' es un closure (no string), pero 'controller' es string
    $action = $route->getAction();
    $action['controller'] = 'App\Http\Controllers\FallbackController@index';
    $route->setAction($action);

    $result = $method->invoke($manager, $route);

    expect($result)->toBe('App\Http\Controllers\FallbackController');
});
