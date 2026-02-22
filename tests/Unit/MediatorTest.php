<?php

use Ignaciocastro0713\CqbusMediator\Contracts\Mediator;
use Ignaciocastro0713\CqbusMediator\Discovery\DiscoverHandler;
use Ignaciocastro0713\CqbusMediator\Exceptions\HandlerNotFoundException;
use Ignaciocastro0713\CqbusMediator\Exceptions\InvalidHandlerException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Tests\Fixtures\Handlers\BasicHandler;
use Tests\Fixtures\Handlers\BasicPipeline;
use Tests\Fixtures\Handlers\BasicRequest;
use Tests\Fixtures\Handlers\HandlerWithoutHandleMethod;
use Tests\Fixtures\Handlers\RequestForInvalidHandler;
use Tests\Fixtures\Handlers\RequestWithoutHandler;

beforeEach(function () {
    $this->mediator = app(Mediator::class);
    $this->cachePath = $this->app->bootstrapPath('cache/mediator.php');
});

afterEach(function () {
    if (File::exists($this->cachePath)) {
        File::delete($this->cachePath);
    }
});

/**
 * Unit Mediator Tests
 */
it('a handler can be dispatched successfully', function () {
    $result = $this->mediator->send(new BasicRequest());
    expect($result)->toBe('initial');
});

it('throws an exception if no handler is found', function () {
    expect(fn () => $this->mediator->send(new RequestWithoutHandler()))
        ->toThrow(HandlerNotFoundException::class);
});

it('throws an exception if handler has no handle method', function () {
    expect(fn () => $this->mediator->send(new RequestForInvalidHandler()))
        ->toThrow(InvalidHandlerException::class);
});

it('can run with a pipeline', function () {
    $this->app['config']->set('mediator.pipelines', [BasicPipeline::class]);

    // Re-instantiate mediator to pick up new config
    $this->app->forgetInstance(Mediator::class);
    $this->mediator = $this->app->make(Mediator::class);

    $result = $this->mediator->send(new BasicRequest());
    expect($result)->toBe('processed');
});

it('mediator cache command creates the cache file', function () {
    Artisan::call('mediator:cache');
    expect(File::exists($this->cachePath))->toBeTrue();

    $cached = require $this->cachePath;
    expect($cached)
        ->toHaveKeys(['handlers', 'actions'])
        ->and($cached['handlers'])->toHaveKey(BasicRequest::class)
        ->and($cached['handlers'][BasicRequest::class])->toBe(BasicHandler::class);
});

it('mediator clear command deletes the cache file', function () {
    Artisan::call('mediator:cache');
    expect(File::exists($this->cachePath))->toBeTrue();

    Artisan::call('mediator:clear');
    expect(File::exists($this->cachePath))->toBeFalse();
});

it('loads handlers from cache file when available', function () {
    Artisan::call('mediator:cache');
    expect(File::exists($this->cachePath))->toBeTrue();

    // Re-instantiate mediator to load from cache
    $this->app->forgetInstance(Mediator::class);
    $mediator = $this->app->make(Mediator::class);

    // Should still work - handlers loaded from cache
    $result = $mediator->send(new BasicRequest());
    expect($result)->toBe('initial');
});

it('handler discovery works as expected', function () {
    $paths = config('mediator.handler_paths', [app_path()]);
    $paths = is_array($paths) ? $paths : [$paths];
    $discovered = DiscoverHandler::in(...$paths)->get();

    expect($discovered)
        ->toContain(BasicHandler::class)
        ->toContain(HandlerWithoutHandleMethod::class)
        ->and($discovered)->toHaveKey(BasicRequest::class)
        ->and($discovered)->toHaveKey(RequestForInvalidHandler::class)
        ->and($discovered)->not()->toHaveKey(RequestWithoutHandler::class);
});

it('throws an exception if handler name is invalid', function () {
    expect(fn () => Artisan::call('make:mediator-handler', ['name' => 'InvalidName']))
        ->toThrow(InvalidHandlerException::class);
});
