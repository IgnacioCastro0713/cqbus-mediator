<?php

use Ignaciocastro0713\CqbusMediator\Support\PublishResults;

it('wraps results and exposes typed API', function () {
    $results = new PublishResults(['HandlerA' => 'val1', 'HandlerB' => null]);

    expect($results->all())->toBe(['HandlerA' => 'val1', 'HandlerB' => null])
        ->and($results->get('HandlerA'))->toBe('val1')
        ->and($results->get('NonExistent'))->toBeNull()
        ->and($results->isEmpty())->toBeFalse()
        ->and($results->count())->toBe(2)
        ->and($results->handlerClasses())->toBe(['HandlerA', 'HandlerB']);
});

it('is iterable via foreach', function () {
    $results = new PublishResults(['A' => 1, 'B' => 2]);

    $collected = [];
    foreach ($results as $key => $val) {
        $collected[$key] = $val;
    }

    expect($collected)->toBe(['A' => 1, 'B' => 2]);
});

it('supports array-style access', function () {
    $results = new PublishResults(['A' => 'foo']);

    expect($results['A'])->toBe('foo')
        ->and(isset($results['A']))->toBeTrue()
        ->and(isset($results['B']))->toBeFalse();
});

it('supports array-style mutation', function () {
    $results = new PublishResults(['A' => 'foo']);
    $results['B'] = 'bar';
    unset($results['A']);

    expect(isset($results['A']))->toBeFalse()
        ->and($results['B'])->toBe('bar');
});

it('is empty when constructed with no results', function () {
    $results = new PublishResults();

    expect($results->isEmpty())->toBeTrue()
        ->and($results->count())->toBe(0)
        ->and($results->handlerClasses())->toBe([]);
});
