<?php

use Ignaciocastro0713\CqbusMediator\Support\ActionDecoratorManager;
use Illuminate\Support\Facades\Route;

it('ignores standard closure routes without throwing errors', function () {
    app(ActionDecoratorManager::class)->boot();

    // Register a standard route that is NOT a mediator action
    Route::get('/standard-closure', fn () => 'standard');

    // Simulate a matched route (like Laravel does internally during dispatch)
    $request = Illuminate\Http\Request::create('/standard-closure', 'GET');
    $route = Route::getRoutes()->match($request);

    // Test that the route executes properly without the manager interfering
    // Since it's a closure and doesn't have a 'controller' attribute
    expect($route->run())->toBe('standard');
});

it('ignores array based routes without throwing errors', function () {
    app(ActionDecoratorManager::class)->boot();

    // Register an array-based action that has no controller property
    Route::get('/array-route', [Ignaciocastro0713\CqbusMediator\Tests\Fixtures\Handlers\BasicHandler::class, 'handle']);

    $request = Illuminate\Http\Request::create('/array-route', 'GET');
    $route = Route::getRoutes()->match($request);

    expect($route->getActionName())->toBe(Ignaciocastro0713\CqbusMediator\Tests\Fixtures\Handlers\BasicHandler::class . '@handle');
});

it('ignores routes with array-based controllers without throwing errors', function () {
    app(ActionDecoratorManager::class)->boot();

    Route::get('/array-controller-route', function () {
        return 'array_controller';
    });

    $request = Illuminate\Http\Request::create('/array-controller-route', 'GET');
    $route = Route::getRoutes()->match($request);

    // Manually mutate the action array of the route to simulate the edge case
    // where $uses is a closure but 'controller' is an array (invalid but good for branch coverage)
    $action = $route->getAction();
    $action['controller'] = ['WeirdController', 'index'];
    $route->setAction($action);

    // Getting controller class shouldn't throw an error and just fall back to null
    $manager = app(ActionDecoratorManager::class);
    $reflection = new ReflectionClass($manager);
    $method = $reflection->getMethod('getControllerClass');
    $method->setAccessible(true);

    $result = $method->invoke($manager, $route);

    expect($result)->toBeNull();
});
