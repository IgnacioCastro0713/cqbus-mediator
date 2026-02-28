<?php

declare(strict_types=1);

namespace Tests\Fixtures\Handlers;

use Ignaciocastro0713\CqbusMediator\Attributes\Handlers\RequestHandler;
use Ignaciocastro0713\CqbusMediator\Attributes\Pipelines\Pipeline;
use Ignaciocastro0713\CqbusMediator\Attributes\Pipelines\SkipGlobalPipelines;

#[RequestHandler(SkipGlobalWithHandlerPipelineRequest::class)]
#[SkipGlobalPipelines]
#[Pipeline(FirstPipeline::class)]
class SkipGlobalWithHandlerPipelineHandler
{
    public function handle(SkipGlobalWithHandlerPipelineRequest $request): array
    {
        return [
            'value' => $request->value,
            'order' => $request->pipelineOrder,
        ];
    }
}
