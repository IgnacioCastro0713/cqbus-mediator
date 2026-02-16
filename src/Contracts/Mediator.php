<?php

declare(strict_types=1);

namespace Ignaciocastro0713\CqbusMediator\Contracts;

interface Mediator
{
    public function send(object $request): mixed;
}
