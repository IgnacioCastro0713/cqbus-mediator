<?php

declare(strict_types=1);

namespace Ignaciocastro0713\CqbusMediator\Console;

use Ignaciocastro0713\CqbusMediator\Console\Concerns\GeneratesMediatorFiles;
use Ignaciocastro0713\CqbusMediator\Exceptions\InvalidHandlerException;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class MakeEventHandlerCommand extends GeneratorCommand
{
    use GeneratesMediatorFiles;

    protected $signature = 'make:mediator-event-handler {name} {--root=Events}';
    protected $description = 'Create a new Event and its corresponding Handler class.';
    protected $type = 'Mediator Event Handler';

    protected function getStub(): string
    {
        return __DIR__ . '/stubs/event-handler.stub';
    }

    /**
     * @return bool
     * @throws InvalidHandlerException
     */
    public function handle(): bool
    {
        $handlerName = $this->getNameInput();

        if (! Str::endsWith($handlerName, 'Handler')) {
            throw new InvalidHandlerException("The event handler's name must end with 'Handler'.");
        }

        $folderName = str_replace('Handler', '', $handlerName);
        $eventName = str_replace('Handler', 'Event', $handlerName);

        [$fullNamespace, $basePath] = $this->getNamespaceAndPath($folderName);

        $handlerPath = $basePath . DIRECTORY_SEPARATOR . $handlerName . '.php';
        $eventPath = $basePath . DIRECTORY_SEPARATOR . $eventName . '.php';

        if (! $this->shouldOverwriteFiles($handlerPath, $eventPath)) {
            return false;
        }

        $this->generateFile(
            $handlerPath,
            $this->getStub(),
            [
                '{{ namespace }}' => $fullNamespace,
                '{{ class }}' => $handlerName,
                '{{ eventClass }}' => $eventName,
                '{{ eventNamespace }}' => $fullNamespace,
            ],
            "Event Handler class [$handlerPath] created successfully."
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
