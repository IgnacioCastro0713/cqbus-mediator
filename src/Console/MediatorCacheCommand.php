<?php

declare(strict_types=1);

namespace Ignaciocastro0713\CqbusMediator\Console;

use Ignaciocastro0713\CqbusMediator\Discovery\DiscoverAction;
use Ignaciocastro0713\CqbusMediator\Discovery\DiscoverHandler;
use Ignaciocastro0713\CqbusMediator\MediatorConfig;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Command\Command as ConsoleCommand;

class MediatorCacheCommand extends Command
{
    protected $signature = 'mediator:cache';
    protected $description = 'Create a cache file for the Mediator handlers and actions.';

    public function handle(): int
    {
        $this->info('Caching Mediator handlers and actions...');

        $handlerPaths = MediatorConfig::handlerPaths();

        $handlers = DiscoverHandler::in(...$handlerPaths)->get();
        $actions = DiscoverAction::in(...$handlerPaths)->get();

        $content = "<?php\n\nreturn " . var_export([
            'handlers' => $handlers,
            'actions' => $actions,
        ], true) . ";\n";

        $cachePath = $this->laravel->bootstrapPath('cache/mediator.php');
        File::put($cachePath, $content);

        $this->info('Mediator handlers and actions cached successfully!');

        return ConsoleCommand::SUCCESS;
    }
}
