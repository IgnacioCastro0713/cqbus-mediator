<?php

declare(strict_types=1);

namespace Tests\Fixtures\Handlers;

use Ignaciocastro0713\CqbusMediator\Attributes\Handlers\RequestHandler;
use Ignaciocastro0713\CqbusMediator\Attributes\Pipelines\SkipGlobalPipelines;

#[RequestHandler(SkipGlobalRequest::class)]
#[SkipGlobalPipelines]
class SkipGlobalHandler
{
    public function handle(SkipGlobalRequest $request): array
    {
        return [
            'value' => $request->value,
            'order' => $request->pipelineOrder,
        ];
    }
}
