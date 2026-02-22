<?php

use Ignaciocastro0713\CqbusMediator\Contracts\Mediator;
use Tests\Fixtures\Handlers\GlobalAndHandlerRequest;
use Tests\Fixtures\Handlers\GlobalTestPipeline;
use Tests\Fixtures\Handlers\MultiplePipelineRequest;
use Tests\Fixtures\Handlers\NoPipelineRequest;
use Tests\Fixtures\Handlers\PipelineTestRequest;
use Tests\Fixtures\Handlers\SkipGlobalRequest;
use Tests\Fixtures\Handlers\SkipGlobalWithHandlerPipelineRequest;

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
    expect($result['order'])->toBe([])
        ->and($result['value'])->toBe('original');
});

it('executes handler-level pipelines even when skipping global pipelines', function () {
    // Set global pipeline
    $this->app['config']->set('mediator.pipelines', [GlobalTestPipeline::class]);

    // Re-instantiate mediator to pick up new config
    $this->app->forgetInstance(Mediator::class);
    $mediator = $this->app->make(Mediator::class);

    $result = $mediator->send(new SkipGlobalWithHandlerPipelineRequest());

    // Only handler-level pipeline should run, not global
    expect($result['order'])->toBe(['first'])
        ->and($result['value'])->toBe('original-first');
});
