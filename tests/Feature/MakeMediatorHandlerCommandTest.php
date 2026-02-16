<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

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

it('creates handler and request files successfully', function () {
    Artisan::call('make:mediator-handler', ['name' => 'TestHandler']);

    $handlerPath = app_path('Http/Handlers/Test/TestHandler.php');
    $requestPath = app_path('Http/Handlers/Test/TestRequest.php');

    expect(File::exists($handlerPath))->toBeTrue();
    expect(File::exists($requestPath))->toBeTrue();

    expect(File::get($handlerPath))
        ->toContain('class TestHandler')
        ->toContain('namespace App\Http\Handlers\Test;')
        ->toContain('#[RequestHandler(TestRequest::class)]');

    expect(File::get($requestPath))
        ->toContain('class TestRequest')
        ->toContain('namespace App\Http\Handlers\Test;');
});

it('creates action file when option is provided', function () {
    Artisan::call('make:mediator-handler', ['name' => 'TestHandler', '--action' => true]);

    $actionPath = app_path('Http/Handlers/Test/TestAction.php');

    expect(File::exists($actionPath))->toBeTrue();
    expect(File::get($actionPath))
        ->toContain('class TestAction')
        ->toContain('use Ignaciocastro0713\CqbusMediator\Traits\AsAction;');
});

it('respects the root directory option', function () {
    Artisan::call('make:mediator-handler', [
        'name' => 'TestHandler',
        '--root' => 'MyCustom',
    ]);

    $handlerPath = app_path('Http/MyCustom/Test/TestHandler.php');

    expect(File::exists($handlerPath))->toBeTrue();
    expect(File::get($handlerPath))
        ->toContain('namespace App\Http\MyCustom\Test;');
});

it('throws exception if handler name does not end with Handler', function () {
    expect(fn () => Artisan::call('make:mediator-handler', ['name' => 'InvalidName']))
        ->toThrow(Ignaciocastro0713\CqbusMediator\Exceptions\InvalidHandlerException::class);
});

it('does not overwrite existing files if confirmation is declined', function () {
    // 1. Create "pre-existing" files
    $handlerPath = app_path('Http/Handlers/Test/TestHandler.php');
    $requestPath = app_path('Http/Handlers/Test/TestRequest.php');

    // Normalize paths to match Command's output format (OS dependent)
    $handlerPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $handlerPath);
    $requestPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $requestPath);

    File::ensureDirectoryExists(dirname($handlerPath));
    File::put($handlerPath, 'Old Handler Content');
    File::put($requestPath, 'Old Request Content');

    // 2. Run command, expect question listing both files, answer "no"
    // Note: The order of files in the message depends on the order in code.
    // In MakeMediatorHandlerCommand, it checks: Handler, Request, Action.
    $expectedMessage = "The following file(s) already exist:\n- $handlerPath\n- $requestPath\nDo you want to overwrite them?";

    $this->artisan('make:mediator-handler', ['name' => 'TestHandler'])
        ->expectsQuestion($expectedMessage, false)
        ->assertExitCode(0);

    // 3. Assert content hasn't changed
    expect(File::get($handlerPath))->toBe('Old Handler Content');
    expect(File::get($requestPath))->toBe('Old Request Content');
});
