<?php

namespace Tests\InvalidFixtures\InvalidHandlers\AbstractHandler;

use Ignaciocastro0713\CqbusMediator\Attributes\RequestHandler;
use Tests\Fixtures\Handlers\BasicRequest;

#[RequestHandler(BasicRequest::class)]
abstract class AbstractHandler
{
    abstract public function handle(BasicRequest $request): void;
}
