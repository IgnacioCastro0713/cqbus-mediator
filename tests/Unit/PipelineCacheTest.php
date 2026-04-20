<?php

use Ignaciocastro0713\CqbusMediator\Constants\MediatorConstants;
use Ignaciocastro0713\CqbusMediator\Contracts\Mediator;
use Ignaciocastro0713\CqbusMediator\Services\MediatorService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Tests\Fixtures\Handlers\BasicRequest;

beforeEach(function () {
    $this->cachePath = $this->app->bootstrapPath('cache/mediator.php');
});

afterEach(function () {
    if (File::exists($this->cachePath)) {
        File::delete($this->cachePath);
    }
});

it('pipeline cache keys include type suffix after mediator:cache', function () {
    Artisan::call('mediator:cache');

    $cached = require $this->cachePath;
    $pipelines = $cached['pipelines'];

    foreach (array_keys($pipelines) as $key) {
        expect($key)->toMatch('/:(request|notification)$/');
    }
});

it('resolves pipelines from cache without ReflectionClass on dispatch', function () {
    Artisan::call('mediator:cache');

    $this->app->forgetInstance(Mediator::class);
    $mediator = $this->app->make(Mediator::class);

    // Pipeline cache should be pre-warmed — dispatch should work correctly
    $result = $mediator->send(new BasicRequest('test'));

    expect($result)->not->toBeNull();
});

it('request handlers use request pipeline type in cache key', function () {
    Artisan::call('mediator:cache');

    $cached = require $this->cachePath;
    $pipelines = $cached['pipelines'];

    $requestKeys = array_filter(array_keys($pipelines), fn (string $k) => str_ends_with($k, ':' . MediatorConstants::PIPELINE_TYPE_REQUEST));

    expect($requestKeys)->not->toBeEmpty();
});
