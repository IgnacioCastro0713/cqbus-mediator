<?php

declare(strict_types=1);

namespace Ignaciocastro0713\CqbusMediator\Console\Concerns;

use Exception;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Application;

/**
 * @property Filesystem $files
 * @property Application $laravel
 * @method string rootNamespace()
 * @method mixed option(string $key = null)
 * @method void error(string $string, string|int|null $verbosity = null)
 * @method void info(string $string, string|int|null $verbosity = null)
 * @method bool confirm(string $question, bool $default = false)
 */
trait GeneratesMediatorFiles
{
    /**
     * @param string $folderName
     * @return array{0: string, 1: string}
     */
    protected function getNamespaceAndPath(string $folderName): array
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
        $basePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $basePath);
        
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
    protected function generateFile(string $path, string $stubPath, array $replacements, string $message): void
    {
        try {
            $stub = $this->files->get($stubPath);
        } catch (Exception $e) {
            $this->error("Could not read stub file: $stubPath. Error: " . $e->getMessage());
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
    protected function ensureDirectoryExists(string $path): void
    {
        if (! $this->files->isDirectory($path)) {
            $this->files->makeDirectory($path, 0755, true, true);
        }
    }

    /**
     * @param string ...$paths
     * @return bool
     */
    protected function shouldOverwriteFiles(string ...$paths): bool
    {
        $existing = array_filter($paths, [$this->files, 'exists']);

        if (! $existing) {
            return true;
        }

        // Canonicalize paths to ensure consistency
        $existing = array_map(fn ($p) => realpath($p) ?: $p, $existing);

        $message = "The following file(s) already exist:\n- " . implode("\n- ", $existing) . "\nDo you want to overwrite them?";

        return $this->confirm($message);
    }
}
