<?php

use Ignaciocastro0713\CqbusMediator\Discovery\ActionDiscovery;
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

    $discovered = ActionDiscovery::in(...$paths)->get();

    // Should find our test fixtures
    expect($discovered)->toBeArray();

    // Each discovered action should be a valid class string
    foreach ($discovered as $actionClass) {
        expect(class_exists($actionClass))->toBeTrue();
    }
});

it('discovers actions from Fixtures directory', function () {
    $discovered = ActionDiscovery::in(__DIR__ . '/../Fixtures')->get();

    expect($discovered)
        ->toBeArray()
        ->toContain(Tests\Fixtures\AttributeAction::class)
        ->toContain(Tests\Fixtures\AuthAction::class)
        ->toContain(Tests\Fixtures\NoPrefixAction::class);
});

it('returns empty array when no actions found', function () {
    // Point to a directory with no actions
    $discovered = ActionDiscovery::in(__DIR__ . '/../Fixtures/Pipelines')->get();

    expect($discovered)->toBeArray()->toBeEmpty();
});

it('filters out classes without static route method', function () {
    // Handlers don't have a static route method, so they should be filtered out
    $discovered = ActionDiscovery::in(__DIR__ . '/../Fixtures/Handlers')->get();

    // Should not contain any handlers (they don't have route() method)
    expect($discovered)->not->toContain(Tests\Fixtures\Handlers\BasicHandler::class);
});

it('isValidActionClass returns false for non-existent class', function () {
    $ActionDiscovery = ActionDiscovery::in(__DIR__ . '/../Fixtures');

    // Use reflection to test the private method
    $reflection = new ReflectionClass($ActionDiscovery);
    $method = $reflection->getMethod('isValidActionClass');
    $method->setAccessible(true);

    // Test with non-existent class
    $result = $method->invoke($ActionDiscovery, 'NonExistentClass\\That\\DoesNotExist');

    expect($result)->toBeFalse();
});

it('isValidActionClass returns false for class without AsAction trait', function () {
    $ActionDiscovery = ActionDiscovery::in(__DIR__ . '/../Fixtures');

    $reflection = new ReflectionClass($ActionDiscovery);
    $method = $reflection->getMethod('isValidActionClass');
    $method->setAccessible(true);

    // BasicHandler exists but doesn't have AsAction trait
    $result = $method->invoke($ActionDiscovery, Tests\Fixtures\Handlers\BasicHandler::class);

    expect($result)->toBeFalse();
});
