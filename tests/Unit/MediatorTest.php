<?php

use Ignaciocastro0713\CqbusMediator\Attributes\RequestHandler;
use Ignaciocastro0713\CqbusMediator\Contracts\Mediator;
use Ignaciocastro0713\CqbusMediator\HandlerDiscovery;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class MyTestRequest
{
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
        return 'Test successful';
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
    File::deleteDirectory($this->app->basePath('app/Http'));
});

// Tests
it('a handler can be dispatched successfully', function () {
    $result = $this->mediator->send(new MyTestRequest());
    expect($result)->toBe('Test successful');
});

it('throws an exception if no handler is found', function () {
    expect(fn () => $this->mediator->send(new NoHandlerRequest()))->toThrow(InvalidArgumentException::class);
});

it('throws an exception if handler has no handle method', function () {
    $this->app->bind("mediator.handler." . MyTestRequest::class, InvalidHandler::class);
    expect(fn () => $this->mediator->send(new MyTestRequest()))->toThrow(InvalidArgumentException::class);
});

it('can run with a pipeline', function () {
    $this->app['config']->set('mediator.pipelines', [MyTestPipeline::class]);

    $request = new class () {
        public string $name = 'initial';
    };
    $handler = new class () {
        public function handle($request): string
        {
            return $request->name;
        }
    };

    $requestClass = get_class($request);
    $this->app->bind("mediator.handler." . $requestClass, fn () => $handler);

    $result = $this->mediator->send($request);

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
    $paths = HandlerDiscovery::getHandlerPaths();
    $discovered = HandlerDiscovery::discoverHandlers($paths);
    expect($discovered)->toContain(MyTestHandler::class);

    $requestClass = HandlerDiscovery::getRequestClass(MyTestHandler::class);
    expect($requestClass)->toBe(MyTestRequest::class);

    $requestClassInvalid = HandlerDiscovery::getRequestClass(NoHandlerRequest::class);
    expect($requestClassInvalid)->toBeNull();
});

it('throws an exception if handler name is invalid', function () {
    expect(fn () => Artisan::call('make:mediator-handler', ['name' => 'InvalidName']))
        ->toThrow(InvalidArgumentException::class);
});
