<?php

declare(strict_types=1);

namespace Ignaciocastro0713\CqbusMediator\Console;

use Ignaciocastro0713\CqbusMediator\Console\Concerns\GeneratesMediatorFiles;
use Ignaciocastro0713\CqbusMediator\Exceptions\InvalidHandlerException;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class MakeNotificationCommand extends GeneratorCommand
{
    use GeneratesMediatorFiles;

    protected $signature = 'make:mediator-notification {name} {--root=Events}';
    protected $description = 'Create a new Event and its corresponding Notification class.';
    protected $type = 'Mediator Notification';

    protected function getStub(): string
    {
        return __DIR__ . '/stubs/notification.stub';
    }

    /**
     * @return bool
     * @throws InvalidHandlerException
     */
    public function handle(): bool
    {
        $notificationName = $this->getNameInput();

        if (! Str::endsWith($notificationName, 'Notification')) {
            throw new InvalidHandlerException("The notification's name must end with 'Notification'.");
        }

        $folderName = str_replace('Notification', '', $notificationName);
        $eventName = $folderName . 'Event';

        [$fullNamespace, $basePath] = $this->getNamespaceAndPath($folderName);

        $notificationPath = $basePath . DIRECTORY_SEPARATOR . $notificationName . '.php';
        $eventPath = $basePath . DIRECTORY_SEPARATOR . $eventName . '.php';

        if (! $this->shouldOverwriteFiles($notificationPath, $eventPath)) {
            return false;
        }

        $this->generateFile(
            $notificationPath,
            $this->getStub(),
            [
                '{{ namespace }}' => $fullNamespace,
                '{{ class }}' => $notificationName,
                '{{ eventClass }}' => $eventName,
                '{{ eventNamespace }}' => $fullNamespace,
            ],
            "Notification class [$notificationPath] created successfully."
        );

        $this->generateFile(
            $eventPath,
            __DIR__ . '/stubs/event.stub',
            [
                '{{ namespace }}' => $fullNamespace,
                '{{ class }}' => $eventName,
            ],
            "Event class [$eventPath] created successfully."
        );

        return true;
    }
}
