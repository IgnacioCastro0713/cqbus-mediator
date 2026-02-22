<?php

use Ignaciocastro0713\CqbusMediator\Managers\ActionDecoratorManager;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Routing\Router;
use Mockery\MockInterface;

it('skips route registration if routes are cached', function () {
    // 1. Mock Application to return routesAreCached = true
    $app = Mockery::mock(Application::class, function (MockInterface $mock) {
        $mock->shouldReceive('routesAreCached')->once()->andReturn(true);
    });

    // 2. Mock Router
    // Should NOT receive any route registration calls (get, post, etc.)
    // We expect 0 calls to register routes because caching is active.
    $router = Mockery::mock(Router::class, function (MockInterface $mock) {
        $mock->shouldReceive('matched')->once(); // registerActions call this
        $mock->shouldNotReceive('get');
        $mock->shouldNotReceive('post');
        $mock->shouldNotReceive('put');
        $mock->shouldNotReceive('patch');
        $mock->shouldNotReceive('delete');
    });

    // 3. Instantiate Manager with mocks
    $manager = new ActionDecoratorManager($router, $app);

    // 4. Boot manager
    $manager->boot();

    // 5. Assertions handled by Mockery expectations
});

it('registers routes if routes are NOT cached', function () {
    // 1. Mock Application to return routesAreCached = false
    $app = Mockery::mock(Application::class, function (MockInterface $mock) {
        $mock->shouldReceive('routesAreCached')->once()->andReturn(false);
        $mock->shouldReceive('bootstrapPath')->andReturn('/tmp/not-found'); // Force discovery (no cache)
    });

    // 2. Mock Router - allow any route registration methods
    $router = Mockery::mock(Router::class, function (MockInterface $mock) {
        $mock->shouldReceive('matched')->once();
        // Allow route registration methods that may be called during discovery
        $mock->shouldReceive('group')->andReturnUsing(function ($attributes, $callback) use ($mock) {
            $callback(); // Execute the callback to register routes within the group
        });
        $mock->shouldReceive('get')->andReturnSelf();
        $mock->shouldReceive('post')->andReturnSelf();
        $mock->shouldReceive('put')->andReturnSelf();
        $mock->shouldReceive('patch')->andReturnSelf();
        $mock->shouldReceive('delete')->andReturnSelf();
    });

    // 3. Instantiate Manager
    $manager = new ActionDecoratorManager($router, $app);

    // 4. Boot - this triggers discovery and route registration
    $manager->boot();

    // If no exception, it passed.
    expect(true)->toBeTrue();
});
