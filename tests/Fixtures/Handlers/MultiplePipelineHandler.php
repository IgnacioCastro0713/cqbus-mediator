<?php

declare(strict_types=1);

namespace Tests\Fixtures\Handlers;

use Ignaciocastro0713\CqbusMediator\Attributes\Pipeline;
use Ignaciocastro0713\CqbusMediator\Attributes\RequestHandler;

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
