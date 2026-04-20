<?php

declare(strict_types=1);

namespace Ignaciocastro0713\CqbusMediator\Console;

use Ignaciocastro0713\CqbusMediator\Attributes\Pipelines\Pipeline;
use Ignaciocastro0713\CqbusMediator\Attributes\Pipelines\SkipGlobalPipelines;
use Ignaciocastro0713\CqbusMediator\Constants\MediatorConstants;
use Ignaciocastro0713\CqbusMediator\Discovery\MediatorDiscovery;
use Ignaciocastro0713\CqbusMediator\Exceptions\InvalidRequestClassException;
use Ignaciocastro0713\CqbusMediator\MediatorConfig;
use Ignaciocastro0713\CqbusMediator\Routing\ActionDecoratorManager;
use Illuminate\Console\Command;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\Console\Command\Command as ConsoleCommand;

class CacheCommand extends Command
{
    protected $signature = 'mediator:cache';
    protected $description = 'Create a cache file for the Mediator handlers, notifications and actions.';

    /**
     * @throws ReflectionException|InvalidRequestClassException
     */
    public function handle(): int
    {
        $this->info('Caching Mediator handlers, notifications and actions...');

        $handlerPaths = MediatorConfig::handlerPaths();
        $discovered = MediatorDiscovery::discover($handlerPaths);

        $handlers = $discovered['handlers'];
        $notifications = $discovered['notifications'];
        $actionClasses = $discovered['actions'];

        $actions = [];
        $decoratorManager = app(ActionDecoratorManager::class);

        foreach ($actionClasses as $actionClass => $config) {
            /** @var class-string $actionClass */
            $actions[$actionClass] = $decoratorManager->resolveRouteAttributes($actionClass);
        }

        $globalPipelines = MediatorConfig::pipelines();
        $requestPipelines = MediatorConfig::requestPipelines();
        $notificationPipelines = MediatorConfig::notificationPipelines();
        $pipelinesCache = [];

        $notificationClasses = [];
        foreach ($notifications as $handlersList) {
            foreach ($handlersList as $handler) {
                $notificationClasses[] = $handler['handler'];
            }
        }

        $allHandlers = array_unique(array_merge(array_values($handlers), $notificationClasses));

        foreach ($allHandlers as $handlerClass) {
            /** @var class-string $handlerClass */
            $reflection = new ReflectionClass($handlerClass);
            $pipelineAttributes = $reflection->getAttributes(Pipeline::class);
            $handlerPipelines = empty($pipelineAttributes) ? [] : $pipelineAttributes[0]->newInstance()->pipes;

            $shouldSkipGlobal = ! empty($reflection->getAttributes(SkipGlobalPipelines::class));

            $isNotification = in_array($handlerClass, $notificationClasses, true);
            $type = $isNotification
                ? MediatorConstants::PIPELINE_TYPE_NOTIFICATION
                : MediatorConstants::PIPELINE_TYPE_REQUEST;

            $typePipelines = $isNotification ? $notificationPipelines : $requestPipelines;
            $allGlobal = array_merge($globalPipelines, $typePipelines);

            $pipelinesCache[$handlerClass . ':' . $type] = $shouldSkipGlobal
                ? $handlerPipelines
                : array_merge($allGlobal, $handlerPipelines);
        }

        $content = "<?php\n\nreturn " . var_export([
            'handlers' => $handlers,
            'notifications' => $notifications,
            'actions' => $actions,
            'pipelines' => $pipelinesCache,
        ], true) . ";\n";

        $cachePath = $this->laravel->bootstrapPath('cache/mediator.php');
        file_put_contents($cachePath, $content);

        $this->info('Mediator handlers, notifications and actions cached successfully!');

        return ConsoleCommand::SUCCESS;
    }
}
