<?php

use Ignaciocastro0713\CqbusMediator\Attributes\RequestHandler;
use Ignaciocastro0713\CqbusMediator\Contracts\Mediator;
use Ignaciocastro0713\CqbusMediator\Discovery\DiscoverHandler;
use Ignaciocastro0713\CqbusMediator\Exceptions\HandlerNotFoundException;
use Ignaciocastro0713\CqbusMediator\Exceptions\InvalidHandlerException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

/**
 * Fixtures
 */
class MyTestRequest
{
    public string $name = "initial";
}

class InvalidRequest
{
}

class NoHandlerRequest
{
}

#[RequestHandler(MyTestRequest::class)]
class MyTestHandler
{
    public function handle(MyTestRequest $request): string
    {
        return $request->name;
    }
}

#[RequestHandler(InvalidRequest::class)]
class InvalidHandler
{
}

class MyTestPipeline
{
    public function handle($request, $next)
    {
        $request->name = 'processed';

        return $next($request);
    }
}

beforeEach(function () {
    $this->mediator = app(Mediator::class);
    $this->cachePath = $this->app->bootstrapPath('cache/mediator_handlers.php');
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
    $result = $this->mediator->send(new MyTestRequest());
    expect($result)->toBe('initial');
});

it('throws an exception if no handler is found', function () {
    expect(fn () => $this->mediator->send(new NoHandlerRequest()))->toThrow(HandlerNotFoundException::class);
});

it('throws an exception if handler has no handle method', function () {
    expect(fn () => $this->mediator->send(new InvalidRequest()))->toThrow(InvalidHandlerException::class);
});

it('can run with a pipeline', function () {
    $this->app['config']->set('mediator.pipelines', [MyTestPipeline::class]);
    $result = $this->mediator->send(new MyTestRequest());
    expect($result)->toBe('processed');
});

it('mediator cache command creates the cache file', function () {
    Artisan::call('mediator:cache');
    expect(File::exists($this->cachePath))->toBeTrue();

    $cachedHandlers = require $this->cachePath;
    expect($cachedHandlers)
        ->toHaveKey(MyTestRequest::class)
        ->and($cachedHandlers[MyTestRequest::class])->toBe(MyTestHandler::class);
});

it('mediator clear command deletes the cache file', function () {
    Artisan::call('mediator:cache');
    expect(File::exists($this->cachePath))->toBeTrue();

    Artisan::call('mediator:clear');
    expect(File::exists($this->cachePath))->toBeFalse();
});

it('handler discovery works as expected', function () {
    $paths = config('mediator.handler_paths', app_path());
    $discovered = DiscoverHandler::in($paths)->get();
    expect($discovered)
        ->toContain(MyTestHandler::class)
        ->toContain(InvalidHandler::class)
        ->and($discovered)->toHaveKey(MyTestRequest::class)
        ->and($discovered)->toHaveKey(InvalidRequest::class)
        ->and($discovered)->not()->toHaveKey(NoHandlerRequest::class);

});

it('throws an exception if handler name is invalid', function () {
    expect(fn () => Artisan::call('make:mediator-handler', ['name' => 'InvalidName']))
        ->toThrow(InvalidHandlerException::class);
});
