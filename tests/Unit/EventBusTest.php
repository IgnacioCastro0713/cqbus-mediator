<?php

use Ignaciocastro0713\CqbusMediator\Contracts\Mediator;
use Ignaciocastro0713\CqbusMediator\Discovery\MediatorDiscovery;
use Ignaciocastro0713\CqbusMediator\Support\PublishResults;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Tests\Fixtures\EventHandlers\CreateDefaultSettingsHandler;
use Tests\Fixtures\EventHandlers\LogUserRegistrationHandler;
use Tests\Fixtures\EventHandlers\SendWelcomeEmailHandler;
use Tests\Fixtures\Events\EventWithoutHandlers;
use Tests\Fixtures\Events\UserRegisteredEvent;

beforeEach(function () {
    $this->mediator = app(Mediator::class);
    $this->cachePath = $this->app->bootstrapPath('cache/mediator.php');
});

afterEach(function () {
    if (File::exists($this->cachePath)) {
        File::delete($this->cachePath);
    }
});

it('publishes an event to multiple handlers', function () {
    $event = new UserRegisteredEvent('user-123', 'test@example.com');

    $results = $this->mediator->publish($event);

    expect($results)->toBeInstanceOf(PublishResults::class)
        ->and($results)->toHaveCount(3);
});

it('executes notifications in priority order (higher first)', function () {
    $event = new UserRegisteredEvent('user-123', 'test@example.com');

    $results = $this->mediator->publish($event);

    // Convert to array to check order
    $handlerOrder = $results->handlerClasses();

    // Priority order: SendWelcomeEmailHandler (10), CreateDefaultSettingsHandler (5), LogUserRegistrationHandler (1)
    expect($handlerOrder[0])->toBe(SendWelcomeEmailHandler::class)
        ->and($handlerOrder[1])->toBe(CreateDefaultSettingsHandler::class)
        ->and($handlerOrder[2])->toBe(LogUserRegistrationHandler::class);
});

it('returns results keyed by handler class name', function () {
    $event = new UserRegisteredEvent('user-123', 'test@example.com');

    $results = $this->mediator->publish($event);

    expect($results->get(SendWelcomeEmailHandler::class))->toBe('welcome_email_sent_to_test@example.com')
        ->and($results->get(CreateDefaultSettingsHandler::class))->toBe('settings_created_for_user-123')
        ->and($results->get(LogUserRegistrationHandler::class))->toBe('logged_registration_user-123')
        ->and($results->handlerClasses())->toContain(SendWelcomeEmailHandler::class)
        ->and($results->handlerClasses())->toContain(CreateDefaultSettingsHandler::class)
        ->and($results->handlerClasses())->toContain(LogUserRegistrationHandler::class);
});

it('returns empty results when no handlers registered for event', function () {
    $event = new EventWithoutHandlers();

    $results = $this->mediator->publish($event);

    expect($results)->toBeInstanceOf(PublishResults::class)
        ->and($results->isEmpty())->toBeTrue();
});

it('discovers notifications correctly', function () {
    $paths = config('mediator.handler_paths', [app_path()]);
    $paths = is_array($paths) ? $paths : [$paths];
    $discovered = MediatorDiscovery::discover($paths)['notifications'];

    expect($discovered)->toHaveKey(UserRegisteredEvent::class)
        ->and($discovered[UserRegisteredEvent::class])->toHaveCount(3);
});

it('caches notifications with mediator:cache command', function () {
    Artisan::call('mediator:cache');
    expect(File::exists($this->cachePath))->toBeTrue();

    $cached = require $this->cachePath;
    expect($cached)
        ->toHaveKey('notifications')
        ->and($cached['notifications'])->toHaveKey(UserRegisteredEvent::class);
});

it('loads notifications from cache when available', function () {
    Artisan::call('mediator:cache');
    expect(File::exists($this->cachePath))->toBeTrue();

    // Re-instantiate mediator to load from cache
    $this->app->forgetInstance(Mediator::class);
    $mediator = $this->app->make(Mediator::class);

    $event = new UserRegisteredEvent('user-456', 'cached@example.com');
    $results = $mediator->publish($event);

    expect($results)->toHaveCount(3)
        ->and($results[SendWelcomeEmailHandler::class])->toBe('welcome_email_sent_to_cached@example.com');
});

it('throws exception when event handler has no handle method', function () {
    $event = new \Tests\Fixtures\Events\EventForInvalidHandler();

    expect(fn () => $this->mediator->publish($event))
        ->toThrow(\Ignaciocastro0713\CqbusMediator\Exceptions\InvalidHandlerException::class);
});

it('executes event handler with pipeline', function () {
    // Set global pipeline to modify the behavior
    $this->app['config']->set('mediator.global_pipelines', [\Tests\Fixtures\Handlers\BasicPipeline::class]);

    // Re-instantiate mediator to pick up new config
    $this->app->forgetInstance(Mediator::class);
    $mediator = $this->app->make(Mediator::class);

    $event = new UserRegisteredEvent('user-789', 'pipeline@example.com');
    $results = $mediator->publish($event);

    // The BasicPipeline modifies the result by setting a 'processed' property
    expect($results)->toBeInstanceOf(PublishResults::class)
        ->and($results->isEmpty())->toBeFalse();
});

it('event handler discovery returns handlers sorted by priority', function () {
    $paths = config('mediator.handler_paths', [app_path()]);
    $paths = is_array($paths) ? $paths : [$paths];
    $discovered = MediatorDiscovery::discover($paths)['notifications'];

    $handlers = $discovered[UserRegisteredEvent::class] ?? [];

    // Check that first handler has higher priority than second
    if (count($handlers) >= 2) {
        expect($handlers[0]['priority'])->toBeGreaterThanOrEqual($handlers[1]['priority']);
    }
});
