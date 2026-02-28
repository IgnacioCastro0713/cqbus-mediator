<?php

declare(strict_types=1);

namespace Ignaciocastro0713\CqbusMediator;

use Illuminate\Support\Arr;

final class MediatorConfig
{
    /**
     * @return array<string>
     */
    public static function handlerPaths(): array
    {
        /** @var array<string> */
        return Arr::wrap(config('mediator.handler_paths') ?? app_path());
    }

    /**
     * @return array<class-string>
     */
    public static function pipelines(): array
    {
        /** @var array<class-string> */
        return Arr::wrap(config('mediator.global_pipelines'));
    }
}
