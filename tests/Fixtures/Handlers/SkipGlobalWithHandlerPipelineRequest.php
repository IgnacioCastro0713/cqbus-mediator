<?php

declare(strict_types=1);

namespace Tests\Fixtures\Handlers;

class SkipGlobalWithHandlerPipelineRequest
{
    public string $value = 'original';
    public array $pipelineOrder = [];
}
