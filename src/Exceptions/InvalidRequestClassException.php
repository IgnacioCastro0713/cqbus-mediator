<?php

declare(strict_types=1);

namespace Ignaciocastro0713\CqbusMediator\Exceptions;

use Exception;

class InvalidRequestClassException extends Exception
{
    public function __construct(
        public readonly string $requestClass,
        public readonly string $handlerClass,
        public readonly string $attributeClass = ''
    ) {
        $attrName = $attributeClass !== '' ? '#[' . class_basename($attributeClass) . ']' : '';
        $source = $attrName !== '' ? "referenced in $attrName on '$handlerClass'" : "specified in handler '$handlerClass'";

        $message = "Request class '$requestClass' $source does not exist.\n\n";
        $message .= "Suggested solutions:\n";
        $message .= "1. Verify the namespace and class name are correct\n";
        $message .= "2. Make sure the request class file exists\n";
        $message .= "3. Run 'composer dump-autoload' to refresh autoloading\n";

        parent::__construct($message);
    }
}
