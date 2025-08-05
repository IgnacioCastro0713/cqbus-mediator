<?php

namespace Ignaciocastro0713\CqbusMediator\Console;

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

        $paths = config('mediator.handler_paths', app_path());
        if (! is_array($paths)) {
            $paths = [$paths ?? app_path()];
        }

        $cachePath = $this->laravel->bootstrapPath('cache/mediator_handlers.php');
        $handlers = DiscoverHandler::in(...$paths)->get();

        $content = "<?php\n\nreturn " . var_export($handlers, true) . ";\n";
        File::put($cachePath, $content);

        $this->info('Mediator handlers cached successfully!');

        return ConsoleCommand::SUCCESS;
    }
}
