<?php

namespace Ignaciocastro0713\CqbusMediator\Console;

use Ignaciocastro0713\CqbusMediator\Exceptions\InvalidHandlerException;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class MakeMediatorHandlerCommand extends GeneratorCommand
{
    protected $signature = 'make:mediator-handler {name} {--root=Handlers} {--group=}';
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

        [$fullNamespace, $basePath] = $this->getNamespaceAndPath($folderName);

        $this->generateFile(
            "$basePath/$handlerName.php",
            $this->getStub(),
            [
                '{{ namespace }}' => $fullNamespace,
                '{{ class }}' => $handlerName,
                '{{ requestClass }}' => $requestName,
                '{{ requestNamespace }}' => $fullNamespace,
            ],
            "Handler class [$basePath/$handlerName.php] created successfully."
        );

        $this->generateFile(
            "$basePath/$requestName.php",
            __DIR__ . '/stubs/request.stub',
            [
                '{{ namespace }}' => $fullNamespace,
                '{{ class }}' => $requestName,
            ],
            "Request class [$basePath/$requestName.php] created successfully."
        );

        return true;
    }

    /**
     * @param string $folderName
     * @return array<string>
     */
    private function getNamespaceAndPath(string $folderName): array
    {
        $rootFolderName = $this->option('root');
        $groupFolderName = $this->option('group');

        $rootNamespace = $this->rootNamespace();
        $baseNamespace = "{$rootNamespace}Http\\$rootFolderName";

        $pathComponents = [
            $baseNamespace,
            $groupFolderName,
            $folderName,
        ];

        $pathComponents = array_filter($pathComponents, 'is_string');
        $fullNamespace = implode('\\', $pathComponents);
        $basePath = $this->laravel->basePath() . '/' . str_replace('\\', '/', implode('/', $pathComponents));

        $this->ensureDirectoryExists($basePath);

        return [$fullNamespace, $basePath];
    }

    /**
     * @param string $path
     * @param string $stubPath
     * @param array<string> $replacements
     * @param string $message
     * @return void
     */
    private function generateFile(string $path, string $stubPath, array $replacements, string $message): void
    {
        $stub = file_get_contents($stubPath);
        $content = str_replace(array_keys($replacements), array_values($replacements), $stub);
        $this->files->put($path, $content);
        $this->info($message);
    }

    /**
     * @param string $path
     * @return void
     */
    protected function ensureDirectoryExists(string $path): void
    {
        if (! $this->files->isDirectory($path)) {
            $this->files->makeDirectory($path, 0755, true, true);
        }
    }
}
