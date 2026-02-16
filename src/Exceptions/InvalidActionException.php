<?php

namespace Ignaciocastro0713\CqbusMediator\Exceptions;

use Exception;

class InvalidActionException extends Exception
{
    public function __construct(object $action, string $methodName)
    {
        $className = get_class($action);

        $message = "Action Class '$className' is missing the required '$methodName' method.\n\n";
        $message .= "Suggested solution:\n";
        $message .= "Ensure you have implemented the method with the following signature:\n";
        $message .= "    public function $methodName(\$request)\n\n";
        $message .= "If you are using the 'AsAction' trait, this method is the entry point for your controller logic.";

        parent::__construct($message);
    }
}
