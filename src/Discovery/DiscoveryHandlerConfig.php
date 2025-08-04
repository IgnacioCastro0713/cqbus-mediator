<?php

namespace Ignaciocastro0713\CqbusMediator\Discovery;

class DiscoveryHandlerConfig
{
    /**
     * @param array<string> $directories
     */
    public function __construct(
        public array $directories,
    ) {
    }

}
