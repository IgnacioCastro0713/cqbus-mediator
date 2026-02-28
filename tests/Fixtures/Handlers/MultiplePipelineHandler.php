<?php

declare(strict_types=1);

namespace Tests\Fixtures\Handlers;

use Ignaciocastro0713\CqbusMediator\Attributes\Handlers\RequestHandler;
use Ignaciocastro0713\CqbusMediator\Attributes\Pipelines\Pipeline;

#[RequestHandler(MultiplePipelineRequest::class)]
#[Pipeline([FirstPipeline::class, SecondPipeline::class])]
class MultiplePipelineHandler
{
    public function handle(MultiplePipelineRequest $request): array
    {
        return [
            'value' => $request->value,
            'order' => $request->pipelineOrder,
        ];
    }
}
