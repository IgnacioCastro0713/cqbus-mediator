<?php

declare(strict_types=1);

namespace Ignaciocastro0713\CqbusMediator\Console;

use Ignaciocastro0713\CqbusMediator\Exceptions\InvalidHandlerException;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class MakeMediatorHandlerCommand extends GeneratorCommand
{
    protected $signature = 'make:mediator-handler {name} {--root=Handlers} {--action}';
    protected $description = 'Create a new Request and its corresponding Handler class in a single folder.';
    protected $type = 'Mediator Handler';
    protected bool $overwrite = true;

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

    /**
     * @param string $folderName
     * @return array<string>
     */
    private function getNamespaceAndPath(string $folderName): array
    {
        $rootFolderName = str_replace("/", "\\", $this->option('root'));

        $rootNamespace = $this->rootNamespace();
        $baseNamespace = "{$rootNamespace}Http\\$rootFolderName";

        $pathComponents = [
            $baseNamespace,
            $folderName,
        ];

        $pathComponents = array_filter($pathComponents, 'is_string');
        $fullNamespace = implode('\\', $pathComponents);

        // Calculate the physical path. We use $this->laravel->path() to correctly resolve
        // the "app" directory location (which is lowercase on disk in standard Laravel installs),
        // ensuring compatibility with case-sensitive filesystems (Linux) where "App" != "app".
        $relativePathWithoutApp = str_replace($rootNamespace, '', $fullNamespace);
        $relativePath = str_replace('\\', DIRECTORY_SEPARATOR, $relativePathWithoutApp);

        /** @phpstan-ignore-next-line */
        $basePath = $this->laravel->path() . DIRECTORY_SEPARATOR . $relativePath;
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
        if ($this->files->exists($path) && ! $this->overwrite) {
            return;
        }

        $stub = file_get_contents($stubPath);
        $keys = array_keys($replacements);
        $values = array_values($replacements);

        $content = str_replace($keys, $values, $stub);
        $this->files->put($path, $content);
        $this->info($message);
    }

    /**
     * @param string $path
     * @return void
     */
    private function ensureDirectoryExists(string $path): void
    {
        if (! $this->files->isDirectory($path)) {
            $this->files->makeDirectory($path, 0755, true, true);
        }
    }

    /**
     * @param string ...$paths Request, Handler and Action
     * @return bool
     */
    private function shouldOverwriteFiles(string ...$paths): bool
    {
        $existing = array_filter($paths, [$this->files, 'exists']);

        if (! $existing) {
            $this->overwrite = true;

            return true;
        }

        $message = "The following file(s) already exist:\n- " . implode("\n- ", $existing) . "\nDo you want to overwrite them?";
        $this->overwrite = $this->confirm($message);

        return $this->overwrite;
    }
}
