<?php

declare(strict_types=1);

namespace Ignaciocastro0713\CqbusMediator\Discovery;

class DiscoverHandlerConfig
{
    /**
     * @param array<string> $directories
     */
    public function __construct(
        public array $directories,
    ) {
    }

}
