<?php

declare(strict_types=1);

namespace Ignaciocastro0713\CqbusMediator;

final class MediatorConfig
{
    /**
     * @return array<string>
     */
    public static function handlerPaths(): array
    {
        $paths = config('mediator.handler_paths', app_path());
        if (! is_array($paths)) {
            $paths = [$paths ?? app_path()];
        }

        return $paths;
    }

    /**
     * @return array<class-string>
     */
    public static function pipelines(): array
    {
        $pipelines = config('mediator.pipelines');

        return is_array($pipelines) ? $pipelines : [];
    }
}
