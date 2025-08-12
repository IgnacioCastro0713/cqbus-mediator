<?php

namespace Ignaciocastro0713\CqbusMediator\Exceptions;

use Exception;

class InvalidActionException extends Exception
{
    public function __construct(object $action, string $methodName)
    {
        parent::__construct("Action Class '" . get_class($action) . "' must have a '" . $methodName . "' method.");
    }
}
