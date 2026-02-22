<?php

declare(strict_types=1);

namespace Tests\Fixtures\Handlers;

use Ignaciocastro0713\CqbusMediator\Attributes\RequestHandler;

/**
 * Abstract handler - should be skipped by discovery (not instantiable)
 */
#[RequestHandler(AbstractHandlerRequest::class)]
abstract class AbstractHandler
{
    abstract public function handle(AbstractHandlerRequest $request): void;
}

class AbstractHandlerRequest
{
}

