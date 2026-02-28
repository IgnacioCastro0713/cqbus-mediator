<?php

declare(strict_types=1);

use Ignaciocastro0713\CqbusMediator\Routing\RouteOptions;

it('initializes with optional empty array', function () {
    $options = new RouteOptions();
    expect($options->toArray())->toBeEmpty();
});

it('initializes with provided array', function () {
    $options = new RouteOptions(['existing' => 'value']);
    expect($options->toArray())->toBe(['existing' => 'value']);
});

it('adds middleware cleanly', function () {
    $options = new RouteOptions();

    // Add string
    $options->addMiddleware('api');
    expect($options->get('middleware'))->toBe(['api']);

    // Add array and deduplicate
    $options->addMiddleware(['web', 'api', 'guest']);
    expect($options->get('middleware'))->toBe(['api', 'web', 'guest']);
});

it('removes middleware cleanly', function () {
    $options = new RouteOptions();

    $options->withoutMiddleware('csrf');
    expect($options->get('withoutMiddleware'))->toBe(['csrf']);

    $options->withoutMiddleware(['auth', 'csrf']);
    expect($options->get('withoutMiddleware'))->toBe(['csrf', 'auth']);
});

it('adds excluded middleware cleanly', function () {
    $options = new RouteOptions();

    $options->excludedMiddleware('csrf');
    expect($options->get('excluded_middleware'))->toBe(['csrf']);

    $options->excludedMiddleware(['auth', 'csrf']);
    expect($options->get('excluded_middleware'))->toBe(['csrf', 'auth']);
});

it('adds prefixes and handles slashes properly', function () {
    $options = new RouteOptions();

    $options->addPrefix('api');
    expect($options->get('prefix'))->toBe('api');

    $options->addPrefix('/v1/');
    expect($options->get('prefix'))->toBe('api/v1');

    $options->addPrefix('users');
    expect($options->get('prefix'))->toBe('api/v1/users');
});

it('appends names with dots properly', function () {
    $options = new RouteOptions();

    $options->name('api');
    expect($options->get('as'))->toBe('api');

    $options->name('.v1');
    expect($options->get('as'))->toBe('api.v1');

    $options->as('users.'); // alias test
    expect($options->get('as'))->toBe('api.v1.users.');
});

it('adds where constraints via array or string', function () {
    $options = new RouteOptions();

    $options->where('id', '[0-9]+');
    expect($options->get('where'))->toBe(['id' => '[0-9]+']);

    $options->where(['slug' => '[a-z\-]+']);
    expect($options->get('where'))->toBe([
        'id' => '[0-9]+',
        'slug' => '[a-z\-]+',
    ]);
});

it('adds default values cleanly', function () {
    $options = new RouteOptions();

    $options->defaults('locale', 'en');
    expect($options->get('defaults'))->toBe(['locale' => 'en']);

    $options->defaults('theme', 'dark');
    expect($options->get('defaults'))->toBe([
        'locale' => 'en',
        'theme' => 'dark',
    ]);
});

it('can check if key exists', function () {
    $options = new RouteOptions(['foo' => 'bar']);

    expect($options->has('foo'))->toBeTrue();
    expect($options->has('baz'))->toBeFalse();
});

it('can retrieve defaults if key does not exist', function () {
    $options = new RouteOptions();

    expect($options->get('missing'))->toBeNull();
    expect($options->get('missing', 'fallback'))->toBe('fallback');
});

it('handles magic method assignments', function () {
    $options = new RouteOptions();

    // Boolean calls (no params)
    $options->withTrashed();
    expect($options->get('withTrashed'))->toBeTrue();

    // Single param calls
    $options->domain('api.app.com');
    expect($options->get('domain'))->toBe('api.app.com');

    // Multi param calls (stored as array)
    $options->someCustomLaravelMethod('arg1', 'arg2');
    expect($options->get('someCustomLaravelMethod'))->toBe(['arg1', 'arg2']);
});
