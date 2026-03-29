<?php

use Ignaciocastro0713\CqbusMediator\Routing\ActionDecoratorManager;
use Illuminate\Support\Facades\Route;
use Tests\Fixtures\PatternAction;
use Tests\Fixtures\PriorityHighAction;
use Tests\Fixtures\PriorityLowAction;

beforeEach(function () {
    config()->set('mediator.handler_paths', [__DIR__ . '/../Fixtures']);
});

it('respects global route patterns', function () {
    // Set global pattern
    Route::pattern('id', '[0-9]+');

    // Boot manager to register routes (this creates duplicate routes, we want the last one)
    app(ActionDecoratorManager::class)->boot();

    $routes = Route::getRoutes()->get();
    $route = null;
    foreach ($routes as $r) {
        if ($r->getAction('controller') === PatternAction::class) {
            $route = $r;
        }
    }

    expect($route)->not->toBeNull()
        ->and($route->wheres)->toHaveKey('id', '[0-9]+');
});

it('registers routes in priority order', function () {
    // Boot manager to register routes
    app(ActionDecoratorManager::class)->boot();

    $routes = Route::getRoutes()->get();
    $highPriorityIndex = -1;
    $lowPriorityIndex = -1;

    foreach ($routes as $index => $route) {
        if ($route->getAction('controller') === PriorityHighAction::class) {
            $highPriorityIndex = $index;
        }
        if ($route->getAction('controller') === PriorityLowAction::class) {
            $lowPriorityIndex = $index;
        }
    }

    expect($highPriorityIndex)->not->toBe(-1)
        ->and($lowPriorityIndex)->not->toBe(-1)
        ->and($highPriorityIndex)->toBeLessThan($lowPriorityIndex);
});

it('registers routes in ascending priority order when configured', function () {
    config()->set('mediator.route_priority_direction', 'asc');
    Ignaciocastro0713\CqbusMediator\Discovery\MediatorDiscovery::clearCache();

    // Clear routes so multiple tests don't overlap in the getRoutes() collection
    Route::setRoutes(new \Illuminate\Routing\RouteCollection());

    app(ActionDecoratorManager::class)->boot();

    $routes = Route::getRoutes()->get();
    $controllersList = [];
    foreach ($routes as $route) {
        $controllersList[] = $route->getAction('controller');
    }

    $highIndex = array_search(PriorityHighAction::class, $controllersList);
    $lowIndex = array_search(PriorityLowAction::class, $controllersList);

    expect(config('mediator.route_priority_direction'))->toBe('asc')
        ->and($highIndex)->not->toBeFalse()
        ->and($lowIndex)->not->toBeFalse()
        ->and($lowIndex)->toBeLessThan($highIndex); // low priority first
});

it('resolves and groups priority from string context', function () {
    app(ActionDecoratorManager::class)->boot();

    $routes = Route::getRoutes()->get();

    $stringIndex = -1;
    $arrayIndex = -1;
    $highIndex = -1;

    foreach ($routes as $index => $route) {
        $controller = $route->getAction('controller');
        if ($controller === \Tests\Fixtures\PriorityStringAction::class) {
            $stringIndex = $index;
        }
        if ($controller === \Tests\Fixtures\PriorityArrayAction::class) {
            $arrayIndex = $index;
        }
        if ($controller === \Tests\Fixtures\PriorityHighAction::class) {
            $highIndex = $index;
        }
    }

    // Globals first (HighAction=10)
    // Then grouped alphabetically (both array and string use 'context', but string has 999, array 500)
    expect($highIndex)->toBeLessThan($stringIndex)
        ->and($stringIndex)->toBeLessThan($arrayIndex);
});
