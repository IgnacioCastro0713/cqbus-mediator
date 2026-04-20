<?php

declare(strict_types=1);

namespace Tests\Fixtures\Handlers;

use Ignaciocastro0713\CqbusMediator\Attributes\Handlers\QueryHandler;

#[QueryHandler(GetUserQuery::class)]
class GetUserQueryHandler
{
    public function handle(GetUserQuery $query): string
    {
        return 'user_found:' . $query->id;
    }
}
