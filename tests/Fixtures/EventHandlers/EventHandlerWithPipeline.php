<?php

declare(strict_types=1);

namespace Tests\Fixtures\EventHandlers;

use Ignaciocastro0713\CqbusMediator\Attributes\Handlers\Notification;
use Ignaciocastro0713\CqbusMediator\Attributes\Pipelines\Pipeline;
use Tests\Fixtures\Events\EventWithPipeline;
use Tests\Fixtures\Handlers\BasicPipeline;

#[Notification(EventWithPipeline::class)]
#[Pipeline(BasicPipeline::class)]
class EventHandlerWithPipeline
{
    public function handle(EventWithPipeline $event): string
    {
        return "pipeline_handler_{$event->data}";
    }
}
