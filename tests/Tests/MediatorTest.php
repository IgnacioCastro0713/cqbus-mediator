<?php

namespace Tests;

use Ignaciocastro0713\CqbusMediator\Attributes\RequestHandler;
use Ignaciocastro0713\CqbusMediator\Contracts\Mediator;
use Ignaciocastro0713\CqbusMediator\HandlerDiscovery;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use InvalidArgumentException;

class MyTestRequest
{
}

class InvalidRequest
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


class NoHandlerRequest
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

class MediatorTest extends TestCase
{
    private Mediator $mediator;
    private string $cachePath;

    public function setUp(): void
    {
        parent::setUp();
        $this->mediator = app(Mediator::class);
        $this->cachePath = $this->app->bootstrapPath('cache/mediator_handlers.php');
    }

    protected function tearDown(): void
    {
        if (File::exists($this->cachePath)) {
            File::delete($this->cachePath);
        }
        parent::tearDown();
    }

    /** @test */
    public function a_handler_can_be_dispatched_successfully()
    {
        $result = $this->mediator->send(new MyTestRequest());
        $this->assertEquals('Test successful', $result);
    }

    /** @test */
    public function an_exception_is_thrown_if_no_handler_is_found()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->mediator->send(new NoHandlerRequest());
    }

    /** @test */
    public function an_exception_is_thrown_if_handler_has_no_handle_method()
    {
        $this->app->bind("mediator.handler." . MyTestRequest::class, InvalidHandler::class);
        $this->expectException(InvalidArgumentException::class);
        $this->mediator->send(new MyTestRequest());
    }

    /** @test */
    public function it_can_run_with_a_pipeline()
    {
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

        $this->assertEquals('processed', $result);
    }

    /** @test */
    public function mediator_cache_command_creates_the_cache_file()
    {
        Artisan::call('mediator:cache');

        $this->assertFileExists($this->cachePath);

        $cachedHandlers = require $this->cachePath;
        $this->assertArrayHasKey(MyTestRequest::class, $cachedHandlers);
        $this->assertEquals(MyTestHandler::class, $cachedHandlers[MyTestRequest::class]);
    }

    /** @test */
    public function mediator_clear_command_deletes_the_cache_file()
    {
        Artisan::call('mediator:cache');
        $this->assertFileExists($this->cachePath);

        Artisan::call('mediator:clear');
        $this->assertFileDoesNotExist($this->cachePath);
    }

    /** @test */
    public function handler_discovery_works_as_expected()
    {
        $paths = HandlerDiscovery::getHandlerPaths();
        $discovered = HandlerDiscovery::discoverHandlers($paths);
        $this->assertContains(MyTestHandler::class, $discovered);

        $requestClass = HandlerDiscovery::getRequestClass(MyTestHandler::class);
        $this->assertEquals(MyTestRequest::class, $requestClass);

        $requestClassInvalid = HandlerDiscovery::getRequestClass(NoHandlerRequest::class);
        $this->assertNull($requestClassInvalid);
    }
}
