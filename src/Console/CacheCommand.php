<?php

declare(strict_types=1);

namespace Ignaciocastro0713\CqbusMediator\Console;

use Ignaciocastro0713\CqbusMediator\Discovery\ActionDiscovery;
use Ignaciocastro0713\CqbusMediator\Discovery\EventHandlerDiscovery;
use Ignaciocastro0713\CqbusMediator\Discovery\HandlerDiscovery;
use Ignaciocastro0713\CqbusMediator\MediatorConfig;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Command\Command as ConsoleCommand;

class CacheCommand extends Command
{
    protected $signature = 'mediator:cache';
    protected $description = 'Create a cache file for the Mediator handlers, event handlers and actions.';

    public function handle(): int
    {
        $this->info('Caching Mediator handlers, event handlers and actions...');

        $handlerPaths = MediatorConfig::handlerPaths();

        $handlers = HandlerDiscovery::in(...$handlerPaths)->get();
        $eventHandlers = EventHandlerDiscovery::in(...$handlerPaths)->get();
        $actions = ActionDiscovery::in(...$handlerPaths)->get();

        $content = "<?php\n\nreturn " . var_export([
            'handlers' => $handlers,
            'event_handlers' => $eventHandlers,
            'actions' => $actions,
        ], true) . ";\n";

        $cachePath = $this->laravel->bootstrapPath('cache/mediator.php');
        File::put($cachePath, $content);

        $this->info('Mediator handlers, event handlers and actions cached successfully!');

        return ConsoleCommand::SUCCESS;
    }
}
