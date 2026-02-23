<?php

declare(strict_types=1);

namespace Ignaciocastro0713\CqbusMediator\Console;

use Exception;
use Ignaciocastro0713\CqbusMediator\Console\Concerns\GeneratesMediatorFiles;
use Ignaciocastro0713\CqbusMediator\Exceptions\InvalidHandlerException;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class MakeHandlerCommand extends GeneratorCommand
{
    use GeneratesMediatorFiles;

    protected $signature = 'make:mediator-handler {name} {--root=Handlers} {--action}';
    protected $description = 'Create a new Request and its corresponding Handler class in a single folder.';
    protected $type = 'Mediator Handler';

    protected function getStub(): string
    {
        return __DIR__ . '/stubs/handler.stub';
    }

    /**
     * @return bool
     * @throws InvalidHandlerException
     */
    public function handle(): bool
    {
        $handlerName = $this->getNameInput();

        if (! Str::endsWith($handlerName, 'Handler')) {
            throw new InvalidHandlerException("The handler's name must end with 'Handler'.");
        }

        $folderName = str_replace('Handler', '', $handlerName);
        $requestName = str_replace('Handler', 'Request', $handlerName);
        $actionName = str_replace('Handler', 'Action', $handlerName);

        [$fullNamespace, $basePath] = $this->getNamespaceAndPath($folderName);

        $handlerPath = $basePath . DIRECTORY_SEPARATOR . $handlerName . '.php';
        $requestPath = $basePath . DIRECTORY_SEPARATOR . $requestName . '.php';
        $actionPath = $basePath . DIRECTORY_SEPARATOR . $actionName . '.php';

        if (! $this->shouldOverwriteFiles($handlerPath, $requestPath, $actionPath)) {
            return false;
        }

        $this->generateFile(
            $handlerPath,
            $this->getStub(),
            [
                '{{ namespace }}' => $fullNamespace,
                '{{ class }}' => $handlerName,
                '{{ requestClass }}' => $requestName,
                '{{ requestNamespace }}' => $fullNamespace,
            ],
            "Handler class [$handlerPath] created successfully."
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

        $action = (bool)$this->option('action');
        if ($action) {
            $this->generateFile(
                $actionPath,
                __DIR__ . '/stubs/action.stub',
                [
                    '{{ namespace }}' => $fullNamespace,
                    '{{ class }}' => $actionName,
                    '{{ requestClass }}' => $requestName,
                ],
                "Action class [$actionPath] created successfully."
            );
        }


        return true;
    }
}
