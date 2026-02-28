<?php

declare(strict_types=1);

namespace Ignaciocastro0713\CqbusMediator\Console;

use Ignaciocastro0713\CqbusMediator\Discovery\MediatorDiscovery;
use Ignaciocastro0713\CqbusMediator\MediatorConfig;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as ConsoleCommand;

class ListCommand extends Command
{
    protected $signature = 'mediator:list 
                            {--handlers : Show only handlers} 
                            {--actions : Show only actions}
                            {--events : Show only notifications}';
    protected $description = 'List all registered Mediator handlers, notifications, and actions.';

    public function handle(): int
    {
        $hasFilters = $this->option('handlers') || $this->option('actions') || $this->option('events');

        $showHandlers = $hasFilters ? $this->option('handlers') : true;
        $showActions = $hasFilters ? $this->option('actions') : true;
        $showEvents = $hasFilters ? $this->option('events') : true;

        $cachePath = $this->laravel->bootstrapPath('cache/mediator.php');
        $fromCache = is_file($cachePath);

        if ($fromCache) {
            $cached = require $cachePath;
            $handlers = $cached['handlers'] ?? [];
            $actions = $cached['actions'] ?? [];
            $notifications = $cached['notifications'] ?? [];
            $this->info('📦 Loading from cache: bootstrap/cache/mediator.php');
        } else {
            $handlerPaths = MediatorConfig::handlerPaths();
            $discovered = MediatorDiscovery::discover($handlerPaths);
            $handlers = $discovered['handlers'];
            $actions = $discovered['actions'];
            $notifications = $discovered['notifications'];
            $this->warn('⚡ Discovering from source (not cached)');
        }

        $this->newLine();

        if ($showHandlers) {
            $this->displayHandlers($handlers);
        }

        if ($showEvents) {
            $this->displayNotifications($notifications);
        }

        if ($showActions) {
            $this->displayActions($actions);
        }

        $totalEventHandlers = array_sum(array_map('count', $notifications));
        $this->displaySummary(count($handlers), $totalEventHandlers, count($actions), (bool) $showHandlers, (bool) $showEvents, (bool) $showActions);

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
     * @param array<string, array<array{handler: string, priority: int}>> $notifications
     */
    private function displayNotifications(array $notifications): void
    {
        $this->components->info('Event Handlers');

        if (empty($notifications)) {
            $this->line('  No notifications registered.');
            $this->newLine();

            return;
        }

        $rows = [];
        foreach ($notifications as $event => $handlers) {
            foreach ($handlers as $handlerInfo) {
                $rows[] = [
                    $event,
                    $handlerInfo['handler'],
                    $handlerInfo['priority'],
                ];
            }
        }

        $this->table(['Event', 'Handler', 'Priority'], $rows);
        $this->newLine();
    }

    /**
     * @param array<int|string, mixed> $actions
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
        foreach ($actions as $key => $value) {
            $actionClass = is_string($key) ? $key : $value;
            $rows[] = [$actionClass];
        }

        $this->table(['Action Class'], $rows);
        $this->newLine();
    }

    private function displaySummary(int $handlersCount, int $notificationsCount, int $actionsCount, bool $showHandlers, bool $showEvents, bool $showActions): void
    {
        $parts = [];

        if ($showHandlers) {
            $parts[] = "<fg=cyan>Handlers:</> {$handlersCount}";
        }

        if ($showEvents) {
            $parts[] = "<fg=yellow>Notifications:</> {$notificationsCount}";
        }

        if ($showActions) {
            $parts[] = "<fg=magenta>Actions:</> {$actionsCount}";
        }

        $this->line('  ' . implode(' | ', $parts));
    }
}
