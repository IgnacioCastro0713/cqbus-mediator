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
