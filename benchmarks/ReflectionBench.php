<?php

declare(strict_types=1);

namespace Benchmarks;

use Ignaciocastro0713\CqbusMediator\Attributes\Pipeline;
use Ignaciocastro0713\CqbusMediator\Attributes\SkipGlobalPipelines;
use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;
use ReflectionClass;

/**
 * Benchmarks for Reflection operations (pipeline resolution).
 *
 * This demonstrates the benefit of caching Reflection results.
 */
class ReflectionBench
{
    /** @var class-string */
    private string $handlerClass;

    /** @var array<string, array<class-string>> */
    private array $pipelinesCache = [];

    public function setUp(): void
    {
        // Use a real handler class from fixtures
        $this->handlerClass = \Tests\Fixtures\Handlers\SinglePipelineHandler::class;
        $this->pipelinesCache = [];
    }

    /**
     * Reflection without cache
     */
    #[Revs(1000)]
    #[Iterations(5)]
    #[Warmup(3)]
    #[BeforeMethods('setUp')]
    public function benchReflectionWithoutCache(): void
    {
        // Simulates what happens without pipelinesCache
        $this->resolvePipelinesWithoutCache($this->handlerClass);
    }

    /**
     * Reflection with cache (first call)
     */
    #[Revs(1000)]
    #[Iterations(5)]
    #[Warmup(3)]
    #[BeforeMethods('setUp')]
    public function benchReflectionWithCacheFirstCall(): void
    {
        // First call - cache miss, does Reflection
        $this->pipelinesCache = []; // Reset cache
        $this->resolvePipelinesWithCache($this->handlerClass);
    }

    /**
     * Reflection with cache (subsequent calls)
     */
    #[Revs(1000)]
    #[Iterations(5)]
    #[Warmup(3)]
    #[BeforeMethods('setUpWithWarmCache')]
    public function benchReflectionWithCacheHit(): void
    {
        // Subsequent calls - cache hit, no Reflection
        $this->resolvePipelinesWithCache($this->handlerClass);
    }

    public function setUpWithWarmCache(): void
    {
        $this->setUp();
        // Warm the cache
        $this->resolvePipelinesWithCache($this->handlerClass);
    }

    /**
     * Helper methods (simulating MediatorService behavior)
     */
    private function resolvePipelinesWithoutCache(string $handlerClass): array
    {
        $handlerPipelines = $this->getHandlerPipelines($handlerClass);

        if ($this->shouldSkipGlobalPipelines($handlerClass)) {
            return $handlerPipelines;
        }

        return array_merge([], $handlerPipelines);
    }

    /**
     * @param class-string $handlerClass
     * @return array<class-string>
     */
    private function resolvePipelinesWithCache(string $handlerClass): array
    {
        if (isset($this->pipelinesCache[$handlerClass])) {
            return $this->pipelinesCache[$handlerClass];
        }

        $handlerPipelines = $this->getHandlerPipelines($handlerClass);

        $pipelines = $this->shouldSkipGlobalPipelines($handlerClass)
            ? $handlerPipelines
            : array_merge([], $handlerPipelines);

        return $this->pipelinesCache[$handlerClass] = $pipelines;
    }

    /**
     * @param class-string $handlerClass
     * @return array<class-string>
     */
    private function getHandlerPipelines(string $handlerClass): array
    {
        $reflection = new ReflectionClass($handlerClass);
        $attributes = $reflection->getAttributes(Pipeline::class);

        if (empty($attributes)) {
            return [];
        }

        return $attributes[0]->newInstance()->pipes;
    }

    /**
     * @param class-string $handlerClass
     */
    private function shouldSkipGlobalPipelines(string $handlerClass): bool
    {
        $reflection = new ReflectionClass($handlerClass);

        return ! empty($reflection->getAttributes(SkipGlobalPipelines::class));
    }
}

