<?php

declare(strict_types=1);

namespace Ignaciocastro0713\CqbusMediator\Facades;

use Ignaciocastro0713\CqbusMediator\Contracts\Mediator as MediatorContract;
use Ignaciocastro0713\CqbusMediator\Support\MediatorFake;
use Ignaciocastro0713\CqbusMediator\Support\PublishResults;
use Illuminate\Support\Facades\Facade;

/**
 * @method static mixed send(object $request)
 * @method static PublishResults publish(object $event)
 * @method static void assertSent(string|callable $request)
 * @method static void assertNotSent(string|callable $request)
 * @method static void assertPublished(string|callable $event)
 * @method static void assertNotPublished(string|callable $event)
 * @method static void assertNothingSent()
 * @method static void assertNothingPublished()
 * @method static \Illuminate\Support\Collection<int, object> sent(string|callable $request)
 * @method static \Illuminate\Support\Collection<int, object> published(string|callable $event)
 *
 * @see \Ignaciocastro0713\CqbusMediator\Contracts\Mediator
 * @see \Ignaciocastro0713\CqbusMediator\Support\MediatorFake
 */
class Mediator extends Facade
{
    /**
     * Replace the bound instance with a fake.
     */
    public static function fake(): MediatorFake
    {
        static::swap($fake = new MediatorFake());

        return $fake;
    }

    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return MediatorContract::class;
    }
}
