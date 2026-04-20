<?php

namespace Tests\Fixtures;

use Ignaciocastro0713\CqbusMediator\Attributes\Handlers\RequestHandler;
use Tests\Fixtures\Handlers\BasicRequest;

#[RequestHandler(BasicRequest::class)]
class PrivateConstructorHandler
{
    private function __construct()
    {
    }

    public function handle(BasicRequest $request): void
    {
    }
}
