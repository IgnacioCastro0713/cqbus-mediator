<?php

declare(strict_types=1);

namespace Tests\Fixtures\Handlers;

use Ignaciocastro0713\CqbusMediator\Attributes\Pipeline;
use Ignaciocastro0713\CqbusMediator\Attributes\RequestHandler;

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
