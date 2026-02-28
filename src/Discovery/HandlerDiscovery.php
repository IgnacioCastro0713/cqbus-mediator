<?php

declare(strict_types=1);

namespace Ignaciocastro0713\CqbusMediator\Discovery;

use Ignaciocastro0713\CqbusMediator\Attributes\Handlers\RequestHandler;
use Ignaciocastro0713\CqbusMediator\Exceptions\InvalidRequestClassException;

readonly class HandlerDiscovery extends AbstractDiscovery
{
    /**
     * Extracts the request class name from a handler class using the RequestHandler attribute.
     *
     * @return array<string, string>
     * @throws InvalidRequestClassException
     */
    public function get(): array
    {
        $handlersMap = [];

        foreach ($this->discoverByAttribute(RequestHandler::class) as $handlerClass => $attribute) {
            /** @var RequestHandler $attribute */
            $requestClass = $attribute->requestClass;

            if (empty($requestClass)) {
                continue;
            }

            $this->ensureTargetClassExists($requestClass, $handlerClass);

            $handlersMap[$requestClass] = $handlerClass;
        }

        return $handlersMap;
    }
}
