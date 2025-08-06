<?php

namespace Ignaciocastro0713\CqbusMediator\Console;

use Ignaciocastro0713\CqbusMediator\Config;
use Ignaciocastro0713\CqbusMediator\Discovery\DiscoverHandler;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Command\Command as ConsoleCommand;

class MediatorCacheCommand extends Command
{
    protected $signature = 'mediator:cache';
    protected $description = 'Create a cache file for the Mediator handlers.';

    public function handle(): int
    {
        $this->info('Caching Mediator handlers...');

        $handlerPaths = Config::handlerPaths();
        $handlers = DiscoverHandler::in(...$handlerPaths)->get();
        $content = "<?php\n\nreturn " . var_export($handlers, true) . ";\n";

        $cachePath = $this->laravel->bootstrapPath('cache/mediator_handlers.php');
        File::put($cachePath, $content);

        $this->info('Mediator handlers cached successfully!');

        return ConsoleCommand::SUCCESS;
    }
}
