<?php

declare(strict_types=1);

namespace Ignaciocastro0713\CqbusMediator\Constants;

use Ignaciocastro0713\CqbusMediator\Attributes\Routing\Api;
use Ignaciocastro0713\CqbusMediator\Attributes\Routing\Web;
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
    public const ATTRIBUTE_API_ROUTE = Api::class;
    public const ATTRIBUTE_WEB_ROUTE = Web::class;
}
