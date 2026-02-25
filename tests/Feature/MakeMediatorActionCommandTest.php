<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    // Cleanup generated files
    $basePath = app_path('Http/Handlers/Test');
    if (File::exists($basePath)) {
        File::deleteDirectory($basePath);
    }

    $customPath = app_path('Http/MyCustom/Test');
    if (File::exists($customPath)) {
        File::deleteDirectory(app_path('Http/MyCustom'));
    }
});

afterEach(function () {
    // Cleanup generated files
    $basePath = app_path('Http/Handlers/Test');
    if (File::exists($basePath)) {
        File::deleteDirectory($basePath);
    }

    $customPath = app_path('Http/MyCustom/Test');
    if (File::exists($customPath)) {
        File::deleteDirectory(app_path('Http/MyCustom'));
    }
});

it('creates action and request files successfully', function () {
    Artisan::call('make:mediator-action', ['name' => 'TestAction']);

    $actionPath = app_path('Http/Handlers/Test/TestAction.php');
    $requestPath = app_path('Http/Handlers/Test/TestRequest.php');

    expect(File::exists($actionPath))->toBeTrue()
        ->and(File::exists($requestPath))->toBeTrue()
        ->and(File::get($actionPath))
        ->toContain('class TestAction')
        ->toContain('namespace App\Http\Handlers\Test;')
        ->toContain('use Ignaciocastro0713\CqbusMediator\Traits\AsAction;')
        ->toContain('public function handle(TestRequest $request)')
        ->and(File::get($requestPath))
        ->toContain('class TestRequest')
        ->toContain('namespace App\Http\Handlers\Test;');
});

it('respects the root directory option', function () {
    Artisan::call('make:mediator-action', [
        'name' => 'TestAction',
        '--root' => 'MyCustom',
    ]);

    $actionPath = app_path('Http/MyCustom/Test/TestAction.php');

    expect(File::exists($actionPath))->toBeTrue()
        ->and(File::get($actionPath))
        ->toContain('namespace App\Http\MyCustom\Test;');
});

it('throws exception if action name does not end with Action', function () {
    expect(fn () => Artisan::call('make:mediator-action', ['name' => 'InvalidName']))
        ->toThrow(Ignaciocastro0713\CqbusMediator\Exceptions\InvalidHandlerException::class);
});

it('does not overwrite existing files if confirmation is declined', function () {
    // 1. Create "pre-existing" files
    $actionPath = app_path('Http/Handlers/Test/TestAction.php');
    $requestPath = app_path('Http/Handlers/Test/TestRequest.php');

    // Normalize paths to match Command's output format (OS dependent)
    $actionPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $actionPath);
    $requestPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $requestPath);

    File::ensureDirectoryExists(dirname($actionPath));
    File::put($actionPath, 'Old Action Content');
    File::put($requestPath, 'Old Request Content');

    // 2. Run command, expect question listing both files, answer "no"
    $existing = [realpath($actionPath), realpath($requestPath)];
    $expectedMessage = "The following file(s) already exist:\n- " . implode("\n- ", $existing) . "\nDo you want to overwrite them?";

    $this->artisan('make:mediator-action', ['name' => 'TestAction'])
        ->expectsQuestion($expectedMessage, false)
        ->assertExitCode(0);

    // 3. Assert content hasn't changed
    expect(File::get($actionPath))->toBe('Old Action Content')
        ->and(File::get($requestPath))->toBe('Old Request Content');
});
