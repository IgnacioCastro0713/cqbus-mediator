<?php

declare(strict_types=1);

namespace Tests\Fixtures\Handlers;

class MultiplePipelineRequest
{
    public string $value = 'original';
    public array $pipelineOrder = [];
}
