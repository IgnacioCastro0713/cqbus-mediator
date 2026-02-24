<?php

declare(strict_types=1);

namespace Ignaciocastro0713\CqbusMediator\Constants;

use Ignaciocastro0713\CqbusMediator\Attributes\ApiRoute;
use Ignaciocastro0713\CqbusMediator\Attributes\Middleware;
use Ignaciocastro0713\CqbusMediator\Attributes\Prefix;
use Ignaciocastro0713\CqbusMediator\Attributes\WebRoute;
use Ignaciocastro0713\CqbusMediator\Traits\AsAction;

final class MediatorConstants
{
    private function __construct()
    {
    }

    public const HANDLE_METHOD = 'handle';
    public const ROUTE_METHOD = 'route';
    public const ACTION_TRAIT = AsAction::class;

    // Attributes
    public const ATTRIBUTE_API_ROUTE = ApiRoute::class;
    public const ATTRIBUTE_WEB_ROUTE = WebRoute::class;
    public const ATTRIBUTE_MIDDLEWARE = Middleware::class;
    public const ATTRIBUTE_PREFIX = Prefix::class;
}
