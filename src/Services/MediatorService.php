<?php

namespace Ignaciocastro0713\CqbusMediator\Services;

use Ignaciocastro0713\CqbusMediator\Contracts\Mediator;
use Ignaciocastro0713\CqbusMediator\Attributes\RequestHandler;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Filesystem\Filesystem;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use SplFileInfo;
use Symfony\Component\Finder\Finder;

class MediatorService implements Mediator
{
    protected array $handlers = [];
    protected Filesystem $files;

    public function __construct(protected Container $container, Filesystem $files)
    {
        $this->files = $files;
    }

    /**
     * Dispatches a (request) to its handler and returns the result.
     *
     * @param object $request The Request object to send.
     * @return mixed The result from the handler.
     * @throws InvalidArgumentException If no handler is registered for the given request.
     * @throws BindingResolutionException If the handler cannot be resolved by the container.
     */
    public function send(object $request): mixed
    {
        $requestClass = get_class($request);
        $handlerClass = $this->handlers[$requestClass] ?? null;

        if (!$handlerClass) {
            throw new InvalidArgumentException("No handler registered for command: " . $requestClass);
        }

        $handler = $this->container->make($handlerClass);
        return $this->executeHandler($handler, $request);
    }

    /**
     * Scans configured directories for handlers marked with the RequestHandler attribute.
     * Uses paths and namespaces defined in the 'mediator.php' configuration file.
     *
     * @return void
     * @throws BindingResolutionException
     */
    public function scanHandlers(): void
    {
        $handlerPaths = config('mediator.handler_paths', []);
        $handlerNamespaces = config('mediator.handler_namespaces', []);
        $excludePaths = config('mediator.exclude_paths', []);

        foreach ($handlerPaths as $directoryPath) {
            if (!$this->files->isDirectory($directoryPath)) {
                continue;
            }

            $finder = new Finder();
            $finder->files()->in($directoryPath);

            foreach ($excludePaths as $exclude) {
                $finder->exclude($exclude);
            }

            $finder->name('*.php');

            foreach ($finder as $file) {
                $className = $this->deriveClassNameFromFile($file, $handlerNamespaces);

                if (!$className || !class_exists($className)) {
                    continue;
                }

                try {
                    $reflectionClass = new ReflectionClass($className);

                    if ($reflectionClass->isAbstract() || $reflectionClass->isInterface() || $reflectionClass->isAnonymous()) {
                        continue;
                    }

                    $attributes = $reflectionClass->getAttributes(RequestHandler::class);

                    foreach ($attributes as $attribute) {
                        $requestHandlerAttribute = $attribute->newInstance();
                        $this->handlers[$requestHandlerAttribute->requestClass] = $className;
                    }

                } catch (ReflectionException $e) {
                    $this->container->make('log')->warning("Could not reflect class $className: {$e->getMessage()}");
                    continue;
                }
            }
        }
    }

    /**
     * Derives the Fully Qualified Class Name (FQCN) from a SplFileInfo object
     * using the configured namespace mappings.
     *
     * @param SplFileInfo $file The file object.
     * @param array $namespaceMappings Configured root namespaces and their paths.
     * @return string|null The FQCN or null if it cannot be determined.
     */
    protected function deriveClassNameFromFile(SplFileInfo $file, array $namespaceMappings): ?string
    {
        $filePath = $file->getPathname();

        foreach ($namespaceMappings as $namespacePrefix => $directoryPath) {
            $directoryPath = rtrim($directoryPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

            if (str_starts_with($filePath, $directoryPath)) {
                $relativePath = substr($filePath, strlen($directoryPath));
                $classSuffix = str_replace([DIRECTORY_SEPARATOR, '.php'], ['\\', ''], $relativePath);

                return rtrim($namespacePrefix, '\\') . '\\' . $classSuffix;
            }
        }
        return null;
    }

    /**
     * Executes the 'handle' method on the given handler object.
     *
     * @param object $handler The instantiated handler object.
     * @param object $message The request (command) object to pass to the handler.
     * @return mixed The result returned by the handler's method.
     * @throws InvalidArgumentException If the handler does not have a 'handle' method.
     */
    protected function executeHandler(object $handler, object $message): mixed
    {
        if (method_exists($handler, 'handle')) {
            return $handler->handle($message);
        }

        throw new InvalidArgumentException("Handler '" . get_class($handler) . "' must have a 'handle' method.");
    }
}
