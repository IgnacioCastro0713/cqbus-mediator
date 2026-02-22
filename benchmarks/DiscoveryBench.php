<?php

declare(strict_types=1);

namespace Benchmarks;

use Ignaciocastro0713\CqbusMediator\Discovery\ActionDiscovery;
use Ignaciocastro0713\CqbusMediator\Discovery\EventHandlerDiscovery;
use Ignaciocastro0713\CqbusMediator\Discovery\HandlerDiscovery;
use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;

/**
 * Benchmarks for comparing discovery performance vs cached loading.
 *
 * Run with: composer benchmark
 */
class DiscoveryBench
{
    private string $fixturesPath;
    private string $cachePath;

    public function setUp(): void
    {
        $this->fixturesPath = __DIR__ . '/../tests/Fixtures';
        $this->cachePath = __DIR__ . '/../benchmarks/cache/mediator.php';

        // Ensure cache directory exists
        $cacheDir = dirname($this->cachePath);
        if (! is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        // Generate cache file for cached benchmarks
        $handlers = HandlerDiscovery::in($this->fixturesPath)->get();
        $eventHandlers = EventHandlerDiscovery::in($this->fixturesPath)->get();
        $actions = ActionDiscovery::in($this->fixturesPath)->get();

        $content = "<?php\n\nreturn " . var_export([
            'handlers' => $handlers,
            'event_handlers' => $eventHandlers,
            'actions' => $actions,
        ], true) . ";\n";

        file_put_contents($this->cachePath, $content);
    }

    // =========================================================================
    // HANDLER DISCOVERY BENCHMARKS
    // =========================================================================

    #[Revs(100)]
    #[Iterations(5)]
    #[Warmup(2)]
    #[BeforeMethods('setUp')]
    public function benchHandlerDiscoveryWithoutCache(): void
    {
        HandlerDiscovery::in($this->fixturesPath)->get();
    }

    #[Revs(100)]
    #[Iterations(5)]
    #[Warmup(2)]
    #[BeforeMethods('setUp')]
    public function benchHandlerDiscoveryWithCache(): void
    {
        $cached = require $this->cachePath;
        $handlers = $cached['handlers'] ?? [];
    }

    // =========================================================================
    // EVENT HANDLER DISCOVERY BENCHMARKS
    // =========================================================================

    #[Revs(100)]
    #[Iterations(5)]
    #[Warmup(2)]
    #[BeforeMethods('setUp')]
    public function benchEventHandlerDiscoveryWithoutCache(): void
    {
        EventHandlerDiscovery::in($this->fixturesPath)->get();
    }

    #[Revs(100)]
    #[Iterations(5)]
    #[Warmup(2)]
    #[BeforeMethods('setUp')]
    public function benchEventHandlerDiscoveryWithCache(): void
    {
        $cached = require $this->cachePath;
        $eventHandlers = $cached['event_handlers'] ?? [];
    }

    // =========================================================================
    // ACTION DISCOVERY BENCHMARKS
    // =========================================================================

    #[Revs(100)]
    #[Iterations(5)]
    #[Warmup(2)]
    #[BeforeMethods('setUp')]
    public function benchActionDiscoveryWithoutCache(): void
    {
        ActionDiscovery::in($this->fixturesPath)->get();
    }

    #[Revs(100)]
    #[Iterations(5)]
    #[Warmup(2)]
    #[BeforeMethods('setUp')]
    public function benchActionDiscoveryWithCache(): void
    {
        $cached = require $this->cachePath;
        $actions = $cached['actions'] ?? [];
    }

    // =========================================================================
    // FULL DISCOVERY (ALL TYPES) BENCHMARKS
    // =========================================================================

    #[Revs(50)]
    #[Iterations(5)]
    #[Warmup(2)]
    #[BeforeMethods('setUp')]
    public function benchFullDiscoveryWithoutCache(): void
    {
        HandlerDiscovery::in($this->fixturesPath)->get();
        EventHandlerDiscovery::in($this->fixturesPath)->get();
        ActionDiscovery::in($this->fixturesPath)->get();
    }

    #[Revs(50)]
    #[Iterations(5)]
    #[Warmup(2)]
    #[BeforeMethods('setUp')]
    public function benchFullDiscoveryWithCache(): void
    {
        $cached = require $this->cachePath;
        $handlers = $cached['handlers'] ?? [];
        $eventHandlers = $cached['event_handlers'] ?? [];
        $actions = $cached['actions'] ?? [];
    }
}

