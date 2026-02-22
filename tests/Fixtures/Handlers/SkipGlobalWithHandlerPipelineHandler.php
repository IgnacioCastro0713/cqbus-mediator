<?php

declare(strict_types=1);

namespace Tests\Fixtures\Handlers;

use Ignaciocastro0713\CqbusMediator\Attributes\Pipeline;
use Ignaciocastro0713\CqbusMediator\Attributes\RequestHandler;
use Ignaciocastro0713\CqbusMediator\Attributes\SkipGlobalPipelines;

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
