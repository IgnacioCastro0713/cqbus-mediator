<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $basePath = app_path('Http/Events/Test');
    if (File::exists($basePath)) {
        File::deleteDirectory($basePath);
    }

    $customPath = app_path('Http/MyCustomEvents/Test');
    if (File::exists($customPath)) {
        File::deleteDirectory(app_path('Http/MyCustomEvents'));
    }
});

afterEach(function () {
    // Cleanup generated files
    $basePath = app_path('Http/Events/Test');
    if (File::exists($basePath)) {
        File::deleteDirectory($basePath);
    }

    $customPath = app_path('Http/MyCustomEvents/Test');
    if (File::exists($customPath)) {
        File::deleteDirectory(app_path('Http/MyCustomEvents'));
    }
});

it('creates event handler and event files successfully', function () {
    Artisan::call('make:mediator-event-handler', ['name' => 'TestHandler']);

    $handlerPath = app_path('Http/Events/Test/TestHandler.php');
    $eventPath = app_path('Http/Events/Test/TestEvent.php');

    expect(File::exists($handlerPath))->toBeTrue()
        ->and(File::exists($eventPath))->toBeTrue()
        ->and(File::get($handlerPath))
        ->toContain('class TestHandler')
        ->toContain('namespace App\Http\Events\Test;')
        ->toContain('#[EventHandler(TestEvent::class)]')
        ->and(File::get($eventPath))
        ->toContain('class TestEvent')
        ->toContain('namespace App\Http\Events\Test;');
});

it('respects the root directory option', function () {
    Artisan::call('make:mediator-event-handler', [
        'name' => 'TestHandler',
        '--root' => 'MyCustomEvents',
    ]);

    $handlerPath = app_path('Http/MyCustomEvents/Test/TestHandler.php');

    expect(File::exists($handlerPath))->toBeTrue()
        ->and(File::get($handlerPath))
        ->toContain('namespace App\Http\MyCustomEvents\Test;');
});

it('throws exception if handler name does not end with Handler', function () {
    expect(fn () => Artisan::call('make:mediator-event-handler', ['name' => 'InvalidName']))
        ->toThrow(Ignaciocastro0713\CqbusMediator\Exceptions\InvalidHandlerException::class);
});

it('does not overwrite existing files if confirmation is declined', function () {
    // 1. Create "pre-existing" files
    $handlerPath = app_path('Http/Events/Test/TestHandler.php');
    $eventPath = app_path('Http/Events/Test/TestEvent.php');

    // Normalize paths
    $handlerPath = str_replace(['/', ''], DIRECTORY_SEPARATOR, $handlerPath);
    $eventPath = str_replace(['/', ''], DIRECTORY_SEPARATOR, $eventPath);

    File::ensureDirectoryExists(dirname($handlerPath));
    File::put($handlerPath, 'Old Handler Content');
    File::put($eventPath, 'Old Event Content');

    // 2. Run command, expect question listing both files, answer "no"
    // Note: The order of files in the message depends on the order in code.
    // In MakeEventHandlerCommand, it checks: Handler, Event.
    $expectedMessage = "The following file(s) already exist:
- $handlerPath
- $eventPath
Do you want to overwrite them?";

    $this->artisan('make:mediator-event-handler', ['name' => 'TestHandler'])
        ->expectsQuestion($expectedMessage, false)
        ->assertExitCode(0);

    // 3. Assert content hasn't changed
    expect(File::get($handlerPath))->toBe('Old Handler Content')
        ->and(File::get($eventPath))->toBe('Old Event Content');
});
