<?php

declare(strict_types=1);

namespace Tests\Fixtures\Handlers;

class GetUserQuery
{
    public function __construct(public readonly string $id = '1') {}
}
