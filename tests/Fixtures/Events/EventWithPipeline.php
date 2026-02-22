<?php

declare(strict_types=1);

namespace Tests\Fixtures\Events;

class EventWithPipeline
{
    public function __construct(
        public readonly string $data = 'test'
    ) {
    }
}
