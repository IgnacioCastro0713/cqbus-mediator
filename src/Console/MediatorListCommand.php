<?php

declare(strict_types=1);

namespace Ignaciocastro0713\CqbusMediator\Console;

use Ignaciocastro0713\CqbusMediator\Discovery\DiscoverAction;
use Ignaciocastro0713\CqbusMediator\Discovery\DiscoverHandler;
use Ignaciocastro0713\CqbusMediator\MediatorConfig;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Command\Command as ConsoleCommand;

class MediatorListCommand extends Command
{
    protected $signature = 'mediator:list {--handlers : Show only handlers} {--actions : Show only actions}';
    protected $description = 'List all registered Mediator handlers and actions.';

    public function handle(): int
    {
        $showHandlers = $this->option('handlers') || ! $this->option('actions');
        $showActions = $this->option('actions') || ! $this->option('handlers');

        $cachePath = $this->laravel->bootstrapPath('cache/mediator.php');
        $fromCache = File::exists($cachePath);

        if ($fromCache) {
            $cached = require $cachePath;
            $handlers = $cached['handlers'] ?? [];
            $actions = $cached['actions'] ?? [];
            $this->info('📦 Loading from cache: bootstrap/cache/mediator.php');
        } else {
            $handlerPaths = MediatorConfig::handlerPaths();
            $handlers = DiscoverHandler::in(...$handlerPaths)->get();
            $actions = DiscoverAction::in(...$handlerPaths)->get();
            $this->warn('⚡ Discovering from source (not cached)');
        }

        $this->newLine();

        if ($showHandlers) {
            $this->displayHandlers($handlers);
        }

        if ($showActions) {
            $this->displayActions($actions);
        }

        $this->displaySummary(count($handlers), count($actions), $showHandlers, $showActions);

        return ConsoleCommand::SUCCESS;
    }

    /**
     * @param array<string, string> $handlers
     */
    private function displayHandlers(array $handlers): void
    {
        $this->components->info('Handlers');

        if (empty($handlers)) {
            $this->line('  No handlers registered.');
            $this->newLine();

            return;
        }

        $rows = [];
        foreach ($handlers as $request => $handler) {
            $rows[] = [$request, $handler];
        }

        $this->table(['Request', 'Handler'], $rows);
        $this->newLine();
    }

    /**
     * @param array<string> $actions
     */
    private function displayActions(array $actions): void
    {
        $this->components->info('Actions');

        if (empty($actions)) {
            $this->line('  No actions registered.');
            $this->newLine();

            return;
        }

        $rows = [];
        foreach ($actions as $action) {
            $rows[] = [$action];
        }

        $this->table(['Action Class'], $rows);
        $this->newLine();
    }

    private function displaySummary(int $handlersCount, int $actionsCount, bool $showHandlers, bool $showActions): void
    {
        $parts = [];

        if ($showHandlers) {
            $parts[] = "<fg=cyan>Handlers:</> {$handlersCount}";
        }

        if ($showActions) {
            $parts[] = "<fg=magenta>Actions:</> {$actionsCount}";
        }

        $this->line('  ' . implode(' | ', $parts));
    }
}
