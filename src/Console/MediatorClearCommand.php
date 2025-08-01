<?php

namespace Ignaciocastro0713\CqbusMediator\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Command\Command as ConsoleCommand;

class MediatorClearCommand extends Command
{
    protected $signature = 'mediator:clear';
    protected $description = 'Remove the Mediator handlers cache file.';

    public function handle(): int
    {
        $cachePath = $this->laravel->bootstrapPath('cache/mediator_handlers.php');

        if (File::exists($cachePath)) {
            File::delete($cachePath);
        }

        $this->info('Mediator handlers cache cleared successfully.');

        return ConsoleCommand::SUCCESS;
    }
}
