<?php

declare(strict_types=1);

namespace Ignaciocastro0713\CqbusMediator\Discovery;

use Ignaciocastro0713\CqbusMediator\Constants\MediatorConstants;
use ReflectionMethod;
use Spatie\StructureDiscoverer\Data\DiscoveredStructure;
use Spatie\StructureDiscoverer\Discover;

class DiscoverAction
{
    private readonly DiscoverHandlerConfig $config;

    /**
     * @param array<string> $directories
     */
    public function __construct(array $directories = [])
    {
        $this->config = new DiscoverHandlerConfig(
            directories: $directories
        );
    }

    public static function in(string ...$directories): self
    {
        return new self(
            directories: $directories,
        );
    }

    /**
     * @return array<string> List of Action class names
     */
    public function get(): array
    {
        return Discover::in(...$this->config->directories)
            ->classes()
            ->custom(fn (DiscoveredStructure $structure) => $this->isValidActionClass($structure->getFcqn()))
            ->get();
    }

    /**
     * Returns true if the given class uses the action trait and has the route static method.
     */
    private function isValidActionClass(string $className): bool
    {
        if (! class_exists($className)) {
            return false;
        }

        return in_array(MediatorConstants::ACTION_TRAIT, class_uses_recursive($className), true)
            && method_exists($className, MediatorConstants::ROUTE_METHOD)
            && (new ReflectionMethod($className, MediatorConstants::ROUTE_METHOD))->isStatic();
    }
}
