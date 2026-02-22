<?php

declare(strict_types=1);

namespace Tests\Fixtures\Handlers;

class PipelineTestRequest
{
    public string $value = 'original';
    public array $pipelineOrder = [];
}
