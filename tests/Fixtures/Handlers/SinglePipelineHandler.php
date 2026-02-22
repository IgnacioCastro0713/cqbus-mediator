<?php

declare(strict_types=1);

namespace Tests\Fixtures\Handlers;

use Ignaciocastro0713\CqbusMediator\Attributes\Pipeline;
use Ignaciocastro0713\CqbusMediator\Attributes\RequestHandler;

#[RequestHandler(PipelineTestRequest::class)]
#[Pipeline(FirstPipeline::class)]
class SinglePipelineHandler
{
    public function handle(PipelineTestRequest $request): array
    {
        return [
            'value' => $request->value,
            'order' => $request->pipelineOrder,
        ];
    }
}
