<?php

declare(strict_types=1);

namespace Tests\Fixtures\Handlers;

class SkipGlobalRequest
{
    public string $value = 'original';
    public array $pipelineOrder = [];
}
