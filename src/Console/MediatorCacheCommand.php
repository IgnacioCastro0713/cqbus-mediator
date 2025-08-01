<?php

namespace Ignaciocastro0713\CqbusMediator\Console;

use Ignaciocastro0713\CqbusMediator\HandlerDiscovery;
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
        $cachePath = $this->laravel->bootstrapPath('cache/mediator_handlers.php');
        $handlerPaths = HandlerDiscovery::getHandlerPaths();
        $discoveredHandlers = HandlerDiscovery::discoverHandlers($handlerPaths);
        $handlersMap = [];

        foreach ($discoveredHandlers as $handlerClass) {
            $requestClass = HandlerDiscovery::getRequestClass($handlerClass);

            if ($requestClass === null) {
                continue;
            }

            $handlersMap[$requestClass] = $handlerClass;
        }

        $content = "<?php\n\nreturn " . var_export($handlersMap, true) . ";\n";
        File::put($cachePath, $content);
        $this->info('Mediator handlers cached successfully!');

        return ConsoleCommand::SUCCESS;
    }
}
