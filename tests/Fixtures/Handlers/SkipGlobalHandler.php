<?php

declare(strict_types=1);

namespace Tests\Fixtures\Handlers;

use Ignaciocastro0713\CqbusMediator\Attributes\RequestHandler;
use Ignaciocastro0713\CqbusMediator\Attributes\SkipGlobalPipelines;

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
