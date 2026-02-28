<?php

declare(strict_types=1);

namespace Ignaciocastro0713\CqbusMediator\Exceptions;

use Exception;

class MissingRouteAttributeException extends Exception
{
    /**
     * @var class-string
     */
    public readonly string $actionClass;

    /**
     * @param class-string $actionClass
     */
    public function __construct(string $actionClass)
    {
        $this->actionClass = $actionClass;

        parent::__construct(
            "The action class '$actionClass' must have at least one routing attribute: " .
            "#[Api] or #[Web]."
        );
    }
}
