<?php

use Ignaciocastro0713\CqbusMediator\Exceptions\HandlerNotFoundException;
use Ignaciocastro0713\CqbusMediator\Exceptions\InvalidActionException;
use Ignaciocastro0713\CqbusMediator\Exceptions\InvalidHandlerException;
use Ignaciocastro0713\CqbusMediator\MediatorConfig;
use Ignaciocastro0713\CqbusMediator\Traits\AsAction;


/**
 * MediatorConfig Tests
 */
it('returns handler paths as array when config is string', function () {
    config()->set('mediator.handler_paths', '/some/path');

    $paths = MediatorConfig::handlerPaths();

    expect($paths)->toBeArray()->toContain('/some/path');
});

it('returns handler paths as array when config is array', function () {
    config()->set('mediator.handler_paths', ['/path1', '/path2']);

    $paths = MediatorConfig::handlerPaths();

    expect($paths)->toBeArray()->toBe(['/path1', '/path2']);
});

it('returns default app_path when handler_paths is null', function () {
    config()->set('mediator.handler_paths', null);

    $paths = MediatorConfig::handlerPaths();

    expect($paths)->toBeArray()->toContain(app_path());
});

it('returns empty array when pipelines config is null', function () {
    config()->set('mediator.pipelines', null);

    $pipelines = MediatorConfig::pipelines();

    expect($pipelines)->toBeArray()->toBeEmpty();
});

it('returns pipelines array when configured', function () {
    config()->set('mediator.pipelines', ['SomePipeline', 'AnotherPipeline']);

    $pipelines = MediatorConfig::pipelines();

    expect($pipelines)->toBe(['SomePipeline', 'AnotherPipeline']);
});

/**
 * Exception Tests
 */
it('HandlerNotFoundException contains request class and suggestion', function () {
    $exception = new HandlerNotFoundException('App\\Requests\\TestRequest');

    expect($exception->getMessage())
        ->toContain('No handler registered for request')
        ->toContain('App\\Requests\\TestRequest')
        ->toContain('Suggested solution')
        ->toContain('make:mediator-handler');

    expect($exception->requestClass)->toBe('App\\Requests\\TestRequest');
});

it('InvalidActionException contains action class and method name', function () {
    $action = new class () {
    };

    $exception = new InvalidActionException($action, 'handle');

    expect($exception->getMessage())
        ->toContain('is missing the required')
        ->toContain('handle')
        ->toContain('Suggested solution');
});

it('InvalidHandlerException handles object input', function () {
    $handler = new class () {
    };

    $exception = new InvalidHandlerException($handler);

    expect($exception->getMessage())
        ->toContain('is invalid')
        ->toContain('Missing')
        ->toContain('handle');
});

it('InvalidHandlerException handles string message input', function () {
    $exception = new InvalidHandlerException('Custom error message');

    expect($exception->getMessage())->toBe('Custom error message');
});

/**
 * AsAction Trait Tests
 */
it('AsAction __invoke throws BadMethodCallException', function () {
    $action = new class () {
        use AsAction;
    };

    expect(fn () => $action->__invoke())
        ->toThrow(BadMethodCallException::class, 'Direct invocation is not supported');
});
