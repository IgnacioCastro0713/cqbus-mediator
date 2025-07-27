<?php

namespace Unit;

use Ignaciocastro0713\CqbusMediator\Services\MediatorService;
use Illuminate\Container\Container;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Foundation\Application;
use Illuminate\Pipeline\Pipeline;
use InvalidArgumentException;
use Mockery;
use PHPUnit\Framework\TestCase;

// Define a simple abstract class that implements a handle method.
abstract class AbstractTestHandler
{
    public function handle(object $request): mixed
    {
        // This method will be mocked, so its implementation doesn't matter
        return null;
    }
}

class MediatorServiceTest extends TestCase
{
    private ?Application $appInstance = null;
    private MediatorService $mediatorService;
    private $configMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->appInstance = new Application();

        Container::setInstance($this->appInstance);

        $this->configMock = Mockery::mock(ConfigRepository::class);

        $this->configMock->shouldReceive('get')
            ->with('mediator.handler_paths', [])
            ->andReturn([])
            ->byDefault();

        $this->configMock->shouldReceive('get')
            ->with('mediator.pipelines', [])
            ->andReturn([])
            ->byDefault();

        $this->appInstance->instance('config', $this->configMock);

        $this->mediatorService = new MediatorService($this->appInstance);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        if ($this->appInstance !== null) {
            Container::setInstance();
            $this->appInstance = null;
        }
        parent::tearDown();
    }

    /** @test */
    public function it_sends_request_to_handler_without_pipelines()
    {
        $request = new class () {};
        $handlerResult = 'Handler processed the request!';

        $handlerMock = Mockery::mock(AbstractTestHandler::class);
        $handlerMock->shouldReceive('handle')
            ->once()
            ->with($request)
            ->andReturn($handlerResult);

        $this->appInstance->instance("mediator.handler." . get_class($request), $handlerMock);

        $result = $this->mediatorService->send($request);

        $this->assertEquals($handlerResult, $result);
    }

    /** @test */
    public function it_sends_request_through_pipelines_and_then_to_handler()
    {
        $request = new class () {};
        $processedRequestByPipeline = (object) ['data' => 'processed'];
        $handlerResult = 'Handler processed the piped request!';

        $this->configMock->shouldReceive('get')
            ->with('mediator.pipelines', [])
            ->andReturn(['App\\Pipelines\\SomePipeline', 'App\\Pipelines\\AnotherPipeline'])
            ->once();

        $pipelineMock = Mockery::mock(Pipeline::class);
        $pipelineMock->shouldReceive('send')
            ->once()
            ->with($request)
            ->andReturnSelf();

        $pipelineMock->shouldReceive('through')
            ->once()
            ->with(['App\\Pipelines\\SomePipeline', 'App\\Pipelines\\AnotherPipeline'])
            ->andReturnSelf();

        $pipelineMock->shouldReceive('then')
            ->once()
            ->andReturnUsing(fn ($callback) => $callback($processedRequestByPipeline));

        $this->appInstance->instance(Pipeline::class, $pipelineMock);

        $handlerMock = Mockery::mock(AbstractTestHandler::class);
        $handlerMock->shouldReceive('handle')
            ->once()
            ->with($processedRequestByPipeline)
            ->andReturn($handlerResult);

        $this->appInstance->instance("mediator.handler." . get_class($request), $handlerMock);

        $result = $this->mediatorService->send($request);

        $this->assertEquals($handlerResult, $result);
    }

    /** @test */
    public function it_throws_exception_if_no_handler_registered()
    {
        $request = new class () {};

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("No handler registered for request: " . get_class($request));

        $this->mediatorService->send($request);
    }

    /** @test */
    public function it_throws_exception_if_handler_missing_handle_method()
    {
        $request = new class () {};
        $invalidHandler = new class () {};

        $this->appInstance->instance("mediator.handler." . get_class($request), $invalidHandler);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Handler '" . get_class($invalidHandler) . "' must have a 'handle' method.");

        $this->mediatorService->send($request);
    }
}
