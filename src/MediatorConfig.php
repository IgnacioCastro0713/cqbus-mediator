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

    /**
     * @return array<class-string>
     */
    public static function requestPipelines(): array
    {
        /** @var array<class-string> */
        return Arr::wrap(config('mediator.request_pipelines'));
    }

    /**
     * @return array<class-string>
     */
    public static function notificationPipelines(): array
    {
        /** @var array<class-string> */
        return Arr::wrap(config('mediator.notification_pipelines'));
    }

    /**
     * @return 'asc'|'desc'
     */
    public static function routePriorityDirection(): string
    {
        return config('mediator.route_priority_direction', 'desc') === 'asc' ? 'asc' : 'desc';
    }
}
