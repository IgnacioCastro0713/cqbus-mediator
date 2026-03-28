<?php

declare(strict_types=1);

namespace Ignaciocastro0713\CqbusMediator\Constants;

use Ignaciocastro0713\CqbusMediator\Attributes\Handlers\Notification;
use Ignaciocastro0713\CqbusMediator\Attributes\Handlers\RequestHandler;
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
    public const ATTRIBUTE_REQUEST_HANDLER = RequestHandler::class;
    public const ATTRIBUTE_NOTIFICATION = Notification::class;

    // Pipeline Types
    public const PIPELINE_TYPE_REQUEST = 'request';
    public const PIPELINE_TYPE_NOTIFICATION = 'notification';
}
