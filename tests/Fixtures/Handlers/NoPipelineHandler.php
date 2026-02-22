<?php

declare(strict_types=1);

namespace Tests\Fixtures\Handlers;

use Ignaciocastro0713\CqbusMediator\Attributes\RequestHandler;

#[RequestHandler(NoPipelineRequest::class)]
class NoPipelineHandler
{
    public function handle(NoPipelineRequest $request): string
    {
        return $request->value;
    }
}
