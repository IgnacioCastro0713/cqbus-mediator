<?php

declare(strict_types=1);

namespace Tests\Architecture;

use Attribute;
use Ignaciocastro0713\CqbusMediator\Contracts\RouteModifier;

test('exceptions must have Exception suffix')
    ->expect('Ignaciocastro0713\CqbusMediator\Exceptions')
    ->toHaveSuffix('Exception');

test('exceptions must extend Exception class')
    ->expect('Ignaciocastro0713\CqbusMediator\Exceptions')
    ->toExtend(\Exception::class);

test('attributes must be readonly')
    ->expect('Ignaciocastro0713\CqbusMediator\Attributes')
    ->toBeClasses()
    ->toBeReadonly();

test('route attributes must implement RouteModifier')
    ->expect([
        \Ignaciocastro0713\CqbusMediator\Attributes\Routing\Api::class,
        \Ignaciocastro0713\CqbusMediator\Attributes\Routing\Web::class,
        \Ignaciocastro0713\CqbusMediator\Attributes\Routing\Middleware::class,
        \Ignaciocastro0713\CqbusMediator\Attributes\Routing\Prefix::class,
        \Ignaciocastro0713\CqbusMediator\Attributes\Routing\Name::class,
    ])
    ->toImplement(RouteModifier::class);

test('attributes must have the Attribute attribute')
    ->expect('Ignaciocastro0713\CqbusMediator\Attributes')
    ->toHaveAttribute(Attribute::class);

test('contracts must be interfaces')
    ->expect('Ignaciocastro0713\CqbusMediator\Contracts')
    ->toBeInterfaces();

test('traits must be traits')
    ->expect('Ignaciocastro0713\CqbusMediator\Traits')
    ->toBeTraits();

test('strict types must be declared')
    ->expect('Ignaciocastro0713\CqbusMediator')
    ->toUseStrictTypes();

test('core does not depend on symfony or laravel http foundation directly except where needed')
    ->expect('Ignaciocastro0713\CqbusMediator')
    ->not->toUse([
        'Symfony\Component\HttpFoundation\Response',
        'Illuminate\Http\Response',
    ]);
