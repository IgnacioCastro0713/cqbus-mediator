<?php

use Ignaciocastro0713\CqbusMediator\Attributes\Pipeline;
use Ignaciocastro0713\CqbusMediator\Attributes\RequestHandler;
use Ignaciocastro0713\CqbusMediator\Attributes\SkipGlobalPipelines;
use Ignaciocastro0713\CqbusMediator\Contracts\Mediator;

/**
 * Fixtures for Handler-level Pipeline tests
 */
class PipelineTestRequest
{
    public string $value = 'original';
    public array $pipelineOrder = [];
}

class FirstPipeline
{
    public function handle(object $request, Closure $next)
    {
        $request->pipelineOrder[] = 'first';
        $request->value .= '-first';

        return $next($request);
    }
}

class SecondPipeline
{
    public function handle(object $request, Closure $next)
    {
        $request->pipelineOrder[] = 'second';
        $request->value .= '-second';

        return $next($request);
    }
}

class GlobalTestPipeline
{
    public function handle(object $request, Closure $next)
    {
        $request->pipelineOrder[] = 'global';
        $request->value .= '-global';

        return $next($request);
    }
}

#[RequestHandler(PipelineTestRequest::class)]
#[Pipeline(FirstPipeline::class)]
class SinglePipelineHandler
{
    public function handle(PipelineTestRequest $request): array
    {
        return [
            'value' => $request->value,
            'order' => $request->pipelineOrder,
        ];
    }
}

class MultiplePipelineRequest
{
    public string $value = 'original';
    public array $pipelineOrder = [];
}

#[RequestHandler(MultiplePipelineRequest::class)]
#[Pipeline([FirstPipeline::class, SecondPipeline::class])]
class MultiplePipelineHandler
{
    public function handle(MultiplePipelineRequest $request): array
    {
        return [
            'value' => $request->value,
            'order' => $request->pipelineOrder,
        ];
    }
}

class NoPipelineRequest
{
    public string $value = 'original';
}

#[RequestHandler(NoPipelineRequest::class)]
class NoPipelineHandler
{
    public function handle(NoPipelineRequest $request): string
    {
        return $request->value;
    }
}

class GlobalAndHandlerRequest
{
    public string $value = 'original';
    public array $pipelineOrder = [];
}

#[RequestHandler(GlobalAndHandlerRequest::class)]
#[Pipeline(SecondPipeline::class)]
class GlobalAndHandlerPipelineHandler
{
    public function handle(GlobalAndHandlerRequest $request): array
    {
        return [
            'value' => $request->value,
            'order' => $request->pipelineOrder,
        ];
    }
}

class SkipGlobalRequest
{
    public string $value = 'original';
    public array $pipelineOrder = [];
}

#[RequestHandler(SkipGlobalRequest::class)]
#[SkipGlobalPipelines]
class SkipGlobalHandler
{
    public function handle(SkipGlobalRequest $request): array
    {
        return [
            'value' => $request->value,
            'order' => $request->pipelineOrder,
        ];
    }
}

class SkipGlobalWithHandlerPipelineRequest
{
    public string $value = 'original';
    public array $pipelineOrder = [];
}

#[RequestHandler(SkipGlobalWithHandlerPipelineRequest::class)]
#[SkipGlobalPipelines]
#[Pipeline(FirstPipeline::class)]
class SkipGlobalWithHandlerPipelineHandler
{
    public function handle(SkipGlobalWithHandlerPipelineRequest $request): array
    {
        return [
            'value' => $request->value,
            'order' => $request->pipelineOrder,
        ];
    }
}

/**
 * Handler-level Pipeline Tests
 */
it('executes a single handler-level pipeline', function () {
    $mediator = app(Mediator::class);

    $result = $mediator->send(new PipelineTestRequest());

    expect($result['value'])->toBe('original-first');
    expect($result['order'])->toBe(['first']);
});

it('executes multiple handler-level pipelines in order', function () {
    $mediator = app(Mediator::class);

    $result = $mediator->send(new MultiplePipelineRequest());

    expect($result['value'])->toBe('original-first-second');
    expect($result['order'])->toBe(['first', 'second']);
});

it('works without handler-level pipelines', function () {
    $mediator = app(Mediator::class);

    $result = $mediator->send(new NoPipelineRequest());

    expect($result)->toBe('original');
});

it('executes global pipelines before handler-level pipelines', function () {
    // Set global pipeline
    $this->app['config']->set('mediator.pipelines', [GlobalTestPipeline::class]);

    // Re-instantiate mediator to pick up new config
    $this->app->forgetInstance(Mediator::class);
    $mediator = $this->app->make(Mediator::class);

    $result = $mediator->send(new GlobalAndHandlerRequest());

    // Global should run first, then handler-level
    expect($result['order'])->toBe(['global', 'second']);
    expect($result['value'])->toBe('original-global-second');
});

it('skips global pipelines when handler has SkipGlobalPipelines attribute', function () {
    // Set global pipeline
    $this->app['config']->set('mediator.pipelines', [GlobalTestPipeline::class]);

    // Re-instantiate mediator to pick up new config
    $this->app->forgetInstance(Mediator::class);
    $mediator = $this->app->make(Mediator::class);

    $result = $mediator->send(new SkipGlobalRequest());

    // Global pipeline should NOT have run
    expect($result['order'])->toBe([]);
    expect($result['value'])->toBe('original');
});

it('executes handler-level pipelines even when skipping global pipelines', function () {
    // Set global pipeline
    $this->app['config']->set('mediator.pipelines', [GlobalTestPipeline::class]);

    // Re-instantiate mediator to pick up new config
    $this->app->forgetInstance(Mediator::class);
    $mediator = $this->app->make(Mediator::class);

    $result = $mediator->send(new SkipGlobalWithHandlerPipelineRequest());

    // Only handler-level pipeline should run, not global
    expect($result['order'])->toBe(['first']);
    expect($result['value'])->toBe('original-first');
});
