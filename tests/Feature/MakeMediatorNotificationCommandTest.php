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
    Artisan::call('make:mediator-notification', ['name' => 'TestNotification']);

    $notificationPath = app_path('Http/Events/Test/TestNotification.php');
    $eventPath = app_path('Http/Events/Test/TestEvent.php');

    expect(File::exists($notificationPath))->toBeTrue()
        ->and(File::exists($eventPath))->toBeTrue()
        ->and(File::get($notificationPath))
        ->toContain('class TestNotification')
        ->toContain('namespace App\Http\Events\Test;')
        ->toContain('#[Notification(TestEvent::class)]')
        ->and(File::get($eventPath))
        ->toContain('class TestEvent')
        ->toContain('namespace App\Http\Events\Test;');
});

it('respects the root directory option', function () {
    Artisan::call('make:mediator-notification', [
        'name' => 'TestNotification',
        '--root' => 'MyCustomEvents',
    ]);

    $notificationPath = app_path('Http/MyCustomEvents/Test/TestNotification.php');

    expect(File::exists($notificationPath))->toBeTrue()
        ->and(File::get($notificationPath))
        ->toContain('namespace App\Http\MyCustomEvents\Test;');
});

it('throws exception if handler name does not end with Notification', function () {
    expect(fn () => Artisan::call('make:mediator-notification', ['name' => 'InvalidName']))
        ->toThrow(Ignaciocastro0713\CqbusMediator\Exceptions\InvalidHandlerException::class);
});

it('does not overwrite existing files if confirmation is declined', function () {
    // 1. Create "pre-existing" files
    $notificationPath = app_path('Http/Events/Test/TestNotification.php');
    $eventPath = app_path('Http/Events/Test/TestEvent.php');

    // Normalize paths
    $notificationPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $notificationPath);
    $eventPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $eventPath);

    File::ensureDirectoryExists(dirname($notificationPath));
    File::put($notificationPath, 'Old Notification Content');
    File::put($eventPath, 'Old Event Content');

    // 2. Run command, expect question listing both files, answer "no"
    // Note: The order of files in the message depends on the order in code.
    // In MakeNotificationCommand, it checks: Notification, Event.
    $existing = [realpath($notificationPath), realpath($eventPath)];
    $expectedMessage = "The following file(s) already exist:\n- " . implode("\n- ", $existing) . "\nDo you want to overwrite them?";

    $this->artisan('make:mediator-notification', ['name' => 'TestNotification'])
        ->expectsQuestion($expectedMessage, false)
        ->assertExitCode(0);

    // 3. Assert content hasn't changed
    expect(File::get($notificationPath))->toBe('Old Notification Content')
        ->and(File::get($eventPath))->toBe('Old Event Content');
});
