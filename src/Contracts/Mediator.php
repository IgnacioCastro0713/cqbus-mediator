<?php

namespace Ignaciocastro0713\CqbusMediator\Contracts;

interface Mediator
{
    public function send(object $request): mixed;
}
