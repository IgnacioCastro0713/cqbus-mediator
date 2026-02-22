<?php

declare(strict_types=1);

namespace Ignaciocastro0713\CqbusMediator\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
readonly class Pipeline
{
    /** @var array<class-string> */
    public array $pipes;

    /**
     * @param class-string|array<class-string> $pipes
     */
    public function __construct(string|array $pipes)
    {
        $this->pipes = (array) $pipes;
    }
}
