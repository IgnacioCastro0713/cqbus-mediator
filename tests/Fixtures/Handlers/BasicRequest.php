<?php

declare(strict_types=1);

namespace Tests\Fixtures\Handlers;

class BasicRequest
{
    public function __construct(
        public string $name = 'initial'
    ) {
    }
}
