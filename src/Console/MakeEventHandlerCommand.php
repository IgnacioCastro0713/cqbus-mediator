<?php

declare(strict_types=1);

namespace Ignaciocastro0713\CqbusMediator\Console;

use Ignaciocastro0713\CqbusMediator\Exceptions\InvalidHandlerException;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class MakeEventHandlerCommand extends GeneratorCommand
{
    protected $signature = 'make:mediator-event-handler {name} {--root=Events}';
    protected $description = 'Create a new Event and its corresponding Handler class.';
    protected $type = 'Mediator Event Handler';
    protected bool $overwrite = true;

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

    /**
     * @param string $folderName
     * @return array{0: string, 1: string}
     */
    private function getNamespaceAndPath(string $folderName): array
    {
        /** @var string $rootOption */
        $rootOption = $this->option('root');
        $rootFolderName = str_replace("/", "\\", $rootOption);

        $rootNamespace = $this->rootNamespace();
        $baseNamespace = "{$rootNamespace}Http\\{$rootFolderName}";

        $pathComponents = [
            $baseNamespace,
            $folderName,
        ];

        $pathComponents = array_filter($pathComponents, 'is_string');
        $fullNamespace = implode('\\', $pathComponents);

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
     * @param array<string, string> $replacements
     * @param string $message
     * @return void
     */
    private function generateFile(string $path, string $stubPath, array $replacements, string $message): void
    {
        if ($this->files->exists($path) && ! $this->overwrite) {
            return;
        }

        $stub = file_get_contents($stubPath);
        if ($stub === false) {
            $this->error("Could not read stub file: $stubPath");

            return;
        }

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
     * @param string ...$paths
     * @return bool
     */
    private function shouldOverwriteFiles(string ...$paths): bool
    {
        $existing = array_filter($paths, [$this->files, 'exists']);

        if (! $existing) {
            $this->overwrite = true;

            return true;
        }

        $message = "The following file(s) already exist:
- " . implode("
- ", $existing) . "
Do you want to overwrite them?";
        $this->overwrite = $this->confirm($message);

        return $this->overwrite;
    }
}
