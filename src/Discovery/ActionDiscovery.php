<?php

declare(strict_types=1);

namespace Ignaciocastro0713\CqbusMediator\Discovery;

use Ignaciocastro0713\CqbusMediator\Constants\MediatorConstants;
use ReflectionMethod;
use Spatie\StructureDiscoverer\Data\DiscoveredStructure;
use Spatie\StructureDiscoverer\Discover;

readonly class ActionDiscovery
{
    /**
     * @param array<string> $directories
     */
    public function __construct(
        private array $directories = []
    ) {
    }

    public static function in(string ...$directories): self
    {
        return new self(directories: $directories);
    }

    /**
     * @return array<class-string> List of Action class names
     */
    public function get(): array
    {
        $discovered = Discover::in(...$this->directories)
            ->classes()
            ->custom(fn (DiscoveredStructure $structure) => $this->isValidActionClass($structure->getFcqn()))
            ->get();

        /** @var array<class-string> $result */
        $result = array_map(
            fn (DiscoveredStructure|string $item): string => is_string($item) ? $item : $item->getFcqn(),
            $discovered
        );

        return $result;
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
