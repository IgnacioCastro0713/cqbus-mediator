<?php

declare(strict_types=1);

namespace Ignaciocastro0713\CqbusMediator\Exceptions;

use Exception;

class HandlerNotFoundException extends Exception
{
    public function __construct(public readonly string $requestClass)
    {
        $handlerClass = str_replace('Request', 'Handler', class_basename($requestClass));
        $message = "No handler registered for request: $requestClass\n\n";
        $message .= "Suggested solution:\n";
        $message .= "Run:\nphp artisan make:mediator-handler $handlerClass\n\n";
        $message .= "See documentation: https://github.com/IgnacioCastro0713/cqbus-mediator";
        parent::__construct($message);
    }
}
