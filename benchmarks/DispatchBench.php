<?php

declare(strict_types=1);

namespace Benchmarks;

use Ignaciocastro0713\CqbusMediator\Contracts\Mediator;
use Ignaciocastro0713\CqbusMediator\MediatorServiceProvider;
use Illuminate\Container\Container;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Application as LaravelApp;
use Illuminate\Routing\Router;
use Orchestra\Testbench\Concerns\CreatesApplication;
use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;
use Tests\Fixtures\Events\UserRegisteredEvent;
use Tests\Fixtures\Handlers\BasicHandler;
use Tests\Fixtures\Handlers\BasicPipeline;
use Tests\Fixtures\Handlers\BasicRequest;
use Tests\Fixtures\Handlers\SinglePipelineHandler;
use Tests\Fixtures\Handlers\PipelineTestRequest;

/**
 * Benchmark for the hot-path dispatching performance.
 * Compares direct execution vs Mediator overhead.
 */
class DispatchBench
{
    use CreatesApplication;

    private Mediator $mediator;
    private BasicHandler $directHandler;
    private BasicRequest $request;
    private PipelineTestRequest $pipelineRequest;
    private UserRegisteredEvent $event;

    public function setUp(): void
    {
        // Boot minimal Laravel app for the benchmark
        $app = $this->createApplication();
        
        // Register Mediator Service Provider
        $app->register(MediatorServiceProvider::class);

        // Configure paths to fixtures
        $app['config']->set('mediator.handler_paths', [
            __DIR__ . '/../tests/Fixtures/Handlers',
            __DIR__ . '/../tests/Fixtures/EventHandlers',
            __DIR__ . '/../tests/Fixtures',
        ]);
        
        // Mock specific pipeline config for pipeline bench
        $app['config']->set('mediator.pipelines', []);

        // Warm up the mediator (cache handlers in memory)
        $this->mediator = $app->make(Mediator::class);
        
        // Prepare objects for direct comparison
        $this->directHandler = new BasicHandler();
        $this->request = new BasicRequest();
        $this->pipelineRequest = new PipelineTestRequest();
        $this->event = new UserRegisteredEvent('bench-user', 'bench@example.com');

        // Initial warmup call to resolve reflection/cache
        $this->mediator->send($this->request);
    }

    /**
     * Baseline: Direct execution
     */
    #[Revs(1000)]
    #[Iterations(5)]
    #[Warmup(2)]
    #[BeforeMethods('setUp')]
    public function benchDirectCall(): void
    {
        $this->directHandler->handle($this->request);
    }

    /**
     * Mediator: Send command (no pipelines)
     */
    #[Revs(1000)]
    #[Iterations(5)]
    #[Warmup(2)]
    #[BeforeMethods('setUp')]
    public function benchMediatorSendSimple(): void
    {
        $this->mediator->send($this->request);
    }

    /**
     * Mediator: Send command (with pipelines)
     */
    #[Revs(1000)]
    #[Iterations(5)]
    #[Warmup(2)]
    #[BeforeMethods('setUp')]
    public function benchMediatorSendWithPipeline(): void
    {
        $this->mediator->send($this->pipelineRequest);
    }

    /**
     * Mediator: Publish event (multiple handlers)
     */
    #[Revs(1000)]
    #[Iterations(5)]
    #[Warmup(2)]
    #[BeforeMethods('setUp')]
    public function benchMediatorPublishEvent(): void
    {
        $this->mediator->publish($this->event);
    }
}
