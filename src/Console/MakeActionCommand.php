<?php

declare(strict_types=1);

namespace Ignaciocastro0713\CqbusMediator\Console;

use Ignaciocastro0713\CqbusMediator\Console\Concerns\GeneratesMediatorFiles;
use Ignaciocastro0713\CqbusMediator\Exceptions\InvalidHandlerException;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class MakeActionCommand extends GeneratorCommand
{
    use GeneratesMediatorFiles;

    protected $signature = 'make:mediator-action {name} {--root=Handlers}';
    protected $description = 'Create a new Action and its corresponding Request class.';
    protected $type = 'Mediator Action';

    protected function getStub(): string
    {
        return __DIR__ . '/stubs/action.stub';
    }

    /**
     * @return bool
     * @throws InvalidHandlerException
     */
    public function handle(): bool
    {
        $actionName = $this->getNameInput();

        if (! Str::endsWith($actionName, 'Action')) {
            throw new InvalidHandlerException("The action's name must end with 'Action'.");
        }

        $folderName = str_replace('Action', '', $actionName);
        $requestName = str_replace('Action', 'Request', $actionName);

        [$fullNamespace, $basePath] = $this->getNamespaceAndPath($folderName);

        $actionPath = $basePath . DIRECTORY_SEPARATOR . $actionName . '.php';
        $requestPath = $basePath . DIRECTORY_SEPARATOR . $requestName . '.php';

        if (! $this->shouldOverwriteFiles($actionPath, $requestPath)) {
            return false;
        }

        $this->generateFile(
            $actionPath,
            $this->getStub(),
            [
                '{{ namespace }}' => $fullNamespace,
                '{{ class }}' => $actionName,
                '{{ requestClass }}' => $requestName,
            ],
            "Action class [$actionPath] created successfully."
        );

        $this->generateFile(
            $requestPath,
            __DIR__ . '/stubs/request.stub',
            [
                '{{ namespace }}' => $fullNamespace,
                '{{ class }}' => $requestName,
            ],
            "Request class [$requestPath] created successfully."
        );

        return true;
    }
}
