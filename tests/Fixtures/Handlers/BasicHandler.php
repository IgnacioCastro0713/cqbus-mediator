<?php

declare(strict_types=1);

namespace Tests\Fixtures\Handlers;

use Ignaciocastro0713\CqbusMediator\Attributes\Handlers\RequestHandler;

#[RequestHandler(BasicRequest::class)]
class BasicHandler
{
    public function handle(BasicRequest $request): string
    {
        return $request->name;
    }
}
