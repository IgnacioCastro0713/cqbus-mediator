<?php

use Ignaciocastro0713\CqbusMediator\Contracts\Mediator;
use Ignaciocastro0713\CqbusMediator\Exceptions\InvalidPipelineException;

it('throws InvalidPipelineException for non-existent global pipeline', function () {
    config()->set('mediator.global_pipelines', ['App\\NonExistent\\Pipeline']);
    $this->app->forgetInstance(Mediator::class);

    expect(fn () => $this->app->make(Mediator::class))
        ->toThrow(InvalidPipelineException::class, "global_pipelines");
});

it('throws InvalidPipelineException for non-existent request pipeline', function () {
    config()->set('mediator.request_pipelines', ['App\\NonExistent\\RequestPipeline']);
    $this->app->forgetInstance(Mediator::class);

    expect(fn () => $this->app->make(Mediator::class))
        ->toThrow(InvalidPipelineException::class, "request_pipelines");
});

it('throws InvalidPipelineException for non-existent notification pipeline', function () {
    config()->set('mediator.notification_pipelines', ['App\\NonExistent\\NotificationPipeline']);
    $this->app->forgetInstance(Mediator::class);

    expect(fn () => $this->app->make(Mediator::class))
        ->toThrow(InvalidPipelineException::class, "notification_pipelines");
});

it('does not validate pipelines when loading from cache', function () {
    // Build a cache file with an invalid pipeline to simulate upgrading with stale config
    $cachePath = $this->app->bootstrapPath('cache/mediator.php');
    file_put_contents($cachePath, "<?php\nreturn ['handlers'=>[],'notifications'=>[],'actions'=>[],'pipelines'=>[]];\n");

    config()->set('mediator.global_pipelines', ['App\\NonExistent\\Pipeline']);
    $this->app->forgetInstance(Mediator::class);

    // Should NOT throw — validation is skipped when cache file exists
    expect(fn () => $this->app->make(Mediator::class))->not->toThrow(InvalidPipelineException::class);

    // Cleanup
    @unlink($cachePath);
});
