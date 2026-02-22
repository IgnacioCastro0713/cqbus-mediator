<?php

declare(strict_types=1);

namespace Tests\Fixtures\Handlers;

use Ignaciocastro0713\CqbusMediator\Attributes\RequestHandler;

/**
 * Handler with empty requestClass - should be skipped by discovery
 */
#[RequestHandler('')]
class EmptyRequestClassHandler
{
    public function handle(): void
    {
    }
}
