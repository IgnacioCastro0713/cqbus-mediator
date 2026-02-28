<?php

declare(strict_types=1);

namespace Benchmarks;

use Ignaciocastro0713\CqbusMediator\Discovery\MediatorDiscovery;
use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;

/**
 * Benchmarks for comparing single-pass AST discovery performance vs cached loading.
 *
 * Run with: composer benchmark:discovery
 */
class DiscoveryBench
{
    private string $fixturesPath;
    private string $cachePath;

    public function setUp(): void
    {
        $this->fixturesPath = __DIR__ . '/../tests/Fixtures';
        $this->cachePath = __DIR__ . '/cache/mediator.php';

        // Ensure cache directory exists
        $cacheDir = dirname($this->cachePath);
        if (! is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        // Generate cache file for cached benchmarks
        $discovered = MediatorDiscovery::discover([$this->fixturesPath]);

        $content = "<?php

return " . var_export([
            'handlers' => $discovered['handlers'],
            'notifications' => $discovered['notifications'],
            'actions' => $discovered['actions'],
            'pipelines' => [],
        ], true) . ";
";

        file_put_contents($this->cachePath, $content);
        
        // Clear static cache in memory
        MediatorDiscovery::clearCache();
    }

    /**
     * Single-Pass AST Discovery (Cold Cache)
     */
    #[Revs(10)]
    #[Iterations(5)]
    #[Warmup(1)]
    #[BeforeMethods('setUp')]
    public function benchSinglePassDiscoveryWithoutCache(): void
    {
        MediatorDiscovery::clearCache();
        MediatorDiscovery::discover([$this->fixturesPath]);
    }

    /**
     * Single-Pass AST Discovery (Memory Cached)
     */
    #[Revs(1000)]
    #[Iterations(5)]
    #[Warmup(2)]
    #[BeforeMethods('setUpWarm')]
    public function benchSinglePassDiscoveryMemoryCached(): void
    {
        MediatorDiscovery::discover([$this->fixturesPath]);
    }

    public function setUpWarm(): void
    {
        $this->setUp();
        MediatorDiscovery::discover([$this->fixturesPath]);
    }

    /**
     * Production File Cache Loading
     */
    #[Revs(1000)]
    #[Iterations(5)]
    #[Warmup(2)]
    #[BeforeMethods('setUp')]
    public function benchProductionFileCacheLoad(): void
    {
        $cached = require $this->cachePath;
        $handlers = $cached['handlers'] ?? [];
        $notifications = $cached['notifications'] ?? [];
        $actions = $cached['actions'] ?? [];
    }
}