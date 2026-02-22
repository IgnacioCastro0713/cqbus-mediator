<?php

use Ignaciocastro0713\CqbusMediator\Discovery\DiscoverAction;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->cachePath = $this->app->bootstrapPath('cache/mediator.php');
});

afterEach(function () {
    if (File::exists($this->cachePath)) {
        File::delete($this->cachePath);
    }
});

it('discovers action classes with AsAction trait and route method', function () {
    $paths = config('mediator.handler_paths', [app_path()]);
    $paths = is_array($paths) ? $paths : [$paths];

    $discovered = DiscoverAction::in(...$paths)->get();

    // Should find our test fixtures
    expect($discovered)->toBeArray();

    // Each discovered action should be a valid class string
    foreach ($discovered as $actionClass) {
        expect(class_exists($actionClass))->toBeTrue();
    }
});

it('discovers actions from Fixtures directory', function () {
    $discovered = DiscoverAction::in(__DIR__ . '/../Fixtures')->get();

    expect($discovered)
        ->toBeArray()
        ->toContain(Tests\Fixtures\AttributeAction::class)
        ->toContain(Tests\Fixtures\AuthAction::class)
        ->toContain(Tests\Fixtures\NoPrefixAction::class);
});

it('returns empty array when no actions found', function () {
    // Point to a directory with no actions
    $discovered = DiscoverAction::in(__DIR__ . '/../Fixtures/Pipelines')->get();

    expect($discovered)->toBeArray()->toBeEmpty();
});
