<?php

declare(strict_types=1);

namespace Ignaciocastro0713\CqbusMediator\Console;

use Ignaciocastro0713\CqbusMediator\Attributes\Pipelines\Pipeline;
use Ignaciocastro0713\CqbusMediator\Attributes\Pipelines\SkipGlobalPipelines;
use Ignaciocastro0713\CqbusMediator\Discovery\ActionDiscovery;
use Ignaciocastro0713\CqbusMediator\Discovery\EventHandlerDiscovery;
use Ignaciocastro0713\CqbusMediator\Discovery\HandlerDiscovery;
use Ignaciocastro0713\CqbusMediator\Exceptions\InvalidRequestClassException;
use Ignaciocastro0713\CqbusMediator\Exceptions\MissingRouteAttributeException;
use Ignaciocastro0713\CqbusMediator\MediatorConfig;
use Ignaciocastro0713\CqbusMediator\Routing\ActionDecoratorManager;
use Illuminate\Console\Command;
use ReflectionClass;
use Symfony\Component\Console\Command\Command as ConsoleCommand;

class CacheCommand extends Command
{
    protected $signature = 'mediator:cache';
    protected $description = 'Create a cache file for the Mediator handlers, event handlers and actions.';

    /**
     * @throws \ReflectionException
     * @throws MissingRouteAttributeException
     * @throws InvalidRequestClassException
     */
    public function handle(): int
    {
        $this->info('Caching Mediator handlers, event handlers and actions...');

        $handlerPaths = MediatorConfig::handlerPaths();

        $handlers = HandlerDiscovery::in(...$handlerPaths)->get();
        $eventHandlers = EventHandlerDiscovery::in(...$handlerPaths)->get();
        $actionClasses = ActionDiscovery::in(...$handlerPaths)->get();

        $actions = [];
        $decoratorManager = app(ActionDecoratorManager::class);

        foreach ($actionClasses as $actionClass) {
            $actions[$actionClass] = $decoratorManager->resolveRouteAttributes($actionClass);
        }

        $globalPipelines = MediatorConfig::pipelines();
        $pipelinesCache = [];

        $eventHandlerClasses = [];
        foreach ($eventHandlers as $handlersList) {
            foreach ($handlersList as $handler) {
                $eventHandlerClasses[] = $handler['handler'];
            }
        }

        $allHandlers = array_unique(array_merge(array_values($handlers), $eventHandlerClasses));

        foreach ($allHandlers as $handlerClass) {
            /** @var class-string $handlerClass */
            $reflection = new ReflectionClass($handlerClass);
            $pipelineAttributes = $reflection->getAttributes(Pipeline::class);
            $handlerPipelines = empty($pipelineAttributes) ? [] : $pipelineAttributes[0]->newInstance()->pipes;

            $shouldSkipGlobal = ! empty($reflection->getAttributes(SkipGlobalPipelines::class));

            $pipelinesCache[$handlerClass] = $shouldSkipGlobal
                ? $handlerPipelines
                : array_merge($globalPipelines, $handlerPipelines);
        }

        $content = "<?php\n\nreturn " . var_export([
            'handlers' => $handlers,
            'event_handlers' => $eventHandlers,
            'actions' => $actions,
            'pipelines' => $pipelinesCache,
        ], true) . ";\n";

        $cachePath = $this->laravel->bootstrapPath('cache/mediator.php');
        file_put_contents($cachePath, $content);

        $this->info('Mediator handlers, event handlers and actions cached successfully!');

        return ConsoleCommand::SUCCESS;
    }
}
