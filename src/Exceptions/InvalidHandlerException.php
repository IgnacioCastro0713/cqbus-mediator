<?php

namespace Ignaciocastro0713\CqbusMediator\Exceptions;

use Exception;

class InvalidHandlerException extends Exception
{
    public function __construct(mixed $handlerOrMessage)
    {
        if (is_object($handlerOrMessage)) {
            $className = get_class($handlerOrMessage);
            $message = "The Handler '$className' is invalid.\n\n";
            $message .= "Reason: Missing 'handle' method.\n\n";
            $message .= "Suggested solution:\n";
            $message .= "Implement a public 'handle' method in '{$className}' that accepts the corresponding Request class.";
        } else {
            $message = $handlerOrMessage;
        }

        parent::__construct($message);
    }
}
