<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->cachePath = $this->app->bootstrapPath('cache/mediator.php');
});

afterEach(function () {
    if (File::exists($this->cachePath)) {
        File::delete($this->cachePath);
    }
});

it('lists handlers and actions from discovery', function () {
    Artisan::call('mediator:list');

    $output = Artisan::output();

    expect($output)
        ->toContain('Discovering from source')
        ->toContain('Handlers')
        ->toContain('Notifications')
        ->toContain('Actions');
});

it('lists handlers and actions from cache', function () {
    // First, create the cache
    Artisan::call('mediator:cache');

    // Then list from cache
    Artisan::call('mediator:list');

    $output = Artisan::output();

    expect($output)
        ->toContain('Loading from cache')
        ->toContain('Handlers')
        ->toContain('Notifications')
        ->toContain('Actions');
});

it('filters to show only handlers with --handlers option', function () {
    Artisan::call('mediator:list', ['--handlers' => true]);

    $output = Artisan::output();

    expect($output)
        ->toContain('Handlers')
        ->not->toContain('Notifications')
        ->not->toContain('Actions');
});

it('filters to show only notifications with --events option', function () {
    Artisan::call('mediator:list', ['--events' => true]);

    $output = Artisan::output();

    expect($output)
        ->toContain('Notifications')
        ->not->toContain('Actions')
        ->not->toContain('Request'); // the handlers table header
});

it('filters to show only actions with --actions option', function () {
    Artisan::call('mediator:list', ['--actions' => true]);

    $output = Artisan::output();

    expect($output)
        ->toContain('Actions')
        ->not->toContain('Notifications')
        ->not->toContain('Handlers');
});
it('displays summary with handler and action counts', function () {
    Artisan::call('mediator:list');

    $output = Artisan::output();

    expect($output)->toMatch('/Handlers:\s*\d+/');
    expect($output)->toMatch('/Notifications:\s*\d+/');
    expect($output)->toMatch('/Actions:\s*\d+/');
});

it('table output does not contain unrendered color tags', function () {
    Artisan::call('mediator:list');

    $output = Artisan::output();

    // Should not contain raw color tags like <fg=gray> that weren't rendered
    expect($output)
        ->not->toContain('<fg=')
        ->not->toContain('</>')
        ->not->toContain('</>');
});

it('shows message when no handlers are registered', function () {
    // Create empty directory
    $emptyDir = sys_get_temp_dir() . '/empty-dir-' . uniqid();
    mkdir($emptyDir, 0777, true);

    config()->set('mediator.handler_paths', [$emptyDir]);

    Artisan::call('mediator:list', ['--handlers' => true]);

    $output = Artisan::output();

    expect($output)->toContain('No handlers registered');

    // Cleanup
    rmdir($emptyDir);
});

it('shows message when no notifications are registered', function () {
    // Create empty directory
    $emptyDir = sys_get_temp_dir() . '/empty-dir-' . uniqid();
    mkdir($emptyDir, 0777, true);

    config()->set('mediator.handler_paths', [$emptyDir]);

    Artisan::call('mediator:list', ['--events' => true]);

    $output = Artisan::output();

    expect($output)->toContain('No notifications registered');

    // Cleanup
    rmdir($emptyDir);
});

it('shows message when no actions are registered', function () {
    // Create empty directory
    $emptyDir = sys_get_temp_dir() . '/empty-dir-' . uniqid();
    mkdir($emptyDir, 0777, true);

    config()->set('mediator.handler_paths', [$emptyDir]);

    Artisan::call('mediator:list', ['--actions' => true]);

    $output = Artisan::output();

    expect($output)->toContain('No actions registered');

    // Cleanup
    rmdir($emptyDir);
});

it('shows Pipelines column in handlers table', function () {
    Artisan::call('mediator:list', ['--handlers' => true]);

    expect(Artisan::output())->toContain('Pipelines');
});

it('shows Pipelines column in event handlers table', function () {
    Artisan::call('mediator:list', ['--events' => true]);

    expect(Artisan::output())->toContain('Pipelines');
});

it('warns when cache is active in non-production environment', function () {
    Artisan::call('mediator:cache');
    Artisan::call('mediator:list');

    // The test environment is 'testing', not 'production'
    expect(Artisan::output())->toContain('non-production environment');
});

it('shows (none) when no pipelines are configured for a handler', function () {
    Artisan::call('mediator:list', ['--handlers' => true]);

    expect(Artisan::output())->toContain('(none)');
});
