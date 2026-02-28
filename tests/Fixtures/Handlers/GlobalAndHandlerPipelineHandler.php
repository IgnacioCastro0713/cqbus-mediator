<?php

declare(strict_types=1);

namespace Tests\Fixtures\Handlers;

use Ignaciocastro0713\CqbusMediator\Attributes\Handlers\RequestHandler;
use Ignaciocastro0713\CqbusMediator\Attributes\Pipelines\Pipeline;

#[RequestHandler(GlobalAndHandlerRequest::class)]
#[Pipeline(SecondPipeline::class)]
class GlobalAndHandlerPipelineHandler
{
    public function handle(GlobalAndHandlerRequest $request): array
    {
        return [
            'value' => $request->value,
            'order' => $request->pipelineOrder,
        ];
    }
}
